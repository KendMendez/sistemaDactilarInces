# Commits - Sistema de Autenticación Mejorado

---

## Cambio 1: Migración de JWT Key a variables de entorno

### ¿Qué cambios se realizaron?
Se movió la clave JWT hardcodeada desde `EmpleadoAuthService.php` a una variable de entorno en `.env`.

### ¿Por qué se realizaron estos cambios?
Por seguridad. Las credenciales sensibles no deben estar hardcodeadas en el código fuente.

### ¿Cómo se realizaron estos cambios?
- Se agregó `JWT_SECRET=CpWX/5xZ/47HfkPCC4eOKoIG/NA6EZWb3ps67fYyVY0=` en `.env`
- Se modificó `EmpleadoAuthService.php` línea 15: `$key = env('JWT_SECRET');`

### ¿Qué impacto tienen estos cambios en el sistema?
- Las claves JWT ahora se leen desde `.env`
- Facilita la rotación de claves sin modificar código
- Previene exposición de credenciales en repositorios

### ¿Qué hace/n la/s funcion/es?
La función `login()` ahora lee la clave desde `env('JWT_SECRET')` en lugar de un string hardcodeado.

### ¿Qué buenas prácticas se siguió?
- Separación de configuración y código
- Uso de variables de entorno para datos sensibles

### ¿Qué estructura de carpetas tuvo la modificación?
- `.env`
- `app/Services/EmpleadoAuthService.php`

---

## Cambio 2: Almacenamiento de Token JWT en Cookies HttpOnly

### ¿Qué cambios se realizaron?
Se modificó el flujo de autenticación para que el token JWT se almacene en una cookie `httpOnly` en lugar de retornarse en el cuerpo JSON.

### ¿Por qué se realizaron estos cambios?
- Mayor seguridad: las cookies httpOnly no son accesibles desde JavaScript
- Previene ataques XSS que podrían robar el token del localStorage
- El navegador envía automáticamente las cookies en cada request

### ¿Cómo se realizaron estos cambios?
- Se modificó `Authentication.php`:
  - El método `login()` ahora retorna el token en una cookie
  - Se usa `cookie('token', $token, 60, '/', null, false, true)`
  - Parámetros: nombre, valor, minutos, path, domain, secure, httpOnly

### ¿Qué impacto tienen estos cambios en el sistema?
- El frontend ya no necesita almacenar el token manualmente
- Las peticiones autenticadas funcionan automáticamente
- Se requiere manejo de cookies en el cliente

### ¿Qué hace/n la/s funcion/es?
El método `login()` genera una cookie segura con el token JWT que expira en 60 minutos.

### ¿Qué buenas prácticas se siguió?
- Cookies HttpOnly para almacenar tokens
- Configuración de path y parámetros de seguridad
- Separación de concerns (autenticación en controller)

### ¿Qué estructura de carpetas tuvo la modificación?
- `app/Http/Controllers/Authentication.php`

---

## Cambio 3: Middleware de Autenticación JWT (JwtAuthMiddleware)

### ¿Qué cambios se realizaron?
Se creó un nuevo middleware `JwtAuthMiddleware` que valida el token JWT desde la cookie y adjunta el empleado al request.

### ¿Por qué se realizaron estos cambios?
Necesidad de proteger rutas que requieren autenticación, leyendo el token directamente desde la cookie enviada por el navegador.

### ¿Cómo se realizaron estos cambios?
- Se creó `app/Http/Middleware/JwtAuthMiddleware.php`
- Se decodifica el JWT usando la librería `firebase/php-jwt`
- Se adjunta el modelo `Empleado` al request
- Se registró el middleware en `bootstrap/app.php` con el alias `jwt.auth`

### ¿Qué impacto tienen estos cambios en el sistema?
- Las rutas pueden protegerse con `middleware('jwt.auth')`
- El empleado autenticado está disponible en `$request->empleado`
- Manejo de errores para tokens expirados o inválidos

### ¿Qué hace/n la/s funcion/es?
El método `handle()` intercepta cada request, extrae el token de la cookie, lo valida y permite o deniega el acceso.

### ¿Qué buenas prácticas se siguió?
- Middleware como capa de validación
- Manejo de excepciones específicas de JWT
- Código reutilizable para cualquier ruta protegida

### ¿Qué estructura de carpetas tuvo la modificación?
- `app/Http/Middleware/JwtAuthMiddleware.php` (nuevo)
- `bootstrap/app.php` (modificado)

---

## Cambio 4: Registro de Intentos de Login (LoginLog)

### ¿Qué cambios se realizaron?
Se creó el modelo `LoginLog` y la tabla `login_logs` para registrar todos los intentos de inicio de sesión.

### ¿Por qué se realizaron estos cambios?
- Auditoría de seguridad
- Detección de intentos de acceso no autorizados
- Cumplimiento de requisitos de logging

### ¿Cómo se realizaron estos cambios?
- Se creó `app/Models/LoginLog.php`
- Se creó migración `2026_03_29_011652_create_login_logs_table.php`
- Campos: id_empleado, correo, ip, user_agent, exito, created_at
- Se integró logging en `Authentication.php` método `logLoginAttempt()`

### ¿Qué impacto tienen estos cambios en el sistema?
- Se registra cada intento de login (éxito o fallo)
- Incluye IP y user agent del cliente
- Facilita auditorías de seguridad

### ¿Qué hace/n la/s funcion/es?
El método `logLoginAttempt()` crea un registro en la tabla `login_logs` por cada intento de autenticación.

### ¿Qué buenas prácticas se siguió?
- Modelo con casts apropiados (boolean para exito)
- Foreign key nullable para empleados
- Timestamps desactivados, solo created_at

### ¿Qué estructura de carpetas tuvo la modificación?
- `app/Models/LoginLog.php` (nuevo)
- `database/migrations/2026_03_29_011652_create_login_logs_table.php` (nuevo)
- `app/Http/Controllers/Authentication.php` (modificado)

---

## Cambio 5: Sistema de Recuperación de Contraseña

### ¿Qué cambios se realizaron?
Se implementó el flujo completo de recuperación de contraseña:
- Campos `reset_token` y `reset_token_expira` en tabla empleados
- Servicio `ResetPasswordService` para lógica de negocio
- Mailable `ResetPasswordMail` para enviar emails
- Vista HTML para el email de recuperación
- Endpoints `forgot-password` y `reset-password`

### ¿Por qué se realizaron estos cambios?
Necesidad de que los usuarios puedan recuperar su contraseña cuando la olviden, de forma segura.

### ¿Cómo se realizaron estos cambios?
- Migración `add_reset_token_to_empleados_table.php`
- Servicio `app/Services/ResetPasswordService.php`:
  - `sendResetLink()`: genera token, guarda hash en BD, envía email
  - `resetPassword()`: valida token, actualiza contraseña
- Mailable `app/Mail/ResetPasswordMail.php`
- Vista `resources/views/emails/reset-password.blade.php`
- Endpoints en `Authentication.php`
- Modelo `Empleado.php` actualizado con nuevos campos en fillable

### ¿Qué impacto tienen estos cambios en el sistema?
- Los usuarios pueden solicitar recuperación de contraseña
- Tokens de un solo uso con expiración de 1 hora
- Emails guardados en `storage/logs/mail.log` (driver log)

### ¿Qué hace/n la/s funcion/es?
- `sendResetLink()`: Busca empleado, genera token SHA256, guarda con expiración, envía email
- `resetPassword()`: Valida hash del token, verifica expiración, actualiza contraseña con bcrypt

### ¿Qué buenas prácticas se siguió?
- Tokens con hash SHA256 (no se almacena el token plano)
- Expiración de tokens para seguridad
- Separación de lógica en servicios
- Uso de Mailables de Laravel
- Validación de contraseñas (mínimo 8 caracteres)

### ¿Qué estructura de carpetas tuvo la modificación?
- `database/migrations/*_add_reset_token_to_empleados_table.php` (nuevo)
- `app/Services/ResetPasswordService.php` (nuevo)
- `app/Mail/ResetPasswordMail.php` (nuevo)
- `resources/views/emails/reset-password.blade.php` (nuevo)
- `app/Http/Controllers/Authentication.php` (modificado)
- `app/Models/Empleado.php` (modificado)

---

## Cambio 6: Endpoint de Logout

### ¿Qué cambios se realizaron?
Se agregó el método `logout()` en `Authentication.php` y la ruta correspondiente para cerrar sesión.

### ¿Por qué se realizaron estos cambios?
Necesidad de que los usuarios puedan cerrar sesión de forma segura, eliminando la cookie del token.

### ¿Cómo se realizaron estos cambios?
- Se agregó método `logout()` en `Authentication.php`
- Usa `cookie()->forget('token')` para eliminar la cookie
- Se agregó ruta `POST /api/auth/logout` en `routes/auth.php`

### ¿Qué impacto tienen estos cambios en el sistema?
- Los usuarios pueden cerrar sesión
- La cookie se elimina del navegador
- Se invalidan las futuras peticiones autenticadas

### ¿Qué hace/n la/s funcion/es?
El método `logout()` retorna una respuesta JSON con un cookie de eliminación para 'token'.

### ¿Qué buenas prácticas se siguió?
- Eliminación de cookie (no solo invalidación)
- Respuesta JSON confirmación de cierre
- Ruta con prefijo de autenticación

### ¿Qué estructura de carpetas tuvo la modificación?
- `app/Http/Controllers/Authentication.php` (modificado)
- `routes/auth.php` (modificado)

---

## Cambio 7: Reestructuración de Rutas API

### ¿Qué cambios se realizaron?
Se separaron las rutas de autenticación en un archivo independiente `routes/auth.php` y se actualizó `routes/api.php` para importarlo.

### ¿Por qué se realizaron estos cambios?
Organización del código y mantenimiento más fácil, siguiendo la estructura propuesta para el proyecto.

### ¿Cómo se realizaron estos cambios?
- Se creó `routes/auth.php` con las rutas de autenticación
- Se modificó `routes/api.php` para usar `Route::prefix('auth')->group()`
- Middleware `jwt.auth` registrado en `bootstrap/app.php`

### ¿Qué impacto tienen estos cambios en el sistema?
- Rutas organizadas por módulo
- Fácil escalabilidad para agregar más rutas de autenticación
- Middleware disponible para proteger rutas

### ¿Qué buenas prácticas se siguió?
- Separación de responsabilidades
- Rutas agrupadas por prefijo
- Código modular y organizado

### ¿Qué estructura de carpetas tuvo la modificación?
- `routes/auth.php` (nuevo)
- `routes/api.php` (modificado)
- `bootstrap/app.php` (modificado)

---

## Resumen de Archivos Creados

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `app/Http/Middleware/JwtAuthMiddleware.php` | Nuevo | Middleware de autenticación JWT |
| `app/Models/LoginLog.php` | Nuevo | Modelo para logs de login |
| `app/Services/ResetPasswordService.php` | Nuevo | Servicio de recuperación de contraseña |
| `app/Mail/ResetPasswordMail.php` | Nuevo | Mailable para email de recuperación |
| `resources/views/emails/reset-password.blade.php` | Nuevo | Vista HTML del email |
| `routes/auth.php` | Nuevo | Rutas de autenticación |
| `database/migrations/*_create_login_logs_table.php` | Nuevo | Migración tabla login_logs |
| `database/migrations/*_add_reset_token_to_empleados_table.php` | Nuevo | Migración campos reset_token |

## Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `.env` | Agregada variable JWT_SECRET |
| `app/Services/EmpleadoAuthService.php` | Leer JWT desde env |
| `app/Http/Controllers/Authentication.php` | Cookies, logs, forgot/reset password, logout |
| `app/Models/Empleado.php` | Nuevos campos en fillable |
| `routes/api.php` | Importar rutas auth |
| `bootstrap/app.php` | Registrar middleware jwt.auth |

---

*Fecha: 29/03/2026*
