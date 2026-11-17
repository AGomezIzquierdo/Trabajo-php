<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$buscar = trim($_GET["txtBuscar"] ?? "");
$id = $_POST["id"] ?? ($_GET["id"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST" && $id !== "") {
    $actual = consultarUno($conexion, "SELECT imagen FROM paciente WHERE idpaciente = ?", [$id]);
    if (!$actual) {
        $mensaje = alerta("El paciente indicado no existe.", "danger");
    } else {
        $imagen = subirImagen("fileImagen", false);
        if ($imagen["error"] !== "") {
            $mensaje = alerta($imagen["error"], "danger");
        } else {
            $nuevaImagen = $imagen["nombre"] ?? $actual["imagen"];
            $resultado = ejecutar(
                $conexion,
                "UPDATE paciente SET nombre = ?, apellidos = ?, dni = ?, peso = ?, alergias = ?, fecha_nacimiento = ?, imagen = ?, idaseguradora = ? WHERE idpaciente = ?",
                [trim($_POST["txtNombre"]), trim($_POST["txtApellidos"]), strtoupper(trim($_POST["txtDni"])), $_POST["txtPeso"], isset($_POST["chkAlergias"]) ? 1 : 0, $_POST["txtFecha"], $nuevaImagen, $_POST["lstAseguradora"], $id]
            );
            if ($resultado["ok"]) {
                if ($imagen["nombre"]) {
                    borrarImagen($actual["imagen"]);
                }
                $mensaje = alerta("Paciente actualizado correctamente.", "success");
            } else {
                borrarImagen($imagen["nombre"]);
                $mensaje = alerta(mensajeError($resultado), "danger");
            }
        }
    }
}

$paciente = $id !== "" ? consultarUno($conexion, SQL_PACIENTES . " WHERE p.idpaciente = ?", [$id]) : null;
if ($id !== "" && !$paciente && $mensaje === "") {
    $mensaje = alerta("No existe ningún paciente con id $id.", "warning");
}

$resultados = [];
if ($buscar !== "") {
    $patron = "%$buscar%";
    $resultados = consultar($conexion, SQL_PACIENTES . " WHERE p.nombre ILIKE ? OR p.apellidos ILIKE ? OR p.dni ILIKE ? OR CONCAT(p.nombre, ' ', p.apellidos) ILIKE ? ORDER BY p.apellidos, p.nombre", [$patron, $patron, $patron, $patron]);
}

$aseguradoras = consultar($conexion, "SELECT idaseguradora, nombre FROM aseguradora ORDER BY nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="modificar_paciente.php" method="get" class="mb-4">
        <fieldset>
            <legend>Buscar paciente</legend>
            <div class="row g-2">
                <div class="col-sm-6">
                    <input id="txtBuscar" name="txtBuscar" value="<?php echo e($buscar); ?>" placeholder="Nombre, apellidos o DNI" class="form-control" type="search" required <?php echo $paciente ? "" : "autofocus"; ?>>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </fieldset>
    </form>

    <?php
    if ($buscar !== "") {
        echo tabla(columnasPaciente(), $resultados, fn($f) => botonEditar("modificar_paciente.php", $f["idpaciente"]));
    }
    ?>

    <?php if ($paciente) { ?>
        <form action="modificar_paciente.php" name="frmModificarPaciente" id="frmModificarPaciente" method="post" enctype="multipart/form-data">
            <fieldset>
                <legend>Modificar paciente #<?php echo e($paciente["idpaciente"]); ?></legend>
                <input type="hidden" name="id" value="<?php echo e($paciente["idpaciente"]); ?>">
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtNombre">Nombre</label>
                    <div class="col-sm-6">
                        <input id="txtNombre" name="txtNombre" value="<?php echo e($paciente["nombre"]); ?>" class="form-control" maxlength="50" type="text" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtApellidos">Apellidos</label>
                    <div class="col-sm-6">
                        <input id="txtApellidos" name="txtApellidos" value="<?php echo e($paciente["apellidos"]); ?>" class="form-control" maxlength="80" type="text" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtDni">DNI</label>
                    <div class="col-sm-6">
                        <input id="txtDni" name="txtDni" value="<?php echo e($paciente["dni"]); ?>" class="form-control" pattern="[0-9]{8}[A-Za-z]" title="8 números y una letra" maxlength="9" type="text" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="lstAseguradora">Aseguradora</label>
                    <div class="col-sm-6">
                        <select name="lstAseguradora" id="lstAseguradora" class="form-select" required>
                            <?php echo opciones($aseguradoras, "idaseguradora", "nombre", $paciente["idaseguradora"]); ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtPeso">Peso (kg)</label>
                    <div class="col-sm-6">
                        <input id="txtPeso" name="txtPeso" value="<?php echo e($paciente["peso"]); ?>" class="form-control" type="number" step="0.01" min="0" max="400" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtFecha">Fecha de nacimiento</label>
                    <div class="col-sm-6">
                        <input id="txtFecha" name="txtFecha" value="<?php echo e($paciente["fecha_nacimiento"]); ?>" class="form-control" type="date" max="<?php echo date("Y-m-d"); ?>" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-sm-6 offset-sm-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="chkAlergias" name="chkAlergias" <?php echo $paciente["alergias"] ? "checked" : ""; ?>>
                            <label class="form-check-label" for="chkAlergias">Tiene alergias</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="fileImagen">Fotografía</label>
                    <div class="col-sm-2">
                        <?php echo imagenHtml($paciente["imagen"], 90); ?>
                    </div>
                    <div class="col-sm-4">
                        <input id="fileImagen" name="fileImagen" class="form-control" type="file" accept="image/*">
                        <div class="form-text">Déjalo vacío para mantener la imagen actual.</div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-sm-6 offset-sm-3">
                        <input type="submit" id="btnAceptarModificarPaciente" name="btnAceptarModificarPaciente" class="btn btn-primary" value="Guardar cambios">
                        <a href="modificar_paciente.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </fieldset>
        </form>
    <?php } ?>
</div>
</body>

</html>
