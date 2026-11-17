<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$idpaciente = $_GET["lstPaciente"] ?? "";
$pagada = $_GET["lstPagada"] ?? "";
$desde = $_GET["txtDesde"] ?? "";
$hasta = $_GET["txtHasta"] ?? "";

$pacientes = consultar($conexion, "SELECT idpaciente, CONCAT(apellidos, ', ', nombre, ' (', dni, ')') AS texto FROM paciente ORDER BY apellidos, nombre");

$citas = null;
$total = 0;
if (isset($_GET["btnBuscar"])) {
    $sql = SQL_CITAS . " WHERE 1 = 1";
    $parametros = [];
    if ($idpaciente !== "") {
        $sql .= " AND c.idpaciente = ?";
        $parametros[] = $idpaciente;
    }
    if ($pagada !== "") {
        $sql .= " AND c.pagada = ?";
        $parametros[] = $pagada;
    }
    if ($desde !== "") {
        $sql .= " AND c.fecha_hora >= ?";
        $parametros[] = $desde . " 00:00:00";
    }
    if ($hasta !== "") {
        $sql .= " AND c.fecha_hora <= ?";
        $parametros[] = $hasta . " 23:59:59";
    }
    $citas = consultar($conexion, $sql . " ORDER BY c.fecha_hora DESC", $parametros);
    foreach ($citas as $c) {
        $total += $c["precio"];
    }
}

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <form action="listar_citas_por_pacientes.php" method="get" class="mb-4">
        <fieldset>
            <legend>Listado de citas por paciente</legend>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="lstPaciente">Paciente</label>
                    <select name="lstPaciente" id="lstPaciente" class="form-select">
                        <option value="">Todos</option>
                        <?php echo opciones($pacientes, "idpaciente", "texto", $idpaciente); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="lstPagada">Pagada</label>
                    <select name="lstPagada" id="lstPagada" class="form-select">
                        <option value="" <?php echo $pagada === "" ? "selected" : ""; ?>>Indiferente</option>
                        <option value="1" <?php echo $pagada === "1" ? "selected" : ""; ?>>Sí</option>
                        <option value="0" <?php echo $pagada === "0" ? "selected" : ""; ?>>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="txtDesde">Desde</label>
                    <input id="txtDesde" name="txtDesde" value="<?php echo e($desde); ?>" class="form-control" type="date">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="txtHasta">Hasta</label>
                    <input id="txtHasta" name="txtHasta" value="<?php echo e($hasta); ?>" class="form-control" type="date">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="btnBuscar" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                </div>
            </div>
        </fieldset>
    </form>
    <?php
    if ($citas !== null) {
        echo tabla(columnasCita(), $citas, fn($f) => botonEditar("modificar_cita.php", $f["idcita"]));
        if (count($citas) > 0) {
            echo "<p class='fw-bold'>Importe total: " . euros($total) . "</p>";
        }
    }
    ?>
</div>
</body>

</html>
