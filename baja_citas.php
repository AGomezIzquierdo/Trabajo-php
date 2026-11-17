<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$buscar = trim($_GET["txtBuscar"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $cita = consultarUno($conexion, "SELECT * FROM cita WHERE idcita = ?", [$_POST["id"]]);
    if (!$cita) {
        $mensaje = alerta("No existe ninguna cita con id " . $_POST["id"] . ".", "warning");
    } else {
        $resultado = ejecutar($conexion, "DELETE FROM cita WHERE idcita = ?", [$cita["idcita"]]);
        if ($resultado["ok"]) {
            borrarImagen($cita["imagen"]);
            $mensaje = alerta("Cita " . $cita["idcita"] . " del " . fechaHora($cita["fecha_hora"]) . " borrada.", "success");
        } else {
            $mensaje = alerta(mensajeError($resultado), "danger");
        }
    }
}

$resultados = [];
if ($buscar !== "") {
    if (ctype_digit($buscar)) {
        $resultados = consultar($conexion, SQL_CITAS . " WHERE c.idcita = ?", [$buscar]);
    } else {
        $patron = "%$buscar%";
        $resultados = consultar($conexion, SQL_CITAS . " WHERE c.motivo ILIKE ? OR CONCAT(p.nombre, ' ', p.apellidos) ILIKE ? OR p.dni = ? ORDER BY c.fecha_hora DESC", [$patron, $patron, $buscar]);
    }
}

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="baja_citas.php" method="get" class="mb-4">
        <fieldset>
            <legend>Baja de cita</legend>
            <div class="row g-2">
                <div class="col-sm-6">
                    <input id="txtBuscar" name="txtBuscar" value="<?php echo e($buscar); ?>" placeholder="ID de la cita, motivo, nombre o DNI del paciente" class="form-control" type="search" required autofocus>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </fieldset>
    </form>
    <?php
    if ($buscar !== "") {
        echo tabla(columnasCita(), $resultados, fn($f) => botonBorrar("baja_citas.php?txtBuscar=" . urlencode($buscar), $f["idcita"]));
    }
    ?>
</div>
</body>

</html>
