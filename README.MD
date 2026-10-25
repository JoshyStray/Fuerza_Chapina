# Fuerza Chapina · Sistema de gestión de membresías

Aplicación web (PHP + MySQL, arquitectura MVC) para administrar socios, planes,
membresías y asistencias del gimnasio "Fuerza Chapina", Escuintla.

## Requisitos
- XAMPP con PHP 8.1 o superior, Apache y MariaDB/MySQL.
- Navegador moderno. No requiere internet (Bootstrap está incluido localmente).

## Instalación
1. Copiar la carpeta del proyecto en `C:\xampp\htdocs\fuerza-chapina`.
2. Iniciar Apache y MySQL desde el panel de XAMPP.
3. Importar `database.sql` (phpMyAdmin → Importar) o ejecutar:
   `C:\xampp\mysql\bin\mysql.exe -u root < database.sql`
4. Si tu MySQL tiene otra contraseña, ajustar `config/database.php`.
5. Abrir http://localhost/fuerza-chapina/

## Usuarios de prueba
| Rol           | Usuario   | Contraseña  |
|---------------|-----------|-------------|
| Administrador | admin     | Admin2026!  |
| Recepcionista | recepcion | Recep2026!  |

## Estructura
- `index.php`: front controller (`?c=controlador&a=accion`)
- `controllers/`, `models/`, `views/`: patrón MVC
- `core/`: base de datos (PDO), sesión, CSRF y utilidades
- `public/`: CSS, JS y Bootstrap local
- `database.sql`: crea la base de datos y los datos de prueba

## Uso de inteligencia artificial
Se utilizó Claude (Anthropic) como apoyo para estructurar el proyecto, depurar y
explicar código. Todo el código fue revisado y es comprendido por el autor.

## Código de terceros
- Bootstrap 5.3 (MIT), descargado localmente en `public/vendor/bootstrap/`.

## Capturas de pantalla
_(pendiente)_