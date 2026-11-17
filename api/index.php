<?php
$ruta = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
$pagina = basename((string) $ruta);
if ($pagina === "" || $pagina === "api") {
    $pagina = "index.php";
}
$privadas = ["config.php", "config.local.php", "funcionesBD.php"];
$fichero = dirname(__DIR__) . "/" . $pagina;
if (!preg_match('/^[a-z_]+\.php$/', $pagina) || in_array($pagina, $privadas) || !is_file($fichero)) {
    http_response_code(404);
    echo "<h2 style='text-align:center;margin-top:50px'>Página no encontrada</h2>";
    exit;
}
chdir(dirname(__DIR__));
require $fichero;
