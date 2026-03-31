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

### Implemented
- JWT Authentication (login, logout, forgot/reset password)
- EmpleadoAuthService with proper naming
- JwtAuthMiddleware
- LoginLog for audit

### Pending (see tareas.md)
- CRUD for all entities
- Middleware for roles/privileges
- Full API implementation

---

## Key Files
- Routes: `routes/auth.php`, `routes/api.php`
- Auth Service: `app/Services/EmpleadoAuthService.php`
- JWT Middleware: `app/Http/Middleware/JwtAuthMiddleware.php`
- Auth Controller: `app/Http/Controllers/Authentication.php`
- Good practices: `.agents/skills/buenasPracticas_SKILL.md`
- Pending tasks: `tareas.md`
