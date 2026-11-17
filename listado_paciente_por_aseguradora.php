<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$idaseguradora = $_GET["lstAseguradora"] ?? "";
$alergias = $_GET["lstAlergias"] ?? "";
$nacidoDesde = $_GET["txtDesde"] ?? "";
$nacidoHasta = $_GET["txtHasta"] ?? "";

$aseguradoras = consultar($conexion, "SELECT idaseguradora, nombre FROM aseguradora ORDER BY nombre");

$pacientes = null;
if (isset($_GET["btnBuscar"])) {
    $sql = SQL_PACIENTES . " WHERE 1 = 1";
    $parametros = [];
    if ($idaseguradora !== "") {
        $sql .= " AND p.idaseguradora = ?";
        $parametros[] = $idaseguradora;
    }
    if ($alergias !== "") {
        $sql .= " AND p.alergias = ?";
        $parametros[] = $alergias;
    }
    if ($nacidoDesde !== "") {
        $sql .= " AND p.fecha_nacimiento >= ?";
        $parametros[] = $nacidoDesde;
    }
    if ($nacidoHasta !== "") {
        $sql .= " AND p.fecha_nacimiento <= ?";
        $parametros[] = $nacidoHasta;
    }
    $pacientes = consultar($conexion, $sql . " ORDER BY a.nombre, p.apellidos", $parametros);
}

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <form action="listado_paciente_por_aseguradora.php" method="get" class="mb-4">
        <fieldset>
            <legend>Listado de pacientes por aseguradora</legend>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="lstAseguradora">Aseguradora</label>
                    <select name="lstAseguradora" id="lstAseguradora" class="form-select">
                        <option value="">Todas</option>
                        <?php echo opciones($aseguradoras, "idaseguradora", "nombre", $idaseguradora); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="lstAlergias">Alergias</label>
                    <select name="lstAlergias" id="lstAlergias" class="form-select">
                        <option value="" <?php echo $alergias === "" ? "selected" : ""; ?>>Indiferente</option>
                        <option value="1" <?php echo $alergias === "1" ? "selected" : ""; ?>>Sí</option>
                        <option value="0" <?php echo $alergias === "0" ? "selected" : ""; ?>>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="txtDesde">Nacido desde</label>
                    <input id="txtDesde" name="txtDesde" value="<?php echo e($nacidoDesde); ?>" class="form-control" type="date">
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="txtHasta">Nacido hasta</label>
                    <input id="txtHasta" name="txtHasta" value="<?php echo e($nacidoHasta); ?>" class="form-control" type="date">
                </div>
                <div class="col-md-3">
                    <button type="submit" name="btnBuscar" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                </div>
            </div>
        </fieldset>
    </form>
    <?php
    if ($pacientes !== null) {
        echo tabla(columnasPaciente(), $pacientes, fn($f) => botonEditar("modificar_paciente.php", $f["idpaciente"]));
    }
    ?>
</div>
</body>

</html>
