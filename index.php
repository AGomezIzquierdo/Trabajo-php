<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$totales = consultarUno($conexion, "SELECT (SELECT COUNT(*) FROM medico) AS medicos, (SELECT COUNT(*) FROM paciente) AS pacientes, (SELECT COUNT(*) FROM cita) AS citas, (SELECT COUNT(*) FROM aseguradora) AS aseguradoras");
$proximas = consultar($conexion, SQL_CITAS . " WHERE c.fecha_hora >= NOW() ORDER BY c.fecha_hora LIMIT 5");

$tarjetas = [
    ["Médicos", "bi-person-badge", $totales["medicos"] ?? 0, "alta_medico.php", "listado_medicos.php"],
    ["Pacientes", "bi-people", $totales["pacientes"] ?? 0, "alta_paciente.php", "listado_pacientes.php"],
    ["Citas", "bi-calendar-check", $totales["citas"] ?? 0, "alta_citas.php", "listar_citas.php"],
    ["Aseguradoras", "bi-shield-plus", $totales["aseguradoras"] ?? 0, "alta_aseguradora.php", "listar_aseguradora.php"],
];

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <h1 class="mb-4">Gestión de la Clínica Vitalia</h1>
    <div class="row g-4">
        <?php foreach ($tarjetas as $t) { ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <i class="bi <?php echo $t[1]; ?> display-4 text-primary"></i>
                        <h5 class="card-title mt-2"><?php echo $t[0]; ?></h5>
                        <p class="display-6"><?php echo e($t[2]); ?></p>
                        <a href="<?php echo $t[3]; ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Alta</a>
                        <a href="<?php echo $t[4]; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-list-ul"></i> Listado</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <h3 class="mt-5">Próximas citas</h3>
    <?php echo tabla(columnasCita(), $proximas); ?>
</div>
</body>

</html>
