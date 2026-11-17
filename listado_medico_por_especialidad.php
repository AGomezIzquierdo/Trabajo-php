<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$idespecialidad = $_GET["lstEspecialidad"] ?? "";
$soloDisponibles = isset($_GET["chkDisponible"]);
$salarioMinimo = $_GET["txtSalarioMinimo"] ?? "";

$especialidades = consultar($conexion, "SELECT idespecialidad, nombre FROM especialidad ORDER BY nombre");

$medicos = null;
if (isset($_GET["btnBuscar"])) {
    $sql = SQL_MEDICOS . " WHERE 1 = 1";
    $parametros = [];
    if ($idespecialidad !== "") {
        $sql .= " AND m.idespecialidad = ?";
        $parametros[] = $idespecialidad;
    }
    if ($soloDisponibles) {
        $sql .= " AND m.disponible = TRUE";
    }
    if ($salarioMinimo !== "") {
        $sql .= " AND m.salario >= ?";
        $parametros[] = $salarioMinimo;
    }
    $medicos = consultar($conexion, $sql . " ORDER BY e.nombre, m.apellidos", $parametros);
}

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <form action="listado_medico_por_especialidad.php" method="get" class="mb-4">
        <fieldset>
            <legend>Listado de médicos por especialidad</legend>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="lstEspecialidad">Especialidad</label>
                    <select name="lstEspecialidad" id="lstEspecialidad" class="form-select">
                        <option value="">Todas</option>
                        <?php echo opciones($especialidades, "idespecialidad", "nombre", $idespecialidad); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="txtSalarioMinimo">Salario mínimo (€)</label>
                    <input id="txtSalarioMinimo" name="txtSalarioMinimo" value="<?php echo e($salarioMinimo); ?>" class="form-control" type="number" step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="chkDisponible" name="chkDisponible" <?php echo $soloDisponibles ? "checked" : ""; ?>>
                        <label class="form-check-label" for="chkDisponible">Solo disponibles</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="btnBuscar" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                </div>
            </div>
        </fieldset>
    </form>
    <?php
    if ($medicos !== null) {
        echo tabla(columnasMedico(), $medicos, fn($f) => botonEditar("modificar_medico.php", $f["idmedico"]));
    }
    ?>
</div>
</body>

</html>
