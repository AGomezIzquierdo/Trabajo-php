<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$buscar = trim($_GET["txtBuscar"] ?? "");
$id = $_POST["id"] ?? ($_GET["id"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST" && $id !== "") {
    $actual = consultarUno($conexion, "SELECT imagen FROM cita WHERE idcita = ?", [$id]);
    if (!$actual) {
        $mensaje = alerta("La cita indicada no existe.", "danger");
    } else {
        $imagen = subirImagen("fileImagen", false);
        if ($imagen["error"] !== "") {
            $mensaje = alerta($imagen["error"], "danger");
        } else {
            $nuevaImagen = $imagen["nombre"] ?? $actual["imagen"];
            $resultado = ejecutar(
                $conexion,
                "UPDATE cita SET motivo = ?, precio = ?, pagada = ?, fecha_hora = ?, imagen = ?, idpaciente = ?, idmedico = ? WHERE idcita = ?",
                [trim($_POST["txtMotivo"]), $_POST["txtPrecio"], isset($_POST["chkPagada"]) ? 1 : 0, str_replace("T", " ", $_POST["txtFechaHora"]), $nuevaImagen, $_POST["lstPaciente"], $_POST["lstMedico"], $id]
            );
            if ($resultado["ok"]) {
                if ($imagen["nombre"]) {
                    borrarImagen($actual["imagen"]);
                }
                $mensaje = alerta("Cita actualizada correctamente.", "success");
            } else {
                borrarImagen($imagen["nombre"]);
                $mensaje = alerta(mensajeError($resultado), "danger");
            }
        }
    }
}

$cita = $id !== "" ? consultarUno($conexion, SQL_CITAS . " WHERE c.idcita = ?", [$id]) : null;
if ($id !== "" && !$cita && $mensaje === "") {
    $mensaje = alerta("No existe ninguna cita con id $id.", "warning");
}

$resultados = [];
if ($buscar !== "") {
    $patron = "%$buscar%";
    $resultados = consultar($conexion, SQL_CITAS . " WHERE c.motivo ILIKE ? OR CONCAT(p.nombre, ' ', p.apellidos) ILIKE ? OR p.dni = ? OR CONCAT(m.nombre, ' ', m.apellidos) ILIKE ? ORDER BY c.fecha_hora DESC", [$patron, $patron, $buscar, $patron]);
}

$pacientes = consultar($conexion, "SELECT idpaciente, CONCAT(apellidos, ', ', nombre, ' (', dni, ')') AS texto FROM paciente ORDER BY apellidos, nombre");
$medicos = consultar($conexion, "SELECT m.idmedico, CONCAT(m.apellidos, ', ', m.nombre, ' - ', e.nombre) AS texto FROM medico m INNER JOIN especialidad e ON m.idespecialidad = e.idespecialidad ORDER BY m.apellidos, m.nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="modificar_cita.php" method="get" class="mb-4">
        <fieldset>
            <legend>Buscar cita</legend>
            <div class="row g-2">
                <div class="col-sm-6">
                    <input id="txtBuscar" name="txtBuscar" value="<?php echo e($buscar); ?>" placeholder="Motivo, nombre o DNI del paciente, nombre del médico" class="form-control" type="search" required <?php echo $cita ? "" : "autofocus"; ?>>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </fieldset>
    </form>

    <?php
    if ($buscar !== "") {
        echo tabla(columnasCita(), $resultados, fn($f) => botonEditar("modificar_cita.php", $f["idcita"]));
    }
    ?>

    <?php if ($cita) { ?>
        <form action="modificar_cita.php" name="frmModificarCita" id="frmModificarCita" method="post" enctype="multipart/form-data">
            <fieldset>
                <legend>Modificar cita #<?php echo e($cita["idcita"]); ?></legend>
                <input type="hidden" name="id" value="<?php echo e($cita["idcita"]); ?>">
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="lstPaciente">Paciente</label>
                    <div class="col-sm-6">
                        <select name="lstPaciente" id="lstPaciente" class="form-select" required>
                            <?php echo opciones($pacientes, "idpaciente", "texto", $cita["idpaciente"]); ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="lstMedico">Médico</label>
                    <div class="col-sm-6">
                        <select name="lstMedico" id="lstMedico" class="form-select" required>
                            <?php echo opciones($medicos, "idmedico", "texto", $cita["idmedico"]); ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtFechaHora">Fecha y hora</label>
                    <div class="col-sm-6">
                        <input id="txtFechaHora" name="txtFechaHora" value="<?php echo e(date("Y-m-d\TH:i", strtotime($cita["fecha_hora"]))); ?>" class="form-control" type="datetime-local" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtMotivo">Motivo</label>
                    <div class="col-sm-6">
                        <input id="txtMotivo" name="txtMotivo" value="<?php echo e($cita["motivo"]); ?>" class="form-control" maxlength="120" type="text" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtPrecio">Precio (€)</label>
                    <div class="col-sm-6">
                        <input id="txtPrecio" name="txtPrecio" value="<?php echo e($cita["precio"]); ?>" class="form-control" type="number" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-sm-6 offset-sm-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="chkPagada" name="chkPagada" <?php echo $cita["pagada"] ? "checked" : ""; ?>>
                            <label class="form-check-label" for="chkPagada">Pagada</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="fileImagen">Documento / imagen</label>
                    <div class="col-sm-2">
                        <?php echo imagenHtml($cita["imagen"], 90); ?>
                    </div>
                    <div class="col-sm-4">
                        <input id="fileImagen" name="fileImagen" class="form-control" type="file" accept="image/*">
                        <div class="form-text">Déjalo vacío para mantener la imagen actual.</div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-sm-6 offset-sm-3">
                        <input type="submit" id="btnAceptarModificarCita" name="btnAceptarModificarCita" class="btn btn-primary" value="Guardar cambios">
                        <a href="modificar_cita.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </fieldset>
        </form>
    <?php } ?>
</div>
</body>

</html>
