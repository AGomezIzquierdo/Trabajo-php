<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$vacio = ["nombre" => "", "apellidos" => "", "dni" => "", "peso" => "", "alergias" => 0, "fecha_nacimiento" => "", "idaseguradora" => ""];
$datos = $vacio;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $datos = [
        "nombre" => trim($_POST["txtNombre"] ?? ""),
        "apellidos" => trim($_POST["txtApellidos"] ?? ""),
        "dni" => strtoupper(trim($_POST["txtDni"] ?? "")),
        "peso" => $_POST["txtPeso"] ?? "",
        "alergias" => isset($_POST["chkAlergias"]) ? 1 : 0,
        "fecha_nacimiento" => $_POST["txtFecha"] ?? "",
        "idaseguradora" => $_POST["lstAseguradora"] ?? "",
    ];
    $imagen = subirImagen("fileImagen");
    if ($imagen["error"] !== "") {
        $mensaje = alerta($imagen["error"], "danger");
    } else {
        $resultado = ejecutar(
            $conexion,
            "INSERT INTO paciente (nombre, apellidos, dni, peso, alergias, fecha_nacimiento, imagen, idaseguradora) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$datos["nombre"], $datos["apellidos"], $datos["dni"], $datos["peso"], $datos["alergias"], $datos["fecha_nacimiento"], $imagen["nombre"], $datos["idaseguradora"]]
        );
        if ($resultado["ok"]) {
            $mensaje = alerta("Paciente " . $datos["nombre"] . " " . $datos["apellidos"] . " dado de alta con id " . $resultado["id"] . ".", "success");
            $datos = $vacio;
        } else {
            borrarImagen($imagen["nombre"]);
            $mensaje = alerta(mensajeError($resultado), "danger");
        }
    }
}

$aseguradoras = consultar($conexion, "SELECT idaseguradora, nombre FROM aseguradora WHERE activa = TRUE ORDER BY nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="alta_paciente.php" name="frmAltaPaciente" id="frmAltaPaciente" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend>Alta de paciente</legend>
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
                <label class="col-sm-3 col-form-label" for="txtDni">DNI</label>
                <div class="col-sm-6">
                    <input id="txtDni" name="txtDni" value="<?php echo e($datos["dni"]); ?>" placeholder="12345678A" class="form-control" pattern="[0-9]{8}[A-Za-z]" title="8 números y una letra" maxlength="9" type="text" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="lstAseguradora">Aseguradora</label>
                <div class="col-sm-6">
                    <select name="lstAseguradora" id="lstAseguradora" class="form-select" required>
                        <option value="">Selecciona una aseguradora</option>
                        <?php echo opciones($aseguradoras, "idaseguradora", "nombre", $datos["idaseguradora"]); ?>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtPeso">Peso (kg)</label>
                <div class="col-sm-6">
                    <input id="txtPeso" name="txtPeso" value="<?php echo e($datos["peso"]); ?>" placeholder="Peso" class="form-control" type="number" step="0.01" min="0" max="400" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtFecha">Fecha de nacimiento</label>
                <div class="col-sm-6">
                    <input id="txtFecha" name="txtFecha" value="<?php echo e($datos["fecha_nacimiento"]); ?>" class="form-control" type="date" max="<?php echo date("Y-m-d"); ?>" required>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-6 offset-sm-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="chkAlergias" name="chkAlergias" <?php echo $datos["alergias"] ? "checked" : ""; ?>>
                        <label class="form-check-label" for="chkAlergias">Tiene alergias</label>
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
                    <input type="submit" id="btnAceptarAltaPaciente" name="btnAceptarAltaPaciente" class="btn btn-primary" value="Aceptar">
                    <input type="reset" class="btn btn-secondary" value="Limpiar">
                </div>
            </div>
        </fieldset>
    </form>
</div>
</body>

</html>
