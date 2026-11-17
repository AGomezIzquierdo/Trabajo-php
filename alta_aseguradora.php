<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$vacio = ["nombre" => "", "cuota_mensual" => "", "activa" => 1, "fecha_alta" => date("Y-m-d"), "idtipo_aseguradora" => ""];
$datos = $vacio;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $datos = [
        "nombre" => trim($_POST["txtNombre"] ?? ""),
        "cuota_mensual" => $_POST["txtCuota"] ?? "",
        "activa" => isset($_POST["chkActiva"]) ? 1 : 0,
        "fecha_alta" => $_POST["txtFecha"] ?? "",
        "idtipo_aseguradora" => $_POST["lstTipo"] ?? "",
    ];
    $imagen = subirImagen("fileImagen");
    if ($imagen["error"] !== "") {
        $mensaje = alerta($imagen["error"], "danger");
    } else {
        $resultado = ejecutar(
            $conexion,
            "INSERT INTO aseguradora (nombre, cuota_mensual, activa, fecha_alta, imagen, idtipo_aseguradora) VALUES (?, ?, ?, ?, ?, ?)",
            [$datos["nombre"], $datos["cuota_mensual"], $datos["activa"], $datos["fecha_alta"], $imagen["nombre"], $datos["idtipo_aseguradora"]]
        );
        if ($resultado["ok"]) {
            $mensaje = alerta("Aseguradora " . $datos["nombre"] . " dada de alta con id " . $resultado["id"] . ".", "success");
            $datos = $vacio;
        } else {
            borrarImagen($imagen["nombre"]);
            $mensaje = alerta(mensajeError($resultado), "danger");
        }
    }
}

$tipos = consultar($conexion, "SELECT idtipo_aseguradora, nombre FROM tipo_aseguradora ORDER BY nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="alta_aseguradora.php" name="frmAltaAseguradora" id="frmAltaAseguradora" method="post" enctype="multipart/form-data">
        <fieldset>
            <legend>Alta de aseguradora</legend>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtNombre">Nombre</label>
                <div class="col-sm-6">
                    <input id="txtNombre" name="txtNombre" value="<?php echo e($datos["nombre"]); ?>" placeholder="Nombre de la aseguradora" class="form-control" maxlength="60" type="text" required autofocus>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="lstTipo">Tipo</label>
                <div class="col-sm-6">
                    <select name="lstTipo" id="lstTipo" class="form-select" required>
                        <option value="">Selecciona un tipo</option>
                        <?php echo opciones($tipos, "idtipo_aseguradora", "nombre", $datos["idtipo_aseguradora"]); ?>
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtCuota">Cuota mensual (€)</label>
                <div class="col-sm-6">
                    <input id="txtCuota" name="txtCuota" value="<?php echo e($datos["cuota_mensual"]); ?>" placeholder="Cuota mensual" class="form-control" type="number" step="0.01" min="0" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="txtFecha">Fecha de alta</label>
                <div class="col-sm-6">
                    <input id="txtFecha" name="txtFecha" value="<?php echo e($datos["fecha_alta"]); ?>" class="form-control" type="date" required>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-6 offset-sm-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="chkActiva" name="chkActiva" <?php echo $datos["activa"] ? "checked" : ""; ?>>
                        <label class="form-check-label" for="chkActiva">Activa</label>
                    </div>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label" for="fileImagen">Logotipo</label>
                <div class="col-sm-6">
                    <input id="fileImagen" name="fileImagen" class="form-control" type="file" accept="image/*" required>
                </div>
            </div>
            <div class="mb-3 row">
                <div class="col-sm-6 offset-sm-3">
                    <input type="submit" id="btnAceptarAltaAseguradora" name="btnAceptarAltaAseguradora" class="btn btn-primary" value="Aceptar">
                    <input type="reset" class="btn btn-secondary" value="Limpiar">
                </div>
            </div>
        </fieldset>
    </form>
</div>
</body>

</html>
