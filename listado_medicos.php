<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$medicos = consultar($conexion, SQL_MEDICOS . " ORDER BY m.apellidos, m.nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Listado de médicos</h2>
        <a href="alta_medico.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo médico</a>
    </div>
    <?php echo tabla(columnasMedico(), $medicos, fn($f) => botonEditar("modificar_medico.php", $f["idmedico"]) . " " . botonBorrar("baja_medico.php", $f["idmedico"])); ?>
</div>
</body>

</html>
