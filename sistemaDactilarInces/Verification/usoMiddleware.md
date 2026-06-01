# Uso del Middleware JWT en Rutas de Autenticación

---

## Resumen

Se implementó el middleware `jwt.auth` para proteger rutas que requieren autenticación en el sistema.

---

## Middleware JwtAuthMiddleware

### Propósito

El middleware `JwtAuthMiddleware` tiene como función:
1. Verificar que existe una cookie `token` en la solicitud
2. Decodificar y validar el token JWT
3. Verificar que el usuario asociado existe en la base de datos
4. Adjuntar el empleado autenticado a la request
5. Permitir o denegar el acceso según la validez del token

### Ubicación
`app/Http/Middleware/JwtAuthMiddleware.php`

### Registro
Registrado en `bootstrap/app.php` con el alias `jwt.auth`.

---

## Rutas de Autenticación

### Archivo: `routes/auth.php`

| Método | Ruta | Middleware | Descripción |
|--------|------|-----------|-------------|
| POST | `/login` | ❌ Sin middleware | Iniciar sesión (usuario no tiene token aún) |
| POST | `/logout` | ✅ Con middleware | Cerrar sesión (requiere token activo) |
| POST | `/forgot-password` | ❌ Sin middleware | Solicitar recuperación de contraseña |
| POST | `/reset-password` | ❌ Sin middleware | Restablecer contraseña (usa token de email) |

---

## Estructura de Rutas

### routes/api.php (Archivo principal receptor)

```php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(base_path('routes/auth.php'));
```

### routes/auth.php

```php
<?php

use App\Http\Controllers\Authentication;
use Illuminate\Support\Facades\Route;

// Rutas públicas (sin middleware)
Route::post('/login', [Authentication::class, 'login']);
Route::post('/forgot-password', [Authentication::class, 'forgotPassword']);
Route::post('/reset-password', [Authentication::class, 'resetPassword']);

// Ruta protegida (con middleware)
Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [Authentication::class, 'logout']);
});
```

---

## Lógica del Middleware en Rutas

### ¿Por qué login NO tiene middleware?

El usuario NO tiene token antes de iniciar sesión. El middleware busca una cookie `token` que no existe aún.

```
Flujo:
1. Usuario envía credenciales a /login
2. Backend verifica credenciales
3. Backend genera token JWT
4. Backend crea cookie con el token
5. Respuesta se envía al usuario
6. Navegador guarda la cookie
```

### ¿Por qué logout SÍ tiene middleware?

El usuario DEBE tener una sesión activa para poder cerrarla.

```
Flujo:
1. Solicitud llega a /logout con cookie
2. Middleware intercepta
3. Middleware verifica token en cookie
4. Si válido → controlador procesa logout
5. Cookie se elimina
```

---

## Arquitectura de Expansión

### Estructura actual

```
routes/
├── api.php              # Archivo principal - recepta todas las rutas
├── auth.php             # Rutas de autenticación
```

### Estructura para futuro

```
routes/
├── api.php              # Archivo principal - recepta todas las rutas
├── auth.php             # Rutas de autenticación
├── empleado.php         # Rutas de empleados
├── cargo.php            # Rutas de cargos
├── asistencia.php       # Rutas de asistencia
├── horario.php          # Rutas de horarios
├── inasistencia.php     # Rutas de inasistencias
├── feriado.php          # Rutas de feriados
├── role.php             # Rutas de roles
├── privilegio.php       # Rutas de privilegios
├── roleEmpleado.php     # Rutas de relación rol-empleado
├── rolePrivilegio.php   # Rutas de relación rol-privilegio
```

### Agregar nuevas rutas en api.php

```php
<?php

use Illuminate\Support\Facades\Route;

// Modulo de Autenticación
Route::prefix('auth')->group(base_path('routes/auth.php'));

// Modulo de Empleados (ejemplo)
Route::prefix('empleados')->middleware('jwt.auth')->group(base_path('routes/empleado.php'));

// Modulo de Cargos (ejemplo)
Route::prefix('cargos')->middleware('jwt.auth')->group(base_path('routes/cargo.php'));
```

---

## Flujo de Autenticación Completo

```
1. Login (público)
   POST /api/auth/login
   → Genera token JWT
   → Crea cookie httpOnly
   → Retorna respuesta

2. Peticiones protegidas
   Cualquier ruta con middleware
   → Middleware lee cookie
   → Valida token
   → Adjunta usuario
   → Continúa al controlador

3. Logout (protegido)
   POST /api/auth/logout
   → Middleware valida token
   → Controlador elimina cookie
   → Sesión cerrada
```

---

## Endpoints Disponibles

| Método | Endpoint | Autenticación | Descripción |
|--------|----------|---------------|-------------|
| POST | `/api/auth/login` | No | Iniciar sesión |
| POST | `/api/auth/logout` | Sí | Cerrar sesión |
| POST | `/api/auth/forgot-password` | No | Solicitar recuperación |
| POST | `/api/auth/reset-password` | No | Restablecer contraseña |

---

## Buenas Prácticas Aplicadas

1. **Separación de rutas**: Cada módulo tiene su propio archivo de rutas
2. **Middleware específico**: Solo rutas que requieren autenticación lo usan
3. **Principio de responsabilidad única**: El middleware solo valida, no procesa lógica de negocio
4. **Escalabilidad**: Estructura preparada para agregar más módulos fácilmente

---

## Archivos Involucrados

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `app/Http/Middleware/JwtAuthMiddleware.php` | Existente | Middleware de autenticación JWT |
| `bootstrap/app.php` | Existente | Registro del alias `jwt.auth` |
| `routes/api.php` | Modificado | Archivo receptor de rutas |
| `routes/auth.php` | Modificado | Rutas de autenticación con middleware |

---

*Fecha: 29/03/2026*
