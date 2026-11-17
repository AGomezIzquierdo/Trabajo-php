<?php
if (is_file(__DIR__ . "/config.local.php")) {
    require_once __DIR__ . "/config.local.php";
}

define("DB_HOST", getenv("DB_HOST") ?: "aws-1-eu-west-1.pooler.supabase.com");
define("DB_PORT", getenv("DB_PORT") ?: 5432);
define("DB_NAME", getenv("DB_NAME") ?: "postgres");
define("DB_USER", getenv("DB_USER") ?: "postgres.gjztlpvftfwjotioweyu");
define("DB_PASS", getenv("DB_PASS") ?: "");
define("DB_SCHEMA", "clinica");

define("SUPABASE_URL", getenv("SUPABASE_URL") ?: "https://gjztlpvftfwjotioweyu.supabase.co");
define("SUPABASE_KEY", getenv("SUPABASE_KEY") ?: "");
define("SUPABASE_BUCKET", "imagenes");

define("CARPETA_IMAGENES", __DIR__ . "/imagenes/");
define("URL_IMAGENES", "imagenes/");
define("TAMANO_MAXIMO_IMAGEN", 2 * 1024 * 1024);

date_default_timezone_set("Europe/Madrid");
