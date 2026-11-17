<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$idtipo = $_GET["lstTipo"] ?? "";
$soloActivas = isset($_GET["chkActiva"]);
$cuotaMaxima = $_GET["txtCuotaMaxima"] ?? "";

$tipos = consultar($conexion, "SELECT idtipo_aseguradora, nombre FROM tipo_aseguradora ORDER BY nombre");

$aseguradoras = null;
if (isset($_GET["btnBuscar"])) {
    $sql = SQL_ASEGURADORAS . " WHERE 1 = 1";
    $parametros = [];
    if ($idtipo !== "") {
        $sql .= " AND a.idtipo_aseguradora = ?";
        $parametros[] = $idtipo;
    }
    if ($soloActivas) {
        $sql .= " AND a.activa = TRUE";
    }
    if ($cuotaMaxima !== "") {
        $sql .= " AND a.cuota_mensual <= ?";
        $parametros[] = $cuotaMaxima;
    }
    $aseguradoras = consultar($conexion, $sql . " ORDER BY a.cuota_mensual", $parametros);
}

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <form action="listar_aseguradora_por_tipo_aseguradora.php" method="get" class="mb-4">
        <fieldset>
            <legend>Listado de aseguradoras por tipo</legend>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="lstTipo">Tipo de aseguradora</label>
                    <select name="lstTipo" id="lstTipo" class="form-select">
                        <option value="">Todos</option>
                        <?php echo opciones($tipos, "idtipo_aseguradora", "nombre", $idtipo); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="txtCuotaMaxima">Cuota máxima (€)</label>
                    <input id="txtCuotaMaxima" name="txtCuotaMaxima" value="<?php echo e($cuotaMaxima); ?>" class="form-control" type="number" step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="chkActiva" name="chkActiva" <?php echo $soloActivas ? "checked" : ""; ?>>
                        <label class="form-check-label" for="chkActiva">Solo activas</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="btnBuscar" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                </div>
            </div>
        </fieldset>
    </form>
    <?php
    if ($aseguradoras !== null) {
        echo tabla(columnasAseguradora(), $aseguradoras, fn($f) => botonEditar("modificar_aseguradora.php", $f["idaseguradora"]));
    }
    ?>
</div>
</body>

</html>
