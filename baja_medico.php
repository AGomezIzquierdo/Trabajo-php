<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$buscar = trim($_GET["txtBuscar"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $medico = consultarUno($conexion, "SELECT * FROM medico WHERE idmedico = ?", [$_POST["id"]]);
    if (!$medico) {
        $mensaje = alerta("No existe ningún médico con id " . $_POST["id"] . ".", "warning");
    } else {
        $imagenesCitas = consultar($conexion, "SELECT imagen FROM cita WHERE idmedico = ?", [$medico["idmedico"]]);
        $resultado = ejecutar($conexion, "DELETE FROM medico WHERE idmedico = ?", [$medico["idmedico"]]);
        if ($resultado["ok"]) {
            borrarImagen($medico["imagen"]);
            foreach ($imagenesCitas as $c) {
                borrarImagen($c["imagen"]);
            }
            $mensaje = alerta("Médico " . $medico["nombre"] . " " . $medico["apellidos"] . " borrado junto con sus citas.", "success");
        } else {
            $mensaje = alerta(mensajeError($resultado), "danger");
        }
    }
}

$resultados = [];
if ($buscar !== "") {
    if (ctype_digit($buscar)) {
        $resultados = consultar($conexion, SQL_MEDICOS . " WHERE m.idmedico = ? OR m.num_colegiado = ?", [$buscar, $buscar]);
    } else {
        $patron = "%$buscar%";
        $resultados = consultar($conexion, SQL_MEDICOS . " WHERE m.nombre ILIKE ? OR m.apellidos ILIKE ? OR CONCAT(m.nombre, ' ', m.apellidos) ILIKE ? ORDER BY m.apellidos, m.nombre", [$patron, $patron, $patron]);
    }
}

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="baja_medico.php" method="get" class="mb-4">
        <fieldset>
            <legend>Baja de médico</legend>
            <div class="row g-2">
                <div class="col-sm-6">
                    <input id="txtBuscar" name="txtBuscar" value="<?php echo e($buscar); ?>" placeholder="ID, nº de colegiado, nombre o apellidos" class="form-control" type="search" required autofocus>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </fieldset>
    </form>
    <?php
    if ($buscar !== "") {
        echo tabla(columnasMedico(), $resultados, fn($f) => botonBorrar("baja_medico.php?txtBuscar=" . urlencode($buscar), $f["idmedico"]));
    }
    ?>
</div>
</body>

</html>
