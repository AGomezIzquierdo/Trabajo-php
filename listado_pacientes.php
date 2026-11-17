<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$pacientes = consultar($conexion, SQL_PACIENTES . " ORDER BY p.apellidos, p.nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Listado de pacientes</h2>
        <a href="alta_paciente.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo paciente</a>
    </div>
    <?php echo tabla(columnasPaciente(), $pacientes, fn($f) => botonEditar("modificar_paciente.php", $f["idpaciente"]) . " " . botonBorrar("baja_paciente.php", $f["idpaciente"])); ?>
</div>
</body>

</html>
