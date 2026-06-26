#!/usr/bin/env python3
"""Sincroniza el proyecto local con el servidor SFTP configurado."""

import argparse
import os
import sys

import pexpect

LOCAL = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
REMOTE = "/var/www/html/proyecto"
HOST = "127.0.0.1"
PORT = "2222"
USER = "debian"
PASSWORD = "debian"

UPLOADS_LOCAL = os.path.join(LOCAL, "Proyecto/public/uploads/propiedades")
UPLOADS_REMOTE = f"{REMOTE}/Proyecto/public/uploads/propiedades"

BASE_EXCLUDES = [
    ".git",
    ".vscode",
    "Proyecto/app/config/config.local.php",
]

UPLOADS_EXCLUDES = [
    "Proyecto/public/uploads/propiedades/*",
    "!Proyecto/public/uploads/propiedades/.gitkeep",
]


def build_excludes(with_uploads: bool) -> list[str]:
    excludes = list(BASE_EXCLUDES)
    if not with_uploads:
        excludes.extend(UPLOADS_EXCLUDES)
    return excludes


def run_rsync(
    source: str,
    destination: str,
    excludes: list[str] | None = None,
    timeout: int = 120,
) -> int:
    exclude_args: list[str] = []
    for pattern in excludes or []:
        exclude_args.extend(["--exclude", pattern])

    cmd = [
        "rsync",
        "-avz",
        "-e",
        f"ssh -F /dev/null -o StrictHostKeyChecking=no -p {PORT}",
        *exclude_args,
        source,
        destination,
    ]

    child = pexpect.spawn(cmd[0], cmd[1:], encoding="utf-8", timeout=timeout)
    child.logfile = sys.stdout

    while True:
        index = child.expect(
            [
                r"password:",
                pexpect.EOF,
                pexpect.TIMEOUT,
            ],
            timeout=timeout,
        )
        if index == 0:
            child.sendline(PASSWORD)
        elif index == 1:
            break
        else:
            print("Timeout durante rsync")
            return 1

    child.close()
    return child.exitstatus or 0


def count_local_uploads() -> int:
    if not os.path.isdir(UPLOADS_LOCAL):
        return 0

    return sum(
        1
        for name in os.listdir(UPLOADS_LOCAL)
        if os.path.isfile(os.path.join(UPLOADS_LOCAL, name))
        and not name.startswith(".")
    )


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Sincroniza el proyecto local con el servidor."
    )
    parser.add_argument(
        "--with-uploads",
        action="store_true",
        help="Incluye las fotos de public/uploads/propiedades/ en la sincronización.",
    )
    parser.add_argument(
        "--uploads-only",
        action="store_true",
        help="Sincroniza solo la carpeta de fotos (requiere --with-uploads implícito).",
    )
    parser.add_argument(
        "--pull-uploads",
        action="store_true",
        help="Descarga las fotos del servidor hacia la carpeta local.",
    )
    return parser.parse_args()


def main() -> int:
    args = parse_args()

    if args.pull_uploads:
        print(f"Descargando fotos {USER}@{HOST}:{UPLOADS_REMOTE} -> {UPLOADS_LOCAL}")
        os.makedirs(UPLOADS_LOCAL, exist_ok=True)
        code = run_rsync(
            f"{USER}@{HOST}:{UPLOADS_REMOTE}/",
            f"{UPLOADS_LOCAL}/",
            timeout=300,
        )
    elif args.uploads_only:
        local_count = count_local_uploads()
        print(f"Subiendo fotos {UPLOADS_LOCAL} -> {USER}@{HOST}:{UPLOADS_REMOTE}")
        if local_count == 0:
            print("Aviso: no hay archivos de imagen en la carpeta local.")
        code = run_rsync(
            f"{UPLOADS_LOCAL}/",
            f"{USER}@{HOST}:{UPLOADS_REMOTE}/",
            timeout=300,
        )
    else:
        with_uploads = args.with_uploads
        print(f"Sincronizando {LOCAL} -> {USER}@{HOST}:{REMOTE}")
        if with_uploads:
            local_count = count_local_uploads()
            print(f"Incluyendo fotos ({local_count} archivo(s) en local).")
            if local_count == 0:
                print("Aviso: no hay imágenes locales; solo se sincronizará .gitkeep.")
        else:
            print("Las fotos se omiten (usa --with-uploads para incluirlas).")
        code = run_rsync(
            f"{LOCAL}/",
            f"{USER}@{HOST}:{REMOTE}/",
            build_excludes(with_uploads),
        )

    if code == 0:
        print("Sincronización completada.")
    else:
        print(f"Sincronización falló (código {code}).")

    return code


if __name__ == "__main__":
    sys.exit(main())
