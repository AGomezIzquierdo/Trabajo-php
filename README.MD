# Clínica Vitalia

Aplicación web en PHP + PostgreSQL (Supabase) + Bootstrap 5 para la gestión de una clínica: médicos, pacientes, citas y aseguradoras.

## Enlaces

- GitHub: https://github.com/AGomezIzquierdo/Trabajo-php
- Alojamiento web (Vercel):https://trabajo-om6ld3w9k-proyecto-dam.vercel.app/

## Conexión a la base de datos

Base de datos PostgreSQL alojada en Supabase (Session pooler).

| Parámetro | Valor |
|-----------|-------|
| Servidor | `aws-1-eu-central-1.pooler.supabase.com` |
| Puerto | `5432` |
| Base de datos | `postgres` |
| Esquema | `clinica` |
| Usuario | `postgres.gtyejmbwottcxmhdvllu` |
| Contraseña | J35lAHv2a7LfHoJN |

Los datos se configuran en `config.php`. Las claves no están en el repositorio: se leen de las variables de entorno `DB_PASS` (contraseña de la base de datos) y `SUPABASE_KEY` (clave secreta de Supabase para subir imágenes a Storage). En local se pueden definir en un fichero `config.local.php` (no se sube a GitHub):

```php
<?php
putenv("DB_PASS=contraseña");
putenv("SUPABASE_KEY=clave_secreta");
```

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
2. Crear en **Storage** un bucket público llamado `imagenes`.
3. Definir `DB_PASS` y `SUPABASE_KEY` (variables de entorno o `config.local.php`).
4. Requiere PHP 8.1 o superior con las extensiones `pdo_pgsql` y `curl`.

### Desplegado en Vercel



