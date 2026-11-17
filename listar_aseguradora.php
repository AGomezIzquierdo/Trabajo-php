<?php
require_once("funcionesBD.php");
$conexion = obtenerConexion();

$aseguradoras = consultar($conexion, SQL_ASEGURADORAS . " ORDER BY a.nombre");

include_once("cabecera.html");
?>
<div class="container" id="formularios">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Listado de aseguradoras</h2>
        <a href="alta_aseguradora.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nueva aseguradora</a>
    </div>
    <?php echo tabla(columnasAseguradora(), $aseguradoras, fn($f) => botonEditar("modificar_aseguradora.php", $f["idaseguradora"]) . " " . botonBorrar("baja_aseguradora.php", $f["idaseguradora"])); ?>
</div>
</body>

</html>
