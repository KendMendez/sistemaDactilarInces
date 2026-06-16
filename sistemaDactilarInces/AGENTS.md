# AGENTS.md - Sistema Dactilar INCES

## Build / Lint / Test Commands

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run a single test file
./vendor/bin/pest tests/Feature/ExampleTest.php

# Run a single test by name
./vendor/bin/pest --filter=test_example

# Run tests with coverage
./vendor/bin/pest --coverage
```

### Code Style (Formatting)
```bash
# Format all code (Pint - Laravel's code style fixer)
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

### Laravel Commands
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run migrations
php artisan migrate
php artisan migrate:fresh

# Create components
php artisan make:controller NameController
php artisan make:model Name
php artisan make:service NameService
php artisan make:request NameRequest
```

---

## Code Style Guidelines

### General
- Use return type hints on all methods
- Use nullable types where appropriate (`?string`, `?array`)
- Always use fully qualified class names after `use` statements

### Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| Controllers | PascalCase | `Authentication.php`, `EmpleadoController.php` |
| Models | PascalCase | `Empleado.php`, `Asistencia.php` |
| Services | PascalCase | `EmpleadoAuthService.php` |
| Methods | **camelCase + English** | `login()`, `store()`, `registerAttempt()` |
| Variables (generic) | **camelCase + English** | `$authenticated`, `$token`, `$foundEmployee` |
| Variables (model fields) | **camelCase + Spanish** | `$correo`, `$contrasena`, `$nombre` |
| Database columns | snake_case | `hora_entrada`, `reset_token_expira` |
| Request param | `$req` | `login(Request $req)` |

### Special Rules
- **Methods ALWAYS in English**: `login()`, `store()`, `createSessionCookie()`, `registerAttempt()`
- **Variables in English** EXCEPT for model fields
- **Model fields keep Spanish names**: `correo`, `contraseña`, `nombre`
- **Never use**: `password`, `email` for variables that map to model fields

### Example
```php
// ✅ Correct
public function login(Request $req): JsonResponse
{
    $correo = $req->input('correo');           // model field → Spanish
    $contrasena = $req->input('contraseña');   // model field → Spanish
    $authenticated = $this->authService->login([...]);  // variable → English
    $token = $this->authService->createSessionCookie($authenticated['token']);
}

// ❌ Incorrect
public function login(Request $req): JsonResponse
{
    $email = $req->input('correo');            // variable name doesn't match model
    $password = $req->input('contraseña');      // using 'password' instead of 'contraseña'
    $loggedIn = $this->authService->login();   // variable in Spanish
}
```

---

## File Structure
```
app/
├── Http/
│   ├── Controllers/     # Thin controllers
│   └── Middleware/       # JWT, roles middleware
├── Models/               # Eloquent models with relations
├── Services/             # Business logic
├── Mail/                 # Mailables
├── Helpers/              # Utility classes (Message, etc)
└── Providers/            # Service providers

routes/
├── api.php               # Main API routes
└── auth.php              # Authentication routes
```

---

## Controller Pattern
- Use dependency injection via constructor
- Return `JsonResponse` for API endpoints
- Keep controllers thin, delegate logic to services

### Response Format
```php
return response()->json([
    'error' => 0,        // 0 = success, 1 = error
    'msg' => 'Mensaje',
    'results' => [...]   // optional data
], 200);                 // HTTP status code
```

---

## Service Pattern
- Place business logic in `app/Services/`
- Inject services via constructor
- Return arrays with `error` and `msg` keys
- Keep controllers thin, services thick

---

## Error Handling
- Wrap controller logic in try-catch blocks
- Use `App\Helpers\Message::exception()` for generic errors
- Log errors with `Log::error()`
- Return consistent JSON responses

---

## Security
- Passwords hashed with `bcrypt()` or `Hash::make()`
- Verify with `Hash::check()`
- JWT tokens with expiration
- Reset tokens hashed with `hash('sha256', $token)`
- Never hardcode secrets - use `.env`

---

## Testing (Pest)
- Write tests in `tests/Feature/` and `tests/Unit/`
- Use Pest's `expect()` syntax
- Feature tests use `RefreshDatabase` trait

---

## Project Status

### Goal
Agrupar privilegios por `campo` en el formulario de roles, filtrar menú/secciones según privilegios del usuario, y extender `hasPrivilege()` condicional a nivel de componentes/botones en toda la UI.

### Constraints & Preferences
- Backend: `C:/xampp/htdocs/sistemaDactilarInces` (Laravel)
- Frontend: `C:/UISistemaDactilarInces` (Angular)
- API URL: `http://localhost/sistemaDactilarInces/public/api`
- Forgot/reset password completamente eliminado.
- Foreign keys encriptadas client-side, desencriptadas server-side.
- Kiosko: captura de huella → PNG → matching server-side GD 1:N → POST asistencia → anti-spam + reglas de horario.
- Aprobación es solo cambio de `status`.
- Rutas Kiosko protegidas por header `X-Kiosko-Key`.
- Inasistencias automáticas a las 18:00 saltando fines de semana y feriados.
- `horarios.dia` es JSON array.
- Contraseña mínimo 8 caracteres; identificación solo dígitos.
- GD extension habilitada.
- `PrivilegioMiddleware` omite chequeo de privilegio si el rol es "Administrador".
- `Crypt::encrypt()` es no-determinístico (IV aleatorio) — no se pueden comparar IDs encriptados entre requests.
- FullCalendar v6.1.20 (CSS inyectado vía JS runtime, sin archivos `.css` en npm).
- PHP timezone debe ser `America/Caracas` (Venezuela UTC-4).
- Privilegios tienen campo `campo` para agrupar visualmente en la UI.
- Menú principal se filtra según `campos` del usuario autenticado.
- No se usa guard de rutas para filtrar por privilegios — se maneja con `hasPrivilege()` condicional en templates y el menú ya filtrado, más el middleware del backend como defensa real.
- Las rutas protegidas por JWT se mantienen con `authGuard` únicamente.
- `auth.ts` `hasPrivilege()` carga `privilegios` desde `localStorage` en el constructor para tener datos disponibles inmediatamente en navegaciones subsecuentes.

### Implemented Features
- JWT Authentication (login, logout)
- EmpleadoAuthService with proper naming
- JwtAuthMiddleware, PrivilegioMiddleware, KioskoMiddleware
- LoginLog for audit
- CRUD: Cargos, Roles, Empleados, Horarios, Feriados, Asistencias, Inasistencias, Aprobaciones
- Kiosko module: fingerprint capture, GD matching, attendance POST
- Privilege system: campo grouping, menu filtering, per-component visibility
- FullCalendar v6 feriados
- Inasistencias automáticas via cron dailyAt('18:00')
- Super admin backup seeder: `superadmin@test.com` / `SuperAdmin2025`

### Known Issues / In Progress
- Opcache en XAMPP puede servir bytecode compilado viejo tras cambios en backend. Solución: reiniciar Apache (services.msc → Apache → Restart) o llamar `opcache_reset()` temporal.

---

## Key Files

### Backend (Laravel)
| File | Description |
|------|-------------|
| `routes/auth.php` | Auth routes (login, me, logout) |
| `routes/api.php` | Main API routes (CRUD, kiosko, etc.) |
| `app/Http/Controllers/Authentication.php` | Login/logout/me — `login()` retorna privilegios+campos |
| `app/Services/EmpleadoAuthService.php` | Auth logic, returns `empleado` array con `id` incluido |
| `app/Http/Middleware/PrivilegioMiddleware.php` | Privilege check, bypass si rol "Administrador" |
| `app/Http/Middleware/KioskoMiddleware.php` | Valida `X-Kiosko-Key` header |
| `app/Http/Middleware/JwtAuthMiddleware.php` | JWT validation |
| `app/Http/Controllers/RolePrivilegioController.php` | `findPrivilegiosByRoleId()` retorna todos con `selected: bool` |
| `app/Services/EmpleadoService.php` | CRUD + búsqueda + validación de imagen/huella |
| `app/Services/KioskoService.php` | Matching GD, Pearson correlation, threshold 0.85 |
| `database/seeders/PrivilegeSeeder.php` | Todos los privilegios con `campo` |
| `database/seeders/FeriadoSeeder.php` | 83 feriados venezolanos 2025–2030 |
| `config/app.php` | Timezone `America/Caracas` |

### Frontend (Angular)
| File | Description |
|------|-------------|
| `src/app/services/auth.ts` | `login()` guarda privilegios/campos en memoria + localStorage; `hasPrivilege()` / `hasCampo()` |
| `src/app/app.ts` | Filtra `menuItems` según `campos` via `loadPrivileges()` en `ngOnInit` + `NavigationEnd` |
| `src/app/components/*/` | Cada componente inyecta `Auth` y usa `@if(auth.hasPrivilege('ver X'))` |

### Other
- Good practices: `.agents/skills/buenasPracticas_SKILL.md`

---

## Key Decisions

### Privilege System
- **Phase 3 approach**: endpoint single-request retornando todos los privilegios con `selected: bool` — soluciona no-determinismo de `Crypt::encrypt()`.
- **Campo column**: nueva columna en BD para agrupar visualmente en UI.
- **No route guards for privileges**: menú filtrado por `campos` + `hasPrivilege()` condicional en templates + middleware backend.
- **Privilegios en login**: `POST /login` retorna privilegios + campos directamente, eliminando segunda llamada a `/me`.

### Kiosko
- **Matching**: 64×64 + Pearson correlation (0.0–1.0, score alto = mejor match). Threshold 0.85.
- **Imagen**: resize a 64×64, validación de varianza < 0.01 rechaza.

### Technical
- **FullCalendar v6.1.20**: CSS vía JS runtime (sin archivos CSS). Handlers deben llamar `cdr.detectChanges()`.
- **Timezone**: `America/Caracas` (Venezuela UTC-4). Config en `config/app.php` + `php.ini`.
- **Encryption**: `Crypt::encrypt()` no-determinístico — endpoints single-request computan IDs consistentemente.

---

## Critical Context

| Topic | Detail |
|-------|--------|
| `Crypt::encrypt()` | No-determinístico (IV aleatorio). No comparar IDs entre requests. |
| FullCalendar | v6.1.20, CSS via JS runtime. Corre sobre Preact fuera del zone de Angular — usar `cdr.detectChanges()`. |
| PHP Timezone | Era `Europe/Berlin` (UTC+2) — ahora `America/Caracas`. |
| XAMPP Opcache | Cachea bytecode compilado. Reiniciar Apache tras cambios en backend. |
| Kiosko Matching | Pearson correlation, threshold 0.85 (score alto = mejor match). |
| Admin Bypass | `PrivilegioMiddleware` y `Authentication` retornan TODOS los privilegios si rol "Administrador". |
| `hasPrivilege()` | Lee de memoria (cargado desde login response o localStorage), fallback a localStorage. |
| Menu Filter | `loadPrivileges()` en `ngOnInit` y `NavigationEnd`. Solo lee localStorage, sin HTTP. |
| Login Flow | `login()` → guarda privilegios en memoria+localStorage → navegación → `NavigationEnd` → `loadPrivileges()` filtra menú |

---

## Setup (laptop after pull)

```bash
# Backend
cd C:/xampp/htdocs/sistemaDactilarInces
git pull origin devKend
composer install
php artisan migrate
php artisan db:seed --class=PrivilegeSeeder

# Frontend
cd C:/UISistemaDactilarInces
git pull origin devKend
npm install
npx ng build

# Restart Apache (services.msc → Apache → Restart)
```
