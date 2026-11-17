<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$mensaje = "";
$buscar = trim($_GET["txtBuscar"] ?? "");
$id = $_POST["id"] ?? ($_GET["id"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST" && $id !== "") {
    $actual = consultarUno($conexion, "SELECT imagen FROM aseguradora WHERE idaseguradora = ?", [$id]);
    if (!$actual) {
        $mensaje = alerta("La aseguradora indicada no existe.", "danger");
    } else {
        $imagen = subirImagen("fileImagen", false);
        if ($imagen["error"] !== "") {
            $mensaje = alerta($imagen["error"], "danger");
        } else {
            $nuevaImagen = $imagen["nombre"] ?? $actual["imagen"];
            $resultado = ejecutar(
                $conexion,
                "UPDATE aseguradora SET nombre = ?, cuota_mensual = ?, activa = ?, fecha_alta = ?, imagen = ?, idtipo_aseguradora = ? WHERE idaseguradora = ?",
                [trim($_POST["txtNombre"]), $_POST["txtCuota"], isset($_POST["chkActiva"]) ? 1 : 0, $_POST["txtFecha"], $nuevaImagen, $_POST["lstTipo"], $id]
            );
            if ($resultado["ok"]) {
                if ($imagen["nombre"]) {
                    borrarImagen($actual["imagen"]);
                }
                $mensaje = alerta("Aseguradora actualizada correctamente.", "success");
            } else {
                borrarImagen($imagen["nombre"]);
                $mensaje = alerta(mensajeError($resultado), "danger");
            }
        }
    }
}

$aseguradora = $id !== "" ? consultarUno($conexion, SQL_ASEGURADORAS . " WHERE a.idaseguradora = ?", [$id]) : null;
if ($id !== "" && !$aseguradora && $mensaje === "") {
    $mensaje = alerta("No existe ninguna aseguradora con id $id.", "warning");
}

$resultados = [];
if ($buscar !== "") {
    $resultados = consultar($conexion, SQL_ASEGURADORAS . " WHERE a.nombre ILIKE ? ORDER BY a.nombre", ["%$buscar%"]);
}

$tipos = consultar($conexion, "SELECT idtipo_aseguradora, nombre FROM tipo_aseguradora ORDER BY nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <?php echo $mensaje; ?>
    <form action="modificar_aseguradora.php" method="get" class="mb-4">
        <fieldset>
            <legend>Buscar aseguradora</legend>
            <div class="row g-2">
                <div class="col-sm-6">
                    <input id="txtBuscar" name="txtBuscar" value="<?php echo e($buscar); ?>" placeholder="Nombre de la aseguradora" class="form-control" type="search" required <?php echo $aseguradora ? "" : "autofocus"; ?>>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </fieldset>
    </form>

    <?php
    if ($buscar !== "") {
        echo tabla(columnasAseguradora(), $resultados, fn($f) => botonEditar("modificar_aseguradora.php", $f["idaseguradora"]));
    }
    ?>

    <?php if ($aseguradora) { ?>
        <form action="modificar_aseguradora.php" name="frmModificarAseguradora" id="frmModificarAseguradora" method="post" enctype="multipart/form-data">
            <fieldset>
                <legend>Modificar aseguradora #<?php echo e($aseguradora["idaseguradora"]); ?></legend>
                <input type="hidden" name="id" value="<?php echo e($aseguradora["idaseguradora"]); ?>">
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtNombre">Nombre</label>
                    <div class="col-sm-6">
                        <input id="txtNombre" name="txtNombre" value="<?php echo e($aseguradora["nombre"]); ?>" class="form-control" maxlength="60" type="text" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="lstTipo">Tipo</label>
                    <div class="col-sm-6">
                        <select name="lstTipo" id="lstTipo" class="form-select" required>
                            <?php echo opciones($tipos, "idtipo_aseguradora", "nombre", $aseguradora["idtipo_aseguradora"]); ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtCuota">Cuota mensual (€)</label>
                    <div class="col-sm-6">
                        <input id="txtCuota" name="txtCuota" value="<?php echo e($aseguradora["cuota_mensual"]); ?>" class="form-control" type="number" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="txtFecha">Fecha de alta</label>
                    <div class="col-sm-6">
                        <input id="txtFecha" name="txtFecha" value="<?php echo e($aseguradora["fecha_alta"]); ?>" class="form-control" type="date" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-sm-6 offset-sm-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="chkActiva" name="chkActiva" <?php echo $aseguradora["activa"] ? "checked" : ""; ?>>
                            <label class="form-check-label" for="chkActiva">Activa</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="fileImagen">Logotipo</label>
                    <div class="col-sm-2">
                        <?php echo imagenHtml($aseguradora["imagen"], 90); ?>
                    </div>
                    <div class="col-sm-4">
                        <input id="fileImagen" name="fileImagen" class="form-control" type="file" accept="image/*">
                        <div class="form-text">Déjalo vacío para mantener la imagen actual.</div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col-sm-6 offset-sm-3">
                        <input type="submit" id="btnAceptarModificarAseguradora" name="btnAceptarModificarAseguradora" class="btn btn-primary" value="Guardar cambios">
                        <a href="modificar_aseguradora.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </fieldset>
        </form>
    <?php } ?>
</div>
</body>

</html>
