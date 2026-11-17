<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$buscar = trim($_GET["txtBuscar"] ?? "");
$id = $_POST["id"] ?? ($_GET["id"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST" && $id !== "") {
    $actual = consultarUno($conexion, "SELECT imagen FROM medico WHERE idmedico = ?", [$id]);
    if (!$actual) {
        $mensaje = alerta("El médico indicado no existe.", "danger");
    } else {
        $imagen = subirImagen("fileImagen", false);
        if ($imagen["error"] !== "") {
            $mensaje = alerta($imagen["error"], "danger");
        } else {
            $nuevaImagen = $imagen["nombre"] ?? $actual["imagen"];
            $resultado = ejecutar(
                $conexion,
                "UPDATE medico SET nombre = ?, apellidos = ?, num_colegiado = ?, salario = ?, disponible = ?, fecha_contratacion = ?, imagen = ?, idespecialidad = ? WHERE idmedico = ?",
                [trim($_POST["txtNombre"]), trim($_POST["txtApellidos"]), trim($_POST["txtColegiado"]), $_POST["txtSalario"], isset($_POST["chkDisponible"]) ? 1 : 0, $_POST["txtFecha"], $nuevaImagen, $_POST["lstEspecialidad"], $id]
            );
            if ($resultado["ok"]) {
                if ($imagen["nombre"]) {
                    borrarImagen($actual["imagen"]);
                }
                $mensaje = alerta("Médico actualizado correctamente.", "success");
            } else {
                borrarImagen($imagen["nombre"]);
                $mensaje = alerta(mensajeError($resultado), "danger");
            }
        }
    }
}

$medico = $id !== "" ? consultarUno($conexion, SQL_MEDICOS . " WHERE m.idmedico = ?", [$id]) : null;
if ($id !== "" && !$medico && $mensaje === "") {
    $mensaje = alerta("No existe ningún médico con id $id.", "warning");
}

$resultados = [];
if ($buscar !== "") {
    $patron = "%$buscar%";
    $resultados = consultar($conexion, SQL_MEDICOS . " WHERE m.nombre ILIKE ? OR m.apellidos ILIKE ? OR m.num_colegiado ILIKE ? OR CONCAT(m.nombre, ' ', m.apellidos) ILIKE ? ORDER BY m.apellidos, m.nombre", [$patron, $patron, $patron, $patron]);
}

$especialidades = consultar($conexion, "SELECT idespecialidad, nombre FROM especialidad ORDER BY nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="modificar_medico.php" method="get" class="mb-4">
        <fieldset>
            <legend>Buscar médico</legend>
            <div class="row g-2">
                <div class="col-sm-6">
                    <input id="txtBuscar" name="txtBuscar" value="<?php echo e($buscar); ?>" placeholder="Nombre, apellidos o nº de colegiado" class="form-control" type="search" required <?php echo $medico ? "" : "autofocus"; ?>>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </fieldset>
    </form>

    <?php
    if ($buscar !== "") {
        echo tabla(columnasMedico(), $resultados, fn($f) => botonEditar("modificar_medico.php", $f["idmedico"]));
    }
    ?>

    <?php if ($medico) { ?>
        <form action="modificar_medico.php" name="frmModificarMedico" id="frmModificarMedico" method="post" enctype="multipart/form-data">
            <fieldset>
                <legend>Modificar médico #<?php echo e($medico["idmedico"]); ?></legend>
                <input type="hidden" name="id" value="<?php echo e($medico["idmedico"]); ?>">
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtNombre">Nombre</label>
                    <div class="col-sm-6">
                        <input id="txtNombre" name="txtNombre" value="<?php echo e($medico["nombre"]); ?>" class="form-control" maxlength="50" type="text" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtApellidos">Apellidos</label>
                    <div class="col-sm-6">
                        <input id="txtApellidos" name="txtApellidos" value="<?php echo e($medico["apellidos"]); ?>" class="form-control" maxlength="80" type="text" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtColegiado">Nº de colegiado</label>
                    <div class="col-sm-6">
                        <input id="txtColegiado" name="txtColegiado" value="<?php echo e($medico["num_colegiado"]); ?>" class="form-control" pattern="[0-9]{9}" title="9 dígitos" maxlength="9" type="text" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="lstEspecialidad">Especialidad</label>
                    <div class="col-sm-6">
                        <select name="lstEspecialidad" id="lstEspecialidad" class="form-select" required>
                            <?php echo opciones($especialidades, "idespecialidad", "nombre", $medico["idespecialidad"]); ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtSalario">Salario (€)</label>
                    <div class="col-sm-6">
                        <input id="txtSalario" name="txtSalario" value="<?php echo e($medico["salario"]); ?>" class="form-control" type="number" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtFecha">Fecha de contratación</label>
                    <div class="col-sm-6">
                        <input id="txtFecha" name="txtFecha" value="<?php echo e($medico["fecha_contratacion"]); ?>" class="form-control" type="date" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-sm-6 offset-sm-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="chkDisponible" name="chkDisponible" <?php echo $medico["disponible"] ? "checked" : ""; ?>>
                            <label class="form-check-label" for="chkDisponible">Disponible</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="fileImagen">Fotografía</label>
                    <div class="col-sm-2">
                        <?php echo imagenHtml($medico["imagen"], 90); ?>
                    </div>
                    <div class="col-sm-4">
                        <input id="fileImagen" name="fileImagen" class="form-control" type="file" accept="image/*">
                        <div class="form-text">Déjalo vacío para mantener la imagen actual.</div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-sm-6 offset-sm-3">
                        <input type="submit" id="btnAceptarModificarMedico" name="btnAceptarModificarMedico" class="btn btn-primary" value="Guardar cambios">
                        <a href="modificar_medico.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </fieldset>
        </form>
    <?php } ?>
</div>
</body>

</html>
