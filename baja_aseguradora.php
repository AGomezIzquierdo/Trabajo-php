<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$buscar = trim($_GET["txtBuscar"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $aseguradora = consultarUno($conexion, "SELECT * FROM aseguradora WHERE idaseguradora = ?", [$_POST["id"]]);
    if (!$aseguradora) {
        $mensaje = alerta("No existe ninguna aseguradora con id " . $_POST["id"] . ".", "warning");
    } else {
        $resultado = ejecutar($conexion, "DELETE FROM aseguradora WHERE idaseguradora = ?", [$aseguradora["idaseguradora"]]);
        if ($resultado["ok"]) {
            borrarImagen($aseguradora["imagen"]);
            $mensaje = alerta("Aseguradora " . $aseguradora["nombre"] . " borrada.", "success");
        } elseif ($resultado["numerror"] === "23503") {
            $mensaje = alerta("No se puede borrar " . $aseguradora["nombre"] . " porque tiene pacientes asociados. Cambia antes su aseguradora o desactívala.", "danger");
        } else {
            $mensaje = alerta(mensajeError($resultado), "danger");
        }
    }
}

$resultados = [];
if ($buscar !== "") {
    if (ctype_digit($buscar)) {
        $resultados = consultar($conexion, SQL_ASEGURADORAS . " WHERE a.idaseguradora = ?", [$buscar]);
    } else {
        $resultados = consultar($conexion, SQL_ASEGURADORAS . " WHERE a.nombre ILIKE ? ORDER BY a.nombre", ["%$buscar%"]);
    }
}

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="baja_aseguradora.php" method="get" class="mb-4">
        <fieldset>
            <legend>Baja de aseguradora</legend>
            <div class="row g-2">
                <div class="col-sm-6">
                    <input id="txtBuscar" name="txtBuscar" value="<?php echo e($buscar); ?>" placeholder="ID o nombre de la aseguradora" class="form-control" type="search" required autofocus>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </fieldset>
    </form>
    <?php
    if ($buscar !== "") {
        echo tabla(columnasAseguradora(), $resultados, fn($f) => botonBorrar("baja_aseguradora.php?txtBuscar=" . urlencode($buscar), $f["idaseguradora"]));
    }
    ?>
</div>
</body>

</html>
