<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$vacio = ["motivo" => "", "precio" => "", "pagada" => 0, "fecha_hora" => "", "idpaciente" => "", "idmedico" => ""];
$datos = $vacio;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $datos = [
        "motivo" => trim($_POST["txtMotivo"] ?? ""),
        "precio" => $_POST["txtPrecio"] ?? "",
        "pagada" => isset($_POST["chkPagada"]) ? 1 : 0,
        "fecha_hora" => $_POST["txtFechaHora"] ?? "",
        "idpaciente" => $_POST["lstPaciente"] ?? "",
        "idmedico" => $_POST["lstMedico"] ?? "",
    ];
    $imagen = subirImagen("fileImagen");
    if ($imagen["error"] !== "") {
        $mensaje = alerta($imagen["error"], "danger");
    } else {
        $resultado = ejecutar(
            $conexion,
            "INSERT INTO cita (motivo, precio, pagada, fecha_hora, imagen, idpaciente, idmedico) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$datos["motivo"], $datos["precio"], $datos["pagada"], str_replace("T", " ", $datos["fecha_hora"]), $imagen["nombre"], $datos["idpaciente"], $datos["idmedico"]]
        );
        if ($resultado["ok"]) {
            $mensaje = alerta("Cita registrada correctamente con id " . $resultado["id"] . " para el " . fechaHora($datos["fecha_hora"]) . ".", "success");
            $datos = $vacio;
        } else {
            borrarImagen($imagen["nombre"]);
            $mensaje = alerta(mensajeError($resultado), "danger");
        }
    }
}

$pacientes = consultar($conexion, "SELECT idpaciente, CONCAT(apellidos, ', ', nombre, ' (', dni, ')') AS texto FROM paciente ORDER BY apellidos, nombre");
$medicos = consultar($conexion, "SELECT m.idmedico, CONCAT(m.apellidos, ', ', m.nombre, ' - ', e.nombre) AS texto FROM medico m INNER JOIN especialidad e ON m.idespecialidad = e.idespecialidad WHERE m.disponible = TRUE ORDER BY m.apellidos, m.nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="alta_citas.php" name="frmAltaCita" id="frmAltaCita" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend>Alta de cita</legend>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="lstPaciente">Paciente</label>
                <div class="col-sm-6">
                    <select name="lstPaciente" id="lstPaciente" class="form-select" required autofocus>
                        <option value="">Selecciona un paciente</option>
                        <?php echo opciones($pacientes, "idpaciente", "texto", $datos["idpaciente"]); ?>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="lstMedico">Médico</label>
                <div class="col-sm-6">
                    <select name="lstMedico" id="lstMedico" class="form-select" required>
                        <option value="">Selecciona un médico disponible</option>
                        <?php echo opciones($medicos, "idmedico", "texto", $datos["idmedico"]); ?>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtFechaHora">Fecha y hora</label>
                <div class="col-sm-6">
                    <input id="txtFechaHora" name="txtFechaHora" value="<?php echo e($datos["fecha_hora"]); ?>" class="form-control" type="datetime-local" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtMotivo">Motivo</label>
                <div class="col-sm-6">
                    <input id="txtMotivo" name="txtMotivo" value="<?php echo e($datos["motivo"]); ?>" placeholder="Motivo de la consulta" class="form-control" maxlength="120" type="text" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtPrecio">Precio (€)</label>
                <div class="col-sm-6">
                    <input id="txtPrecio" name="txtPrecio" value="<?php echo e($datos["precio"]); ?>" placeholder="Precio" class="form-control" type="number" step="0.01" min="0" required>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-6 offset-sm-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="chkPagada" name="chkPagada" <?php echo $datos["pagada"] ? "checked" : ""; ?>>
                        <label class="form-check-label" for="chkPagada">Pagada</label>
                    </div>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="fileImagen">Documento / imagen</label>
                <div class="col-sm-6">
                    <input id="fileImagen" name="fileImagen" class="form-control" type="file" accept="image/*" required>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-6 offset-sm-3">
                    <input type="submit" id="btnAceptarAltaCita" name="btnAceptarAltaCita" class="btn btn-primary" value="Aceptar">
                    <input type="reset" class="btn btn-secondary" value="Limpiar">
                </div>
            </div>
        </fieldset>
    </form>
</div>
</body>

</html>
