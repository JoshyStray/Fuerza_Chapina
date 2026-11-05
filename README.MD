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
_(pendiente)_# Fuerza_Chapina

## Checklist de seguridad (OWASP Top 10)

| Control | Dónde está | Cómo probarlo |
|---|---|---|
| Inyección SQL: consultas preparadas | Todos los modelos (`models/*.php`, `prepare()` con parámetros) | Buscar `' OR 1=1 --` en Membresías: 0 resultados y sin error |
| XSS: escape de salida | `e()` en las vistas; `textContent` en `recepcion.js` | Poner `<script>alert(1)</script>` como nombre de un socio: se muestra como texto |
| CSRF | Token por sesión en formularios (`Csrf::campo()`) y cabecera `X-CSRF-Token` en `fetch()` | POST sin token: responde 403 |
| Contraseñas con hash | `password_hash` / `password_verify` (bcrypt) | La columna `password_hash` nunca guarda texto plano |
| Sesión segura | Cookie HttpOnly + SameSite, ID estricto, regeneración al entrar y salir, cierre a los 30 min de inactividad | Ver la cookie `FCSESSID` en las herramientas del navegador |
| Fuerza bruta | `IntentoLogin`: 5 intentos fallidos por usuario e IP en 10 min bloquean el acceso (HTTP 429) | 6 contraseñas incorrectas seguidas |
| Control de acceso por rol (servidor) | `requerirRol()` en los controladores | Con `recepcion`, abrir `?c=usuario&a=listar`: 403 |
| Cabeceras de seguridad | CSP, X-Frame-Options, nosniff, Referrer-Policy, Cache-Control (`index.php`) | Pestaña Red de las herramientas del navegador |
| Errores sin detalles técnicos | `try/catch` en `index.php`; `display_errors` apagado; registro en `storage/logs/app.log` | Cualquier fallo muestra un mensaje genérico |
| Validación en el servidor | `MembresiaController::validar()` y reglas RN-01 a RN-04 | Desactivar la validación HTML5 y enviar datos inválidos |
| Credenciales fuera de las vistas | `config/database.php`; carpetas internas bloqueadas con `.htaccess` | Abrir `/fuerza-chapina/config/database.php` en el navegador: 403 |
| Sin dependencias externas | Bootstrap local en `public/vendor/` | Funciona sin internet |

### Si el login queda bloqueado
Ejecutar en phpMyAdmin: `DELETE FROM intentos_login;`
