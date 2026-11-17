<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$citas = consultar($conexion, SQL_CITAS . " ORDER BY c.fecha_hora DESC");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Listado de citas</h2>
        <a href="alta_citas.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva cita</a>
    </div>
    <?php echo tabla(columnasCita(), $citas, fn($f) => botonEditar("modificar_cita.php", $f["idcita"]) . " " . botonBorrar("baja_citas.php", $f["idcita"])); ?>
</div>
</body>

</html>
