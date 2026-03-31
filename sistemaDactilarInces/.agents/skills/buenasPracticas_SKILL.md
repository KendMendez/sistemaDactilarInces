name: buenasPracticas

description: Documenta y aplica las 8 buenas prácticas del proyecto Sistema Dactilar INCES: Arquitectura, Seguridad, Patrones de Respuesta, Eloquent ORM, Manejo de Errores, Configuraciones, Inyección de Dependencias y Consistencia en Nomenclatura

#buenasPracticas_function: 
Aplicar y documentar las buenas prácticas del proyecto en cada modificación de código

##where or when use it:
Usar cuando se necesiten implementar nuevas funcionalidades o refactorizar código existente, aplicando las 8 buenas prácticas identificadas en el proyecto.

##how to use it:

### 1. ARQUITECTURA Y SEPARACIÓN DE RESPONSABILIDADES

**Concepto**: Separar la lógica de negocio en Services dedicados, mantener Controllers delgados y usar Helpers para lógica reutilizable.

**Cómo aplicarlo**:
- Crear clases en `app/Services/` para lógica de negocio compleja
- Mantener Controllers solo con validaciones y llamadas a services
- Usar `app/Helpers/` para funciones utilitarias

**Ejemplo de estructura**:
```php
// Controller (delgado)
class EmpleadoController extends Controller
{
    public function __construct(protected EmpleadoService $service) {}
    
    public function store(Request $req): JsonResponse
    {
        $result = $this->service->store($req->validated());
        return response()->json($result, $result['error'] === 0 ? 201 : 400);
    }
}

// Service (lógica de negocio)
class EmpleadoService
{
    public function store(array $data): array
    {
        // Validaciones de negocio
        // Lógica compleja
        // Retorna array con 'error' y 'msg'
    }
}
```

---

### 2. SEGURIDAD

**Concepto**: Implementar medidas de protección contra ataques comunes: contraseñas hasheadas, tokens seguros, auditoría y validación de inputs.

**Cómo aplicarlo**:
- Usar `bcrypt()` o `Hash::make()` para contraseñas
- Usar `Hash::check()` para verificar contraseñas
- Tokens JWT con expiración configurable
- Tokens de reset con hash (`hash('sha256', $token)`) y fecha de expiración
- Cookies HTTP-only cuando sea posible
- Registrar eventos importantes en logs (`LoginLog`)

**Ejemplo**:
```php
// Crear usuario
$empleado->contraseña = bcrypt($contrasena);

// Verificar login
if (Hash::check($auth['contraseña'], $foundEmployee['contraseña'])) {
    // Login exitoso
}

// Token de reset seguro
$empleado->reset_token = hash('sha256', $token);
$empleado->reset_token_expira = Carbon::now()->addHours(1);
```

---

### 3. PATRONES DE RESPUESTA CONSISTENTE

**Concepto**: Estandarizar todas las respuestas JSON del API con formato uniforme.

**Cómo aplicarlo**:
- Siempre usar el formato: `['error' => 0|1, 'msg' => 'mensaje', 'results' => ...]`
- Usar códigos HTTP apropiados (200=éxito, 400=bad request, 401=sin auth, 404=no encontrado, 500=error servidor)
- Usar el helper `Message` para mensajes reutilizables

**Ejemplo**:
```php
// Éxito
return response()->json([
    'error' => 0,
    'msg' => 'Registro almacenado con exito.',
    'results' => $empleado
], 201);

// Error
return response()->json([
    'error' => 1,
    'msg' => 'Empleado no encontrado.',
], 404);
```

---

### 4. ELOQUENT ORM

**Concepto**: Usar Eloquent de forma efectiva con relaciones, $fillable, $casts y mutadores.

**Cómo aplicarlo**:
- Definir `$fillable` en todos los modelos para protección mass assignment
- Usar `$casts` para tipos de datos (`'boolean'`, `'datetime'`, `'array'`)
- Definir relaciones Eloquent (`belongsTo`, `hasMany`, etc.)
- Usar `where()` con condiciones en array para claridad
- Desactivar timestamps cuando no sean necesarios (`public $timestamps = false`)

**Ejemplo**:
```php
class LoginLog extends Model
{
    protected $table = 'login_logs';
    
    protected $fillable = [
        'id_empleado',
        'correo',
        'ip',
        'exito',
    ];
    
    protected $casts = [
        'exito' => 'boolean',
        'created_at' => 'datetime',
    ];
    
    public $timestamps = false;
    
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
```

---

### 5. MANEJO DE ERRORES

**Concepto**: Capturar excepciones, responder consistentemente y usar logging para debugging.

**Cómo aplicarlo**:
- Envolver lógica en bloques `try-catch`
- Usar `Message::exception()` para mensajes genéricos de error
- Loggear errores cuando sea necesario
- Retornar respuesta JSON consistente en catch

**Ejemplo**:
```php
public function login(Request $req): JsonResponse
{
    try {
        // lógica normal
        return response()->json([...]);
    } catch (\Exception $e) {
        Log::error('Login error: ' . $e->getMessage());
        
        return response()->json([
            'error' => 1,
            'msg' => Message::exception(),
        ], 500);
    }
}
```

---

### 6. CONFIGURACIONES

**Concepto**: Usar variables de entorno para secrets y configuración específica del entorno.

**Cómo aplicarlo**:
- Guardar secrets en `.env` (nunca hardcodear)
- Usar `env('KEY')` para acceder a variables de entorno
- Mantener configuración en archivos de `config/`
- Usar `php artisan config:clear` después de cambios

**Ejemplo**:
```php
// .env
JWT_SECRET=tu_secret_aqui
MAIL_MAILER=smtp

// En código
$key = env('JWT_SECRET');
$token = JWT::encode($payload, $key, 'HS256');
```

---

### 7. INYECCIÓN DE DEPENDENCIAS EN CONSTRUCTOR

**Concepto**: Inyectar servicios y dependencias via constructor para mejor testabilidad y código limpio.

**Cómo aplicarlo** (basado en Authentication controller):
```php
// En Authentication.php
class Authentication extends Controller
{
    public function __construct(protected EmpleadoAuthService $authService) {}
    
    public function login(Request $req): JsonResponse
    {
        $this->authService->login([...]);
    }
}
```

**Beneficios**:
- Código más limpio y legible
- Fácil de testear (se pueden mockear los servicios)
- Laravel maneja la instanciación automáticamente
- Más fácil de mantener y extender

**Notas**:
- Usar `protected` o `private` para las propiedades inyectadas
- PHP 8+ permite usar el constructor property promotion: `public function __construct(protected Service $service)`
- NO usar `new Service()` dentro de los métodos

---

### 8. CONSISTENCIA EN NOMENCLATURA

**Concepto**: Mantener un estilo de nombres uniforme en todo el proyecto.

**Convenciones del proyecto**:

| Elemento | Formato | Ejemplo |
|----------|---------|---------|
| Métodos/Funciones | Inglés | `login()`, `store()`, `index()` |
| Variables genéricas | Inglés | `$authenticated`, `$token`, `$userId` |
| Campos del modelo | Como están en DB | `correo`, `contraseña`, `nombre` |

**Reglas específicas**:

1. **Métodos siempre en inglés**
   ```php
   // ✅ Correcto
   public function login(): JsonResponse { }
   public function store(Request $req): JsonResponse { }
   public function index(): JsonResponse { }
   public function show(int $id): JsonResponse { }
   public function update(Request $req, int $id): JsonResponse { }
   public function destroy(int $id): JsonResponse { }
   public function registerAttempt(...): void { }
   public function createSessionCookie(...): Cookie { }
   ```

2. **Variables en inglés** (excepto campos del modelo)
   ```php
   $authenticated = $this->service->login();
   $sessionToken = $this->authService->createSessionCookie($token);
   $userId = $empleado->id;
   $foundEmployee = Empleado::where(...)->first();
   $response = [];
   $message = 'Bienvenido';
   ```

3. **Excepción: campos del modelo en español**
   - Si el modelo tiene `contraseña`, usar `$contrasena`
   - Si el modelo tiene `correo`, usar `$correo`
   - Mantener `contraseña` SIEMPRE como `contraseña` (nunca `password`)

4. **Request parameter corto**
   ```php
   public function login(Request $req): JsonResponse { }
   ```

**Ejemplo completo**:

```php
public function login(Request $req): JsonResponse
{
    try {
        $correo = $req->input('correo');           // campo del modelo
        $contrasena = $req->input('contraseña');   // campo del modelo
        $authenticated = $this->authService->login([
            'correo' => $correo,
            'contraseña' => $contrasena,
        ]);

        if (!$authenticated) {
            $this->authService->registerAttempt($correo, $req, false);
            return response()->json([
                'error' => 1,
                'msg' => 'Correo o contraseña incorrectos',
            ], 401);
        }

        $this->authService->registerAttempt($correo, $req, true, $authenticated['empleado'] ?? null);
        $cookie = $this->authService->createSessionCookie($authenticated['token']);

        return response()->json([
            'error' => 0,
            'msg' => 'Login successful',
            'results' => [
                'empleado' => $authenticated['empleado'] ?? null,
            ],
        ], 200)->withCookie($cookie);

    } catch (\Exception $e) {
        $this->authService->registerAttempt($req->input('correo'), $req, false);
        Log::error('Login error: ' . $e->getMessage());
        return response()->json([
            'error' => 1,
            'msg' => Message::exception(),
        ], 500);
    }
}
```

**Checklist de nombres**:
- [ ] Métodos en inglés (login, store, show, update, destroy, etc.)
- [ ] Variables genéricas en inglés
- [ ] `contraseña` y `correo` según aparecen en el modelo
- [ ] No usar `password`, `email` en variables que mapean campos del modelo

---

## Estructura de carpetas a seguir

```
app/
├── Http/
│   ├── Controllers/     # Controladores delgados
│   └── Middleware/      # Middlewares (auth, roles, etc)
├── Models/              # Modelos Eloquent con relaciones
├── Services/            # Lógica de negocio
├── Mail/                # Mailables
├── Helpers/              # Clases utilitarias (Message, etc)
└── Providers/           # Service providers

routes/
├── api.php              # Rutas API principales
├── auth.php             # Rutas de autenticación
```

## Verificación final

Después de aplicar estas buenas prácticas:
1. Verificar que los Controllers estén delgados
2. Confirmar que los Services contengan la lógica de negocio
3. Probar que todas las respuestas tengan formato consistente
4. Verificar que no haya passwords o secrets hardcodeados
5. Confirmar que todos los modelos tengan $fillable definidos
6. Probar el manejo de errores con casos edge
