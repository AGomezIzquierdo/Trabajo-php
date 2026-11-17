<?php
define("DB_HOST", "aws-1-eu-west-1.pooler.supabase.com");
define("DB_PORT", 5432);
define("DB_NAME", "postgres");
define("DB_USER", "postgres.gjztlpvftfwjotioweyu");
define("DB_PASS", getenv("DB_PASS") ?: "J35lAHv2a7LfHoJN");
define("DB_SCHEMA", "clinica");

define("CARPETA_IMAGENES", __DIR__ . "/imagenes/");
define("URL_IMAGENES", "imagenes/");
define("TAMANO_MAXIMO_IMAGEN", 2 * 1024 * 1024);
