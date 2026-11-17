<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$datos = ["nombre" => "", "apellidos" => "", "num_colegiado" => "", "salario" => "", "disponible" => 1, "fecha_contratacion" => date("Y-m-d"), "idespecialidad" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $datos = [
        "nombre" => trim($_POST["txtNombre"] ?? ""),
        "apellidos" => trim($_POST["txtApellidos"] ?? ""),
        "num_colegiado" => trim($_POST["txtColegiado"] ?? ""),
        "salario" => $_POST["txtSalario"] ?? "",
        "disponible" => isset($_POST["chkDisponible"]) ? 1 : 0,
        "fecha_contratacion" => $_POST["txtFecha"] ?? "",
        "idespecialidad" => $_POST["lstEspecialidad"] ?? "",
    ];
    $imagen = subirImagen("fileImagen");
    if ($imagen["error"] !== "") {
        $mensaje = alerta($imagen["error"], "danger");
    } else {
        $resultado = ejecutar(
            $conexion,
            "INSERT INTO medico (nombre, apellidos, num_colegiado, salario, disponible, fecha_contratacion, imagen, idespecialidad) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$datos["nombre"], $datos["apellidos"], $datos["num_colegiado"], $datos["salario"], $datos["disponible"], $datos["fecha_contratacion"], $imagen["nombre"], $datos["idespecialidad"]]
        );
        if ($resultado["ok"]) {
            $mensaje = alerta("Médico " . $datos["nombre"] . " " . $datos["apellidos"] . " dado de alta con id " . $resultado["id"] . ".", "success");
            $datos = ["nombre" => "", "apellidos" => "", "num_colegiado" => "", "salario" => "", "disponible" => 1, "fecha_contratacion" => date("Y-m-d"), "idespecialidad" => ""];
        } else {
            borrarImagen($imagen["nombre"]);
            $mensaje = alerta(mensajeError($resultado), "danger");
        }
    }
}

$especialidades = consultar($conexion, "SELECT idespecialidad, nombre FROM especialidad ORDER BY nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="alta_medico.php" name="frmAltaMedico" id="frmAltaMedico" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend>Alta de médico</legend>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtNombre">Nombre</label>
                <div class="col-sm-6">
                    <input id="txtNombre" name="txtNombre" value="<?php echo e($datos["nombre"]); ?>" placeholder="Nombre" class="form-control" maxlength="50" type="text" required autofocus>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtApellidos">Apellidos</label>
                <div class="col-sm-6">
                    <input id="txtApellidos" name="txtApellidos" value="<?php echo e($datos["apellidos"]); ?>" placeholder="Apellidos" class="form-control" maxlength="80" type="text" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtColegiado">Nº de colegiado</label>
                <div class="col-sm-6">
                    <input id="txtColegiado" name="txtColegiado" value="<?php echo e($datos["num_colegiado"]); ?>" placeholder="9 dígitos" class="form-control" pattern="[0-9]{9}" title="9 dígitos" maxlength="9" type="text" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="lstEspecialidad">Especialidad</label>
                <div class="col-sm-6">
                    <select name="lstEspecialidad" id="lstEspecialidad" class="form-select" required>
                        <option value="">Selecciona una especialidad</option>
                        <?php echo opciones($especialidades, "idespecialidad", "nombre", $datos["idespecialidad"]); ?>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtSalario">Salario (€)</label>
                <div class="col-sm-6">
                    <input id="txtSalario" name="txtSalario" value="<?php echo e($datos["salario"]); ?>" placeholder="Salario mensual" class="form-control" type="number" step="0.01" min="0" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtFecha">Fecha de contratación</label>
                <div class="col-sm-6">
                    <input id="txtFecha" name="txtFecha" value="<?php echo e($datos["fecha_contratacion"]); ?>" class="form-control" type="date" required>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-6 offset-sm-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="chkDisponible" name="chkDisponible" <?php echo $datos["disponible"] ? "checked" : ""; ?>>
                        <label class="form-check-label" for="chkDisponible">Disponible</label>
                    </div>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="fileImagen">Fotografía</label>
                <div class="col-sm-6">
                    <input id="fileImagen" name="fileImagen" class="form-control" type="file" accept="image/*" required>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-6 offset-sm-3">
                    <input type="submit" id="btnAceptarAltaMedico" name="btnAceptarAltaMedico" class="btn btn-primary" value="Aceptar">
                    <input type="reset" class="btn btn-secondary" value="Limpiar">
                </div>
            </div>
        </fieldset>
    </form>
</div>
</body>

</html>
