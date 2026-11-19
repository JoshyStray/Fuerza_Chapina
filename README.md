# Fuerza Chapina · Sistema de gestión de membresías

Aplicación web full-stack en PHP 8.0 o superior y MySQL/MariaDB, con arquitectura Modelo-Vista-Controlador. Administra socios, planes, membresías, asistencias, usuarios y reportes del gimnasio *Fuerza Chapina*, Escuintla, Guatemala.

Proyecto final de Programación Web · Licenciatura en Administración de Sistemas Informáticos · Universidad Regional de Guatemala · 2S2026.

## Requisitos
- XAMPP con PHP 8.0 o superior (probado con PHP 8.3) y MariaDB 10.11 o MySQL 8.
- Extensiones de PHP activas en `php.ini`: `pdo_mysql` y `mbstring`. Si falta alguna, la aplicación lo indica en pantalla.
- Navegador moderno. No requiere internet: Bootstrap 5.3.3 está incluido en `public/vendor/`.

## Instalación
1. Copiar la carpeta del proyecto en `C:\xampp\htdocs\fuerza-chapina`.
2. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
3. Crear la base de datos, el usuario de la aplicación y los datos de prueba:
   - Terminal: `C:\xampp\mysql\bin\mysql.exe -u root < database.sql`
   - phpMyAdmin: pestaña *Importar* → seleccionar `database.sql`.
4. Abrir **http://localhost/fuerza-chapina/** e iniciar sesión.

La aplicación se conecta con el usuario `fc_app`, que solo tiene `SELECT`, `INSERT`, `UPDATE` y `DELETE` sobre la base `fuerza_chapina`. No usa `root`. Sus credenciales están en `config/database.php`.

## Usuarios de prueba
| Rol | Usuario | Contraseña | Alcance |
|---|---|---|---|
| Administrador | `admin` | `Admin2026!` | Todo el sistema |
| Recepcionista | `recepcion` | `Recep2026!` | Recepción, socios, membresías, asistencias, planes (consulta) y reportes |

## Permisos por módulo
| Módulo | Administrador | Recepcionista |
|---|---|---|
| Recepción (semáforo AJAX y registro de asistencia) | Sí | Sí |
| Socios | Crear, editar, activar/desactivar, eliminar* | Crear, editar |
| Planes y precios | Crear, editar, activar/desactivar, eliminar* | Solo consulta |
| Membresías | Registrar, editar, eliminar | Registrar |
| Asistencias | Crear, editar, eliminar | Crear |
| Usuarios | Crear, editar, activar/desactivar, eliminar* | Sin acceso |
| Reportes | Consultar | Consultar |

\* Solo si no tiene registros asociados. Si los tiene, se desactiva (borrado lógico) para conservar el historial.

## Reglas de negocio
- **RN-01.** `fecha_fin = fecha_inicio + duración del plan`. La calcula el servidor.
- **RN-02.** Si el socio renueva con la membresía vigente, la nueva inicia el día siguiente al vencimiento.
- **RN-03.** No se registra asistencia sin membresía vigente en esa fecha.
- **RN-04.** Una asistencia por socio por día. La base de datos lo garantiza con `UNIQUE (socio_id, fecha)`, y el servidor lo valida con un mensaje claro.

## Estructura