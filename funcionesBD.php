<?php
require_once("config.php");

function obtenerConexion()
{
    try {
        $conexion = new PDO(
            "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => true,
            ]
        );
        $conexion->exec("SET search_path TO " . DB_SCHEMA . "; SET client_encoding TO 'UTF8'; SET TIME ZONE 'Europe/Madrid'");
        return $conexion;
    } catch (PDOException $ex) {
        die("<h2 style='text-align:center;margin-top:50px'>Error de conexión con la base de datos: " . htmlspecialchars($ex->getMessage()) . "</h2>");
    }
}

function consultar($conexion, $sql, $parametros = [])
{
    try {
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($parametros);
        return $sentencia->fetchAll();
    } catch (PDOException $ex) {
        return [];
    }
}

function consultarUno($conexion, $sql, $parametros = [])
{
    $filas = consultar($conexion, $sql, $parametros);
    return count($filas) > 0 ? $filas[0] : null;
}

function ejecutar($conexion, $sql, $parametros = [])
{
    try {
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute($parametros);
        $id = stripos(ltrim($sql), "INSERT") === 0 ? $conexion->lastInsertId() : 0;
        return ["ok" => true, "numerror" => "", "error" => "", "id" => $id, "filas" => $sentencia->rowCount()];
    } catch (PDOException $ex) {
        return ["ok" => false, "numerror" => $ex->errorInfo[0] ?? $ex->getCode(), "error" => $ex->errorInfo[2] ?? $ex->getMessage(), "id" => 0, "filas" => 0];
    }
}

function mensajeError($resultado)
{
    switch ($resultado["numerror"]) {
        case "23505":
            return "Ya existe un registro con ese valor único (DNI, nº de colegiado...).";
        case "23503":
            return stripos($resultado["error"], "delete") !== false || stripos($resultado["error"], "still referenced") !== false
                ? "No se puede borrar porque tiene registros relacionados."
                : "El valor seleccionado en el desplegable no existe.";
        case "22P02":
        case "22007":
        case "22008":
            return "Alguno de los datos introducidos no tiene un formato válido.";
        default:
            return "Se ha producido un error " . $resultado["numerror"] . ": " . $resultado["error"];
    }
}

function e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function alerta($texto, $tipo = "info")
{
    return "<div class='alert alert-$tipo alert-dismissible fade show' role='alert'>" . e($texto) .
        "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Cerrar'></button></div>";
}

function opciones($filas, $campoValor, $campoTexto, $seleccionado = null)
{
    $html = "";
    foreach ($filas as $fila) {
        $marcado = ((string) $fila[$campoValor] === (string) $seleccionado) ? " selected" : "";
        $html .= "<option value='" . e($fila[$campoValor]) . "'$marcado>" . e($fila[$campoTexto]) . "</option>";
    }
    return $html;
}

function subirImagen($campo, $obligatoria = true)
{
    if (!isset($_FILES[$campo]) || $_FILES[$campo]["error"] === UPLOAD_ERR_NO_FILE) {
        return ["nombre" => null, "error" => $obligatoria ? "Debes seleccionar una imagen." : ""];
    }
    $fichero = $_FILES[$campo];
    if ($fichero["error"] !== UPLOAD_ERR_OK) {
        return ["nombre" => null, "error" => "No se ha podido subir la imagen (código " . $fichero["error"] . ")."];
    }
    if ($fichero["size"] > TAMANO_MAXIMO_IMAGEN) {
        return ["nombre" => null, "error" => "La imagen no puede superar los 2 MB."];
    }
    $tipos = ["image/jpeg" => "jpg", "image/png" => "png", "image/gif" => "gif", "image/webp" => "webp"];
    $info = @getimagesize($fichero["tmp_name"]);
    if ($info === false || !isset($tipos[$info["mime"]])) {
        return ["nombre" => null, "error" => "El fichero debe ser una imagen JPG, PNG, GIF o WEBP."];
    }
    if (!is_dir(CARPETA_IMAGENES)) {
        mkdir(CARPETA_IMAGENES, 0775, true);
    }
    $nombre = "img_" . bin2hex(random_bytes(8)) . "." . $tipos[$info["mime"]];
    if (!move_uploaded_file($fichero["tmp_name"], CARPETA_IMAGENES . $nombre)) {
        return ["nombre" => null, "error" => "No se ha podido guardar la imagen en el servidor."];
    }
    return ["nombre" => $nombre, "error" => ""];
}

function borrarImagen($nombre)
{
    $protegidas = ["medico.png", "paciente.png", "cita.png", "aseguradora.png", "sin_imagen.png"];
    if ($nombre && !in_array($nombre, $protegidas) && basename($nombre) === $nombre && is_file(CARPETA_IMAGENES . $nombre)) {
        unlink(CARPETA_IMAGENES . $nombre);
    }
}

function imagenHtml($nombre, $ancho = 70)
{
    $ruta = ($nombre && is_file(CARPETA_IMAGENES . $nombre)) ? URL_IMAGENES . $nombre : URL_IMAGENES . "sin_imagen.png";
    return "<img src='" . e($ruta) . "' alt='imagen' class='img-thumbnail' style='width:{$ancho}px;height:{$ancho}px;object-fit:cover'>";
}

function siNo($valor)
{
    return $valor ? "<span class='badge text-bg-success'>Sí</span>" : "<span class='badge text-bg-secondary'>No</span>";
}

function fecha($valor)
{
    return $valor ? date("d/m/Y", strtotime($valor)) : "";
}

function fechaHora($valor)
{
    return $valor ? date("d/m/Y H:i", strtotime($valor)) : "";
}

function euros($valor)
{
    return number_format((float) $valor, 2, ",", ".") . " €";
}

function tabla($columnas, $filas, $acciones = null)
{
    if (count($filas) === 0) {
        return alerta("No se han encontrado registros.", "warning");
    }
    $html = "<div class='table-responsive'><table class='table table-striped table-hover align-middle'><thead class='table-dark'><tr>";
    foreach ($columnas as $titulo => $campo) {
        $html .= "<th>" . e($titulo) . "</th>";
    }
    if ($acciones) {
        $html .= "<th>Acción</th>";
    }
    $html .= "</tr></thead><tbody>";
    foreach ($filas as $fila) {
        $html .= "<tr>";
        foreach ($columnas as $campo) {
            $html .= "<td>" . ($campo instanceof Closure ? $campo($fila) : e($fila[$campo])) . "</td>";
        }
        if ($acciones) {
            $html .= "<td>" . $acciones($fila) . "</td>";
        }
        $html .= "</tr>";
    }
    return $html . "</tbody></table></div><p class='text-muted'>" . count($filas) . " registro(s) encontrado(s)</p>";
}

function botonBorrar($pagina, $id)
{
    return "<form action='" . e($pagina) . "' method='post' class='d-inline' onsubmit=\"return confirm('¿Seguro que quieres borrar el registro $id?');\">" .
        "<input type='hidden' name='id' value='" . e($id) . "'>" .
        "<button type='submit' class='btn btn-danger btn-sm'><i class='bi bi-trash'></i> Borrar</button></form>";
}

function botonEditar($pagina, $id)
{
    return "<a href='" . e($pagina) . "?id=" . urlencode($id) . "' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Modificar</a>";
}

function columnasMedico()
{
    return [
        "Imagen" => fn($f) => imagenHtml($f["imagen"]),
        "ID" => "idmedico",
        "Nombre" => fn($f) => e($f["nombre"] . " " . $f["apellidos"]),
        "Nº colegiado" => "num_colegiado",
        "Especialidad" => "especialidad",
        "Salario" => fn($f) => euros($f["salario"]),
        "Disponible" => fn($f) => siNo($f["disponible"]),
        "Contratación" => fn($f) => fecha($f["fecha_contratacion"]),
    ];
}

function columnasPaciente()
{
    return [
        "Imagen" => fn($f) => imagenHtml($f["imagen"]),
        "ID" => "idpaciente",
        "Nombre" => fn($f) => e($f["nombre"] . " " . $f["apellidos"]),
        "DNI" => "dni",
        "Peso" => fn($f) => number_format((float) $f["peso"], 2, ",", ".") . " kg",
        "Alergias" => fn($f) => siNo($f["alergias"]),
        "Nacimiento" => fn($f) => fecha($f["fecha_nacimiento"]),
        "Aseguradora" => "aseguradora",
    ];
}

function columnasCita()
{
    return [
        "Imagen" => fn($f) => imagenHtml($f["imagen"]),
        "ID" => "idcita",
        "Fecha y hora" => fn($f) => fechaHora($f["fecha_hora"]),
        "Motivo" => "motivo",
        "Paciente" => "paciente",
        "Médico" => "medico",
        "Precio" => fn($f) => euros($f["precio"]),
        "Pagada" => fn($f) => siNo($f["pagada"]),
    ];
}

function columnasAseguradora()
{
    return [
        "Imagen" => fn($f) => imagenHtml($f["imagen"]),
        "ID" => "idaseguradora",
        "Nombre" => "nombre",
        "Tipo" => "tipo",
        "Cuota mensual" => fn($f) => euros($f["cuota_mensual"]),
        "Activa" => fn($f) => siNo($f["activa"]),
        "Fecha de alta" => fn($f) => fecha($f["fecha_alta"]),
    ];
}

define("SQL_MEDICOS", "SELECT m.*, e.nombre AS especialidad FROM medico m INNER JOIN especialidad e ON m.idespecialidad = e.idespecialidad");
define("SQL_PACIENTES", "SELECT p.*, a.nombre AS aseguradora FROM paciente p INNER JOIN aseguradora a ON p.idaseguradora = a.idaseguradora");
define("SQL_CITAS", "SELECT c.*, CONCAT(p.nombre, ' ', p.apellidos) AS paciente, CONCAT(m.nombre, ' ', m.apellidos) AS medico FROM cita c INNER JOIN paciente p ON c.idpaciente = p.idpaciente INNER JOIN medico m ON c.idmedico = m.idmedico");
define("SQL_ASEGURADORAS", "SELECT a.*, t.nombre AS tipo FROM aseguradora a INNER JOIN tipo_aseguradora t ON a.idtipo_aseguradora = t.idtipo_aseguradora");
