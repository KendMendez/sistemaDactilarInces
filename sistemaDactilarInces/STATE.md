# STATE — copia local manual

Aplicar archivo por archivo en la laptop cuando no se pueda usar `git pull`.

---

## Setup inicial

```bash
# 1. Backend — pegar en C:/xampp/htdocs/sistemaDactilarInces
composer install
php artisan migrate
php artisan db:seed --class=PrivilegeSeeder

# 2. Frontend — pegar en C:/UISistemaDactilarInces
npm install
npx ng build

# 3. Reiniciar Apache (services.msc → Apache → Restart)
```

---

# BACKEND (Laravel)

## 1. `config/app.php` — timezone (línea 68)

```php
'timezone' => 'America/Caracas',
```

## 2. `app/Http/Controllers/Authentication.php`

**Ruta completa:** `C:/xampp/htdocs/sistemaDactilarInces/app/Http/Controllers/Authentication.php`

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\Message;
use App\Models\Empleado;
use App\Models\Privilegio;
use App\Services\EmpleadoAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Authentication extends Controller
{
    public function __construct(protected EmpleadoAuthService $authService) {}

    public function login(Request $req): JsonResponse
    {
        try {
            $correo = $req->input('correo');
            $contrasena = $req->input('contraseña');

            Log::debug('[Login] Request received', [
                'correo' => $correo,
                'contrasena_present' => !is_null($contrasena),
                'contrasena_length' => is_string($contrasena) ? strlen($contrasena) : null,
                'content_type' => $req->header('Content-Type'),
                'method' => $req->method(),
            ]);

            $authenticated = $this->authService->login([
                'correo' => $correo,
                'contraseña' => $contrasena,
            ]);

            Log::debug('[Login] Service result', [
                'authenticated' => $authenticated ? 'truthy' : 'falsy',
                'result_keys' => $authenticated ? array_keys($authenticated) : [],
            ]);

            if (! $authenticated) {
                $this->authService->registerAttempt($correo, $req, false);

                return response()->json([
                    'error' => 1,
                    'msg' => 'Correo o contraseña incorrectos',
                ], 401);
            }

            $this->authService->registerAttempt($correo, $req, true, $authenticated['empleado'] ?? null);

            $user = $authenticated['empleado'] ?? null;
            if ($user && isset($user['id'])) {
                $userModel = Empleado::find($user['id']);
                if ($userModel) {
                    $userModel->load('roles.privilegios');
                    $empleadoRoles = $userModel->roles->pluck('role')->toArray();
                    if (in_array('Administrador', $empleadoRoles)) {
                        $privilegios = Privilegio::pluck('privilegio')->values();
                        $campos = Privilegio::pluck('campo')->unique()->values();
                    } else {
                        $privilegios = $userModel->roles->flatMap->privilegios->pluck('privilegio')->unique()->values();
                        $campos = $userModel->roles->flatMap->privilegios->pluck('campo')->unique()->values();
                    }
                }
            }

            $cookie = $this->authService->createSessionCookie($authenticated['token']);

            return response()->json([
                'error' => 0,
                'msg' => 'Inicio de sesión exitoso',
                'results' => [
                    'empleado' => $authenticated['empleado'] ?? null,
                    'token' => $authenticated['token'] ?? null,
                    'privilegios' => $privilegios ?? [],
                    'campos' => $campos ?? [],
                ],
            ], 200)->withCookie($cookie);

        } catch (\Exception $e) {
            $this->authService->registerAttempt($req->input('correo'), $req, false);

            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }

    public function me(): JsonResponse
    {
        try {
            /** @var Empleado $user */
            $user = request()->user();
            if (! $user) {
                return response()->json(['error' => 1, 'msg' => 'No autenticado'], 401);
            }

            $user->load('roles.privilegios');

            $empleadoRoles = $user->roles->pluck('role')->toArray();

            if (in_array('Administrador', $empleadoRoles)) {
                $privilegios = Privilegio::pluck('privilegio')->values();
                $campos = Privilegio::pluck('campo')->unique()->values();
            } else {
                $privilegios = $user->roles->flatMap->privilegios->pluck('privilegio')->unique()->values();
                $campos = $user->roles->flatMap->privilegios->pluck('campo')->unique()->values();
            }

            return response()->json([
                'error' => 0,
                'empleado' => [
                    'id' => Crypt::encrypt($user->id),
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'correo' => $user->correo,
                ],
                'roles' => $user->roles->pluck('role'),
                'privilegios' => $privilegios,
                'campos' => $campos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $cookie = $this->authService->deleteSessionCookie();

            return response()->json([
                'error' => 0,
                'msg' => 'Sesión cerrada correctamente',
            ], 200)->withCookie($cookie);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 1,
                'msg' => Message::exception(),
            ], 500);
        }
    }
}
```

---

## 3. `app/Services/EmpleadoAuthService.php`

```php
<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\LoginLog;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Cookie;

class EmpleadoAuthService
{
    public function login(array $auth)
    {
        $message = 'Bienvenido';
        $errorCode = 0;
        $key = config('jwt.secret');

        $time = time();
        $sessionTime = (60 * 60);
        $sessionExpired = $time + $sessionTime;

        $foundEmployee = Empleado::select('id', 'nombre', 'apellido', 'contraseña')->where([
            ['correo', '=', $auth['correo']],
        ])->first();

        Log::debug('[AuthService] Employee lookup', [
            'correo' => $auth['correo'],
            'found' => $foundEmployee ? 'yes' : 'no',
            'auth_contraseña' => $auth['contraseña'] ?? 'MISSING',
            'auth_contraseña_len' => is_string($auth['contraseña']) ? strlen($auth['contraseña']) : null,
        ]);

        $response = null;

        if ($foundEmployee && Hash::check($auth['contraseña'], $foundEmployee['contraseña'])) {
            $token = JWT::encode(['user' => $foundEmployee->id], $key, 'HS256');
            $response = [
                'iat' => $time,
                'expired' => $sessionExpired,
                'token' => $token,
                'msg' => $message,
                'error' => $errorCode,
                'empleado' => [
                    'id' => $foundEmployee['id'],
                    'nombre' => $foundEmployee['nombre'],
                    'apellido' => $foundEmployee['apellido'],
                ],
            ];
        }

        Log::debug('[AuthService] Login result', [
            'employee_found' => $foundEmployee ? 'yes' : 'no',
            'response_empty' => empty($response) ? 'yes' : 'no',
            'hash_check_called' => $foundEmployee ? 'yes' : 'N/A',
        ]);

        return $response;
    }

    public function registerAttempt(
        ?string $correo,
        Request $request,
        bool $exito,
        ?array $empleado = null
    ): void {
        LoginLog::create([
            'id_empleado' => $empleado['id'] ?? null,
            'correo' => $correo ?? 'desconocido',
            'ip' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent(),
            'exito' => $exito,
        ]);
    }

    public function createSessionCookie(string $token): Cookie
    {
        return cookie(
            'token',
            $token,
            60,
            '/',
            null,
            false,
            true
        );
    }

    public function deleteSessionCookie(): Cookie
    {
        return cookie()->forget('token');
    }
}
```

---

## 4. `app/Http/Middleware/PrivilegioMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivilegioMiddleware
{
    public function handle(Request $request, Closure $next, ...$privilegios): Response
    {
        $empleado = $request->attributes->get('empleado');

        if (! $empleado) {
            return response()->json([
                'error' => 1,
                'msg' => 'No autenticado.',
            ], 401);
        }

        $empleadoRoles = $empleado->roles->pluck('role')->toArray();

        if (in_array('Administrador', $empleadoRoles)) {
            return $next($request);
        }

        $empleadoPrivilegios = $empleado->roles
            ->pluck('privilegios.*.privilegio')
            ->flatten()
            ->unique()
            ->toArray();

        $hasPrivilegio = false;
        foreach ($privilegios as $privilegio) {
            if (in_array($privilegio, $empleadoPrivilegios)) {
                $hasPrivilegio = true;
                break;
            }
        }

        if (! $hasPrivilegio) {
            return response()->json([
                'error' => 1,
                'msg' => 'No tienes permisos para acceder a esta ruta.',
            ], 403);
        }

        return $next($request);
    }
}
```

---

## 5. Migración — `database/migrations/2026_06_15_add_campo_to_privilegios_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('privilegios', function (Blueprint $table) {
            $table->string('campo')->after('privilegio');
        });
    }

    public function down(): void
    {
        Schema::table('privilegios', function (Blueprint $table) {
            $table->dropColumn('campo');
        });
    }
};
```

---

## 6. `database/seeders/PrivilegeSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Privilegio;
use Illuminate\Database\Seeder;

class PrivilegeSeeder extends Seeder
{
    public function run(): void
    {
        $privilegios = [
            ['privilegio' => 'ver empleados',        'campo' => 'Empleados'],
            ['privilegio' => 'crear empleado',       'campo' => 'Empleados'],
            ['privilegio' => 'editar empleado',      'campo' => 'Empleados'],
            ['privilegio' => 'eliminar empleado',    'campo' => 'Empleados'],
            ['privilegio' => 'ver roles',            'campo' => 'Roles'],
            ['privilegio' => 'crear rol',            'campo' => 'Roles'],
            ['privilegio' => 'editar rol',           'campo' => 'Roles'],
            ['privilegio' => 'eliminar rol',         'campo' => 'Roles'],
            ['privilegio' => 'ver privilegios',      'campo' => 'Privilegios'],
            ['privilegio' => 'asignar privilegios',  'campo' => 'Privilegios'],
            ['privilegio' => 'ver cargos',           'campo' => 'Cargos'],
            ['privilegio' => 'crear cargo',          'campo' => 'Cargos'],
            ['privilegio' => 'editar cargo',         'campo' => 'Cargos'],
            ['privilegio' => 'eliminar cargo',       'campo' => 'Cargos'],
            ['privilegio' => 'ver feriados',         'campo' => 'Feriados'],
            ['privilegio' => 'crear feriado',        'campo' => 'Feriados'],
            ['privilegio' => 'editar feriado',       'campo' => 'Feriados'],
            ['privilegio' => 'eliminar feriado',     'campo' => 'Feriados'],
            ['privilegio' => 'ver asistencias',      'campo' => 'Asistencias'],
            ['privilegio' => 'registrar asistencia', 'campo' => 'Asistencias'],
            ['privilegio' => 'editar asistencia',    'campo' => 'Asistencias'],
            ['privilegio' => 'eliminar asistencia',  'campo' => 'Asistencias'],
            ['privilegio' => 'ver horarios',         'campo' => 'Horarios'],
            ['privilegio' => 'crear horario',        'campo' => 'Horarios'],
            ['privilegio' => 'editar horario',       'campo' => 'Horarios'],
            ['privilegio' => 'eliminar horario',     'campo' => 'Horarios'],
            ['privilegio' => 'ver inasistencias',    'campo' => 'Inasistencias'],
            ['privilegio' => 'registrar inasistencia','campo' => 'Inasistencias'],
            ['privilegio' => 'editar inasistencia',  'campo' => 'Inasistencias'],
            ['privilegio' => 'eliminar inasistencia','campo' => 'Inasistencias'],
        ];

        foreach ($privilegios as $p) {
            Privilegio::updateOrCreate(
                ['privilegio' => $p['privilegio']],
                ['campo' => $p['campo']]
            );
        }
    }
}
```

---

# FRONTEND (Angular)

## 7. `src/app/services/auth.ts`

```typescript
import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, tap } from 'rxjs';
import { Router } from '@angular/router';
import { URL_API } from '../config/constants';

@Injectable({
  providedIn: 'root',
})
export class Auth {
  private apiUrl = `${URL_API}/auth`;
  private privilegios: string[] = [];
  private campos: string[] = [];

  constructor(private http: HttpClient, private router: Router) {
    const cached = localStorage.getItem('privilegios');
    if (cached) {
      this.privilegios = JSON.parse(cached);
    }
  }

  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials).pipe(
      tap((response: any) => {
        if (response.results?.token) {
          localStorage.setItem('auth_token', response.results.token);
        }
        if (response.results?.empleado) {
          const empleado = response.results.empleado;
          localStorage.setItem('empleado', JSON.stringify(empleado));
          if (empleado.empleadoId) {
            localStorage.setItem('empleadoId', empleado.empleadoId);
          }
        }
        if (response.results?.privilegios) {
          this.privilegios = response.results.privilegios;
          this.campos = response.results.campos || [];
          localStorage.setItem('privilegios', JSON.stringify(this.privilegios));
          localStorage.setItem('campos', JSON.stringify(this.campos));
        }
      })
    );
  }

  isLoggedIn(): boolean {
    return !!localStorage.getItem('auth_token');
  }

  fetchMyPrivileges(): Observable<any> {
    return this.http.get(`${this.apiUrl}/me`).pipe(
      tap((res: any) => {
        this.privilegios = res.privilegios || [];
        this.campos = res.campos || [];
        localStorage.setItem('privilegios', JSON.stringify(this.privilegios));
        localStorage.setItem('campos', JSON.stringify(this.campos));
      })
    );
  }

  hasPrivilege(privilegio: string): boolean {
    if (this.privilegios.length > 0) {
      return this.privilegios.includes(privilegio);
    }
    const cached: string[] = JSON.parse(localStorage.getItem('privilegios') || '[]');
    return cached.includes(privilegio);
  }

  hasCampo(campo: string): boolean {
    if (this.campos.length > 0) {
      return this.campos.includes(campo);
    }
    const cached: string[] = JSON.parse(localStorage.getItem('campos') || '[]');
    return cached.includes(campo);
  }

  getPrivilegios(): string[] {
    return this.privilegios;
  }

  getCampos(): string[] {
    return this.campos;
  }

  logout() {
    this.http.post(`${this.apiUrl}/logout`, {}).subscribe({
      error: () => {},
    });
    localStorage.removeItem('auth_token');
    localStorage.removeItem('empleadoId');
    localStorage.removeItem('empleado');
    localStorage.removeItem('privilegios');
    localStorage.removeItem('campos');
    this.privilegios = [];
    this.campos = [];
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  getEmpleadoId(): string | null {
    return localStorage.getItem('empleadoId');
  }

  getEmpleado(): any {
    const data = localStorage.getItem('empleado');
    return data ? JSON.parse(data) : null;
  }
}
```

---

## 8. `src/app/app.ts`

```typescript
import { Component, inject } from '@angular/core';
import { Router, RouterOutlet, RouterLink, RouterLinkActive, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { Auth } from './services/auth';
import { KioskoService } from './services/kiosko';
import { KioskoOverlay } from './components/kiosko-overlay/kiosko-overlay';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, RouterLink, RouterLinkActive, KioskoOverlay],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  protected auth = inject(Auth);
  protected router = inject(Router);
  protected kiosko = inject(KioskoService);
  isLoggedIn = false;
  pageTitle = 'Dashboard';
  userName = '';
  userRol = '';
  fechaActual = '';

  private allMenuItems = [
    { path: '/menu', label: 'Dashboard' },
    { path: '/empleados', label: 'Empleados' },
    { path: '/cargos', label: 'Cargos' },
    { path: '/roles', label: 'Roles' },
    { path: '/aprobaciones', label: 'Aprobaciones' },
    { path: '/asistencias', label: 'Asistencias' },
    { path: '/inasistencias', label: 'Inasistencias' },
    { path: '/horarios', label: 'Horarios' },
    { path: '/feriados', label: 'Feriados' },
  ];

  menuItems = [...this.allMenuItems];

  private menuCampoMap: Record<string, string> = {
    'Empleados': 'Empleados',
    'Cargos': 'Cargos',
    'Roles': 'Roles',
    'Aprobaciones': 'Asistencias',
    'Asistencias': 'Asistencias',
    'Inasistencias': 'Inasistencias',
    'Horarios': 'Horarios',
    'Feriados': 'Feriados',
  };

  private titles: Record<string, string> = {
    '/menu': 'Dashboard',
    '/empleados': 'Gestión de Empleados',
    '/cargos': 'Gestión de Cargos',
    '/roles': 'Gestión de Roles',
    '/aprobaciones': 'Aprobaciones Pendientes',
    '/asistencias': 'Registro de Asistencias',
    '/inasistencias': 'Registro de Inasistencias',
    '/horarios': 'Gestión de Horarios',
    '/feriados': 'Gestión de Feriados',
  };

  ngOnInit(): void {
    this.kiosko.init();

    this.isLoggedIn = this.auth.isLoggedIn();

    if (this.isLoggedIn) {
      const empleado = this.auth.getEmpleado();
      this.userName = empleado?.nombre || 'Usuario';
      this.fechaActual = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
      this.loadPrivileges();
    }

    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd)
    ).subscribe(() => {
      this.isLoggedIn = this.auth.isLoggedIn();
      this.updatePageTitle();
      this.loadPrivileges();

      if (this.isLoggedIn) {
        const empleado = this.auth.getEmpleado();
        this.userName = empleado?.nombre || 'Usuario';
      }
    });
  }

  private loadPrivileges() {
    const cachedCampos = localStorage.getItem('campos');
    if (cachedCampos) {
      this.filterMenu(JSON.parse(cachedCampos));
    }
  }

  private filterMenu(campos: string[]) {
    this.menuItems = this.allMenuItems.filter(item => {
      if (item.label === 'Dashboard') return true;
      const campo = this.menuCampoMap[item.label];
      return campo ? campos.includes(campo) : false;
    });
  }

  private updatePageTitle() {
    this.pageTitle = this.titles[this.router.url] || 'Dashboard';
  }

  logout() {
    this.auth.logout();
  }
}
```

---

## 9. `src/app/app.html`

```html
<app-kiosko-overlay />
@if(isLoggedIn) {
<div class="min-h-screen flex flex-col bg-gray-50">

  <header class="h-14 flex items-center justify-between px-6 flex-shrink-0" style="background: #003DA5;">
    <div class="flex items-center gap-3">
      <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center text-white font-bold text-xs">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
        </svg>
      </div>
      <div>
        <p class="text-white text-sm font-semibold leading-tight">Sistema Dactilar</p>
        <p class="text-white/60 text-[10px] leading-tight">INCES</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: rgba(255,255,255,0.2);">
          {{ userName.charAt(0).toUpperCase() }}
        </div>
        <span class="text-white text-sm hidden sm:inline">{{ userName }}</span>
      </div>
      <button (click)="logout()" class="p-1.5 rounded-lg hover:bg-white/10 transition-colors" title="Cerrar sesión">
        <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
        </svg>
      </button>
    </div>
  </header>

  <nav class="bg-white border-b border-gray-200 flex items-center gap-1 px-4 py-0 overflow-x-auto flex-shrink-0" style="scrollbar-width: thin;">
    @for (item of menuItems; track item.path) {
    <a [routerLink]="item.path" routerLinkActive="text-[#003DA5] border-b-2 border-[#003DA5] font-semibold"
      class="flex items-center gap-1.5 px-3 py-3 text-xs whitespace-nowrap transition-all duration-150 border-b-2 border-transparent hover:text-[#003DA5] hover:bg-blue-50/50"
      style="color: #64748B;">
      @switch (item.label) {
        @case ('Dashboard') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
        }
        @case ('Empleados') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
        }
        @case ('Cargos') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
        }
        @case ('Roles') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/></svg>
        }
        @case ('Privilegios') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
        }
        @case ('Asistencias') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        }
        @case ('Inasistencias') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        }
        @case ('Horarios') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        }
        @case ('Feriados') {
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
        }
      }
      <span>{{ item.label }}</span>
    </a>
    }
  </nav>

  <div class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between flex-shrink-0">
    <h1 class="text-lg font-semibold" style="color: #1E293B;">{{ pageTitle }}</h1>
    <span class="text-xs" style="color: #94A3B8;">{{ fechaActual }}</span>
  </div>

  <main class="flex-1 overflow-y-auto">
    <router-outlet />
  </main>
</div>
} @else {
<router-outlet />
}
```

---

## 10. `src/app/components/login/login.ts`

```typescript
import { Component, ChangeDetectorRef, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormGroup, FormControl, Validators } from '@angular/forms';
import { Auth } from '../../services/auth';
import { Router } from '@angular/router';
import { MessageHelper } from '../../helpers/message';

@Component({
  selector: 'app-login',
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.html',
  styles: ``,
})
export class Login {
  loginForm: FormGroup;
  error: any = '';
  seeding = false;

  private cdr = inject(ChangeDetectorRef);

  constructor(
    private authService: Auth,
    private router: Router,
    private msg: MessageHelper
  ) {
    this.loginForm = new FormGroup({
      correo: new FormControl('', [Validators.required, Validators.email]),
      contraseña: new FormControl('', [Validators.required])
    });
  }

  onSubmit() {
    if (this.loginForm.invalid) return;
    if (this.seeding) return;

    this.seeding = true;
    this.error = '';
    this.cdr.detectChanges();

    this.authService.login(this.loginForm.value).subscribe({
      next: (response) => {
        if (response.results?.token) {
          this.router.navigate(['/menu']);
        }
        this.seeding = false;
      },
      error: (err) => {
        this.error = err?.error?.msg || this.msg.loginError();
        this.loginForm.get('contraseña')?.setValue('');
        this.seeding = false;
      },
    });
  }
}
```

---

## 11–18. Componentes con `@if(auth.hasPrivilege(...))`

Cada uno en su carpeta dentro de `src/app/components/`. Buscar la línea `if (!this.auth.hasPrivilege('ver X')) return;` en el `.ts` y `@if (auth.hasPrivilege('ver X'))` al inicio del `.html`.

### cargos
- `cargo.ts`: línea 35 `if (!this.auth.hasPrivilege('ver cargos')) return;`
- `cargo.html`: línea 1 `@if (auth.hasPrivilege('ver cargos')) {`

### roles
- `rol.ts`: línea 43 `if (!this.auth.hasPrivilege('ver roles')) return;`
- `rol.html`: línea 1 `@if (auth.hasPrivilege('ver roles')) {`

### empleados
- `empleado.ts`: línea 82 `if (!this.auth.hasPrivilege('ver empleados')) return;`
- `empleado.html`: línea 1 `@if (auth.hasPrivilege("ver empleados")) {`

### horarios
- `horario.ts`: línea 48 `if (!this.auth.hasPrivilege('ver horarios')) return;`
- `horario.html`: línea 1 `@if (auth.hasPrivilege('ver horarios')) {`

### feriados
- `feriado.ts`: línea 58 `if (!this.auth.hasPrivilege('ver feriados')) return;`
- `feriado.html`: línea 1 `@if (auth.hasPrivilege('ver feriados')) {`

### asistencias
- `asistencia.ts`: línea 28 `if (!this.auth.hasPrivilege('ver asistencias')) return;`
- `asistencia.html`: línea 1 `@if (auth.hasPrivilege('ver asistencias')) {`

### inasistencias
- `inasistencia.ts`: línea 28 `if (!this.auth.hasPrivilege('ver inasistencias')) return;`
- `inasistencia.html`: línea 1 `@if (auth.hasPrivilege('ver inasistencias')) {`

### aprobaciones
- `aprobaciones.ts`: línea 29 `if (!this.auth.hasPrivilege('aprobar asistencia')) return;`
- `aprobaciones.html`: línea 1 `@if (auth.hasPrivilege('aprobar asistencia')) {`

---

## Verificación post‑aplicación

1. Abrir DevTools → Network → login → response debe incluir:
   ```json
   "results": { "privilegios": ["ver empleados", ...], "campos": ["Empleados", ...] }
   ```
2. Abrir Application → Local Storage → debe aparecer `privilegios` y `campos` tras login
3. Menú debe filtrarse según privilegios del usuario (sin recargar)
4. Botones de crear/editar/eliminar deben ocultarse según privilegios
