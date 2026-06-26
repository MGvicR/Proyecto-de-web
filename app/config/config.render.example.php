<?php

declare(strict_types=1);

/**
 * Render.com — agrega estas variables en Dashboard → Environment:
 *
 * DB_HOST     = host de tu MySQL externo (ej. mysql.railway.app)
 * DB_NAME     = nombre de la base
 * DB_USER     = usuario
 * DB_PASS     = contraseña
 * DB_PORT     = 25725 (el que te da Aiven, no siempre es 3306)
 * DB_SSL      = true   (Aiven lo requiere; se activa solo si el host es aivencloud.com)
 *
 * Luego importa sql/schema.sql en esa base y redeploy.
 * La base del profesor (tecweb) NO funciona desde Render.
 */
