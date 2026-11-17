# Clínica Vitalia

Aplicación web en PHP + PostgreSQL (Supabase) + Bootstrap 5 para la gestión de una clínica: médicos, pacientes, citas y aseguradoras.

## Enlaces

- GitHub: https://github.com/AGomezIzquierdo/Trabajo-php
- Alojamiento web: _(pendiente de añadir la URL del hosting)_

## Conexión a la base de datos

Base de datos PostgreSQL alojada en Supabase (Session pooler).

| Parámetro | Valor |
|-----------|-------|
| Servidor | `aws-1-eu-west-1.pooler.supabase.com` |
| Puerto | `5432` |
| Base de datos | `postgres` |
| Esquema | `clinica` |
| Usuario | `postgres.gjztlpvftfwjotioweyu` |
| Contraseña | _(la del proyecto de Supabase)_ |

Los datos se configuran en `config.php`. La contraseña se lee de la variable de entorno `DB_PASS`; si no existe, se usa el valor escrito en `config.php`.

## Alumnos y entidad de la que es responsable

| Alumno | Entidad |
|--------|---------|
| Gabriel Vigo | Médicos |
| Alejandro Gómez | Pacientes |
| Juan Antonio | Citas |
| David Jiménez | Aseguradoras |

## Modelo de datos

- `especialidad` 1:N `medico`
- `tipo_aseguradora` 1:N `aseguradora`
- `aseguradora` 1:N `paciente`
- `paciente` 1:N `cita`
- `medico` 1:N `cita`

Todas las tablas tienen clave primaria entera autoincrementada y columnas de tipo número, texto, booleano y fecha. Cada médico, paciente, cita y aseguradora tiene una imagen asociada que se muestra en los listados.

## Funcionalidades por entidad

| Operación | Médicos | Pacientes | Citas | Aseguradoras |
|-----------|---------|-----------|-------|--------------|
| Alta (con desplegable desde BD) | `alta_medico.php` | `alta_paciente.php` | `alta_citas.php` | `alta_aseguradora.php` |
| Consulta y modificación | `modificar_medico.php` | `modificar_paciente.php` | `modificar_cita.php` | `modificar_aseguradora.php` |
| Borrado (por ID u otro campo) | `baja_medico.php` | `baja_paciente.php` | `baja_citas.php` | `baja_aseguradora.php` |
| Listado completo | `listado_medicos.php` | `listado_pacientes.php` | `listar_citas.php` | `listar_aseguradora.php` |
| Listado parametrizado | `listado_medico_por_especialidad.php` | `listado_paciente_por_aseguradora.php` | `listar_citas_por_pacientes.php` | `listar_aseguradora_por_tipo_aseguradora.php` |

## Instalación

1. En Supabase, abrir **SQL Editor**, pegar el contenido de `sql/backup.sql` y ejecutarlo. Crea el esquema `clinica`, las tablas, las relaciones y 10 registros por tabla.
2. Poner la contraseña de la base de datos en `config.php` o en la variable de entorno `DB_PASS`.
3. Copiar el proyecto en el servidor web y dar permisos de escritura a la carpeta `imagenes/` para que se puedan subir imágenes.
4. Requiere PHP 8.1 o superior con la extensión `pdo_pgsql` activada (en XAMPP: descomentar `extension=pdo_pgsql` en `php.ini` y reiniciar Apache).

## Estructura

```
index.php
config.php
funcionesBD.php
cabecera.html
alta_*.php, baja_*.php, modificar_*.php, listado_*.php, listar_*.php
imagenes/
sql/
    backup.sql
    diagrama_ER.png
README.MD
```
