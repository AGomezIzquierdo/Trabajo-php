<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$buscar = trim($_GET["txtBuscar"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $paciente = consultarUno($conexion, "SELECT * FROM paciente WHERE idpaciente = ?", [$_POST["id"]]);
    if (!$paciente) {
        $mensaje = alerta("No existe ningún paciente con id " . $_POST["id"] . ".", "warning");
    } else {
        $imagenesCitas = consultar($conexion, "SELECT imagen FROM cita WHERE idpaciente = ?", [$paciente["idpaciente"]]);
        $resultado = ejecutar($conexion, "DELETE FROM paciente WHERE idpaciente = ?", [$paciente["idpaciente"]]);
        if ($resultado["ok"]) {
            borrarImagen($paciente["imagen"]);
            foreach ($imagenesCitas as $c) {
                borrarImagen($c["imagen"]);
            }
            $mensaje = alerta("Paciente " . $paciente["nombre"] . " " . $paciente["apellidos"] . " borrado junto con sus citas.", "success");
        } else {
            $mensaje = alerta(mensajeError($resultado), "danger");
        }
    }
}

$resultados = [];
if ($buscar !== "") {
    if (ctype_digit($buscar)) {
        $resultados = consultar($conexion, SQL_PACIENTES . " WHERE p.idpaciente = ?", [$buscar]);
    } else {
        $patron = "%$buscar%";
        $resultados = consultar($conexion, SQL_PACIENTES . " WHERE p.dni = ? OR p.nombre ILIKE ? OR p.apellidos ILIKE ? OR CONCAT(p.nombre, ' ', p.apellidos) ILIKE ? ORDER BY p.apellidos, p.nombre", [$buscar, $patron, $patron, $patron]);
    }
}

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="baja_paciente.php" method="get" class="mb-4">
        <fieldset>
            <legend>Baja de paciente</legend>
            <div class="row g-2">
                <div class="col-sm-6">
                    <input id="txtBuscar" name="txtBuscar" value="<?php echo e($buscar); ?>" placeholder="ID, DNI, nombre o apellidos" class="form-control" type="search" required autofocus>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </fieldset>
    </form>
    <?php
    if ($buscar !== "") {
        echo tabla(columnasPaciente(), $resultados, fn($f) => botonBorrar("baja_paciente.php?txtBuscar=" . urlencode($buscar), $f["idpaciente"]));
    }
    ?>
</div>
</body>

</html>
