# Sistema Dactilar INCES — Project Documentation

## Architecture Overview

- **Frontend**: Angular 17+ standalone components, Reactive Forms, HttpClient modules, Tailwind CSS (via inline styles)
- **Backend**: Laravel 11, JWT Auth (tymon/jwt-auth), MySQL (XAMPP), GD Library (fingerprint matching)
- **Communication**: REST JSON API at `http://localhost/sistemaDactilarInces/public/api`
- **Auth**: JWT tokens stored in `localStorage`; login via email/contraseña; forgot/reset password fully removed
- **Security**: All integer PKs encrypted client-side via `Crypt::encrypt()` / `Crypt::decrypt()` before DB queries; foreign keys (`id_empleado`, `id_cargo`, `roleId`) decrypted server-side
- **Router Prefixes**: All routes prefixed with `{module}.` name dot-notation for consistency

---

## Module Map

| # | Module | Frontend Component | Frontend Service | Backend Controller | Backend Service | Backend Model | Route Prefix |
|---|--------|--------------------|------------------|-------------------|----------------|--------------|-------------|
| 1 | Auth/Login | `login` | `auth.ts` | `EmpleadoAuthController` | — | `Empleado` | `auth.` |
| 2 | Empleado | `empleado` | `empleado.ts` | `EmpleadoController` | `EmpleadoService` | `Empleado` | `empleado.` |
| 3 | Asistencia | `asistencia` | `asistencia.ts` | `AsistenciaController` | `AsistenciaService` | `Asistencia` | `asistencia.` |
| 4 | Aprobaciones | `aprobaciones` | (same as asistencia) | (same as asistencia) | (same as asistencia) | (same as asistencia) | (same) |
| 5 | Cargo | `cargo` | `cargo.ts` | `CargoController` | `CargoService` | `Cargo` | `cargo.` |
| 6 | Rol | `rol` | `rol.ts` | `RolController` | `RolService` | `Role` | `rol.` |
| 7 | Privilegio | (none standalone) | `privilegio.ts` | `PrivilegioController` | `PrivilegioService` | `Privilegio` | `privilegio.` |
| 8 | Role-Privilegio | `role-privilegio` | `role-privilegio.ts` | `RolePrivilegioController` | `RolePrivilegioService` | `RolePrivilegio` | `role-privilegio.` |
| 9 | Horario | `horario` | `horario.ts` | `HorarioController` | `HorarioService` | `Horario` | `horario.` |
| 10 | Feriado | `feriado` | `feriado.ts` | `FeriadoController` | `FeriadoService` | `Feriado` | `feriado.` |
| 11 | Inasistencia | `inasistencia` | `inasistencia.ts` | `InasistenciaController` | `InasistenciaService` | `Inasistencia` | `inasistencia.` |
| 12 | Kiosko | `kiosko-overlay` | `kiosko.ts` + `huella.ts` | `KioskoController` | `KioskoService` | `Asistencia` + `Empleado` | `kiosko.` |
| 13 | Menu | `menu` | — | — | — | — | — |

---

## Detailed Module Documentation

### 1. Auth / Login Module

**Frontend**: `components/login/`
- `login.ts` — Component with ReactiveForm (correo, contraseña); calls `auth.login()`; on success navigates to `/menu`; shows error on failure; clears contraseña field on error
- `login.html` — Styled card with INCES logo placeholder, email/password inputs, error banner, submit button with loading spinner

**Frontend Service**: `services/auth.ts`
- `login(credentials)` → `POST /auth/login`; stores `auth_token`, `empleado` (JSON), `empleadoId` in localStorage on success
- `isLoggedIn()` — checks `auth_token` existence
- `logout()` — calls `POST /auth/logout` (ignores error), removes all localStorage items, navigates to `/login`
- `getToken()`, `getEmpleadoId()`, `getEmpleado()` — getters from localStorage

**Backend Controller**: `app/Http/Controllers/EmpleadoAuthController.php`
- `login()` — Validates correo+contraseña; attempts JWT auth; returns `{error, msg, results: {token, empleado}}`; returns 401 with `Credenciales inválidas` on failure
- `logout()` — Invalidates current JWT token; returns JSON response

**Backend Model**: `Empleado` (extends `Authenticatable`)
- `getAuthPassword()` returns `$this->contraseña`
- `getAuthIdentifierName()` returns `'correo'`
- Hidden: `contraseña`, `huella_pulgar`, `huella_indice`

**Routes**: `routes/api.php`
- `POST /auth/login` → `EmpleadoAuthController@login` (name: `auth.login`)
- `POST /auth/logout` → `EmpleadoAuthController@logout` (name: `auth.logout`)

---

### 2. Empleado Module

**Frontend Component**: `components/empleado/`
- `empleado.ts` (447 lines) — Full CRUD component with:
  - Card grid display with photo, name, cargo, cédula, correo, teléfono, sexo
  - Search by cédula (`service.search(q)`) with loading state
  - Form with all fields: nombre, apellido, identificacion (pattern `^\d+$`), correo (email), contraseña (min:8), telefono (Venezuelan format `^0\d{10}$`), sexo (M/F), id_cargo (dropdown), roleId (dropdown)
  - Photo upload: file picker, preview, 2MB size limit
  - Fingerprint capture modal: sequential capture (pulgar → índice) via `HuellaService`, stepper UI (1/2), auto-advance after 2s, "Continuar"/"Finalizar" buttons; pauses/resumes kiosko scanner
  - Password: eye toggle (show/hide SVG), "Cambiar contraseña" checkbox on edit
  - Cargo/Rol dropdowns with collapsed expandable lists
  - Delete with dependency confirmation dialog
  - `trackById()` uses `empleadoId`
- `empleado.html` (418 lines) — Two views (list/form/modal) via `@if` blocks

**Frontend Service**: `services/empleado.ts`
- `index()`, `search(identificacion)`, `store(data)`, `update(id, data)`, `delete(id, force?)`

**Backend Controller**: `app/Http/Controllers/EmpleadoController.php`
- `index()` → `EmpleadoService::index()` — Returns all empleados with cargo, foto, rolIds
- `store(req)` → `EmpleadoService::store()` — validates: id_cargo, nombre, apellido, telefono, identificacion (`regex:/^\d+$/`), correo, contraseña (`min:8`), sexo, foto (nullable), huella_pulgar (nullable), huella_indice (nullable), roleId (nullable)
- `update(req, id)` → `EmpleadoService::update()` — same validation but all `sometimes`
- `search(identificacion)` → `EmpleadoService::findByIdentificacion()` — returns 404 if not found
- `delete(req, id)` → `EmpleadoService::delete()` — dependency check before cascade
- All catch blocks have `Log::error()` calls

**Backend Service**: `app/Services/EmpleadoService.php`
- `index()` — loads with `cargo:id,cargo`, `roles:id,role`; encrypts IDs; returns with `empleadoId`, `cargo` object, `rolIds` array
- `store(data)` — decrypts `id_cargo`; validates duplicate `identificacion`; **unset `roleId`** before `create()`; assigns roles via `RoleEmpleado::insert()`; encrypts password
- `update(id, data)` — decrypts `id_cargo`; **unset `roleId`** before `update()`; uses local variable for `assignRoles()`; validates duplicate identificación (excluding self)
- `delete(id, force)` — checks empleados referencing this ID before deleting
- `findByIdentificacion(identificacion)` — queries by raw identificacion string
- `assignRoles(empleadoId, roleIds)` — decrypts `roleIds` JSON; deletes existing `RoleEmpleado` entries; inserts new ones
- `validateBase64Image(base64)` — max 2048KB; `validateHuellaBase64(base64)` — max 2048KB

**Backend Model**: `app/Models/Empleado.php`
- Fillable: id_cargo, nombre, apellido, telefono, identificacion, correo, contraseña, foto, sexo, huella_pulgar, huella_indice
- Casts: foto, huella_pulgar, huella_indice → string
- Relationships: `cargo()` (BelongsTo Cargo), `roles()` (BelongsToMany via role_empleados)

**Routes**: `routes/empleado.php`
- `GET /empleado/index` → `index` (name: `empleado.index`)
- `POST /empleado/store` → `store` (name: `empleado.store`)
- `PUT /empleado/update/{id}` → `update` (name: `empleado.update`)
- `GET /empleado/search/{identificacion}` → `search` (name: `empleado.search`)
- `DELETE /empleado/{id}/delete` → `delete` (name: `empleado.delete`)

---

### 3. Asistencia Module

**Frontend Component**: `components/asistencia/`
- `asistencia.ts` (60 lines) — Loads data, table display via `trackById()` using `asistenciaId`; delete with dependency confirmation
- `asistencia.html` (86 lines) — Full table: #, Empleado (from `empleado_nombre`), Fecha, Entrada, Salida (`—` if null), Estado (color-coded badge: green=presente, yellow=pending_approval, blue=approved, red=rejected), Tipo (Kiosko/Manual), Acciones (delete button SVG)

**Frontend Service**: `services/asistencia.ts`
- `index()`, `store(data)`, `update(id, data)`, `delete(id, force?)`, `pending()`, `approve(id)`, `reject(id)`

**Backend Controller**: `app/Http/Controllers/AsistenciaController.php`
- `index()` → `AsistenciaService::index()`
- `store(req)` → `AsistenciaService::store()` — validates id_empleado, fecha, hora_entrada, hora_salida
- `update(req, id)` → `AsistenciaService::update()`
- `delete(id)` → `AsistenciaService::delete()`
- `pending()` → `AsistenciaService::pending()` — returns where `status = 'pending_approval'`
- `approve(id)` → `AsistenciaService::approve()` — sets `status = 'approved'`
- `reject(id)` → `AsistenciaService::reject()` — sets `status = 'rejected'`

**Backend Service**: `app/Services/AsistenciaService.php`
- `index()` — loads with `empleado:id,nombre,apellido`; includes `empleado_nombre` in response; encrypts IDs
- `pending()` — same as index but filtered to `status = 'pending_approval'`
- `approve(id)` / `reject(id)` — decrypt id, update status
- `store(data)`, `update(id, data)`, `delete(id)` — standard CRUD with decrypt encryption

**Backend Model**: `app/Models/Asistencia.php`
- Fillable: id_empleado, fecha, hora_entrada, hora_salida, status, tipo_marcacion
- Casts: fecha → date:Y-m-d; status, tipo_marcacion → string
- Relationships: `empleado()` (BelongsTo Empleado)

**Routes**: `routes/asistencia.php`
- `GET /asistencia/index`, `POST /asistencia/store`, `PUT /asistencia/update/{id}`, `DELETE /asistencia/{id}/delete`
- `GET /asistencia/pending`, `PUT /asistencia/{id}/approve`, `PUT /asistencia/{id}/reject`

---

### 4. Aprobaciones Module

**Frontend Component**: `components/aprobaciones/`
- `aprobaciones.ts` (79 lines) — Loads pending list via `AsistenciaService.pending()`; `approve(id)` / `reject(id)` with processing state
- `aprobaciones.html` (47 lines) — Table: Empleado (shows `id_empleado` encrypted ID — should show name via `empleado_nombre`), Fecha, Entrada, Salida, Acción (Aprobar/Rechazar buttons)

*Note: Reuses `AsistenciaService` and `AsistenciaController` (same as Asistencia module)*

---

### 5. Cargo Module

**Frontend Component**: `components/cargo/`
- `cargo.ts` (116 lines) — CRUD with form (single field: cargo), delete with dependency confirmation
- `cargo.html` (103 lines) — Table view (#, Cargo, Acciones) + Form view (nombre del cargo input)

**Frontend Service**: `services/cargo.ts`
- `index()`, `store(data)`, `update(id, data)`, `delete(id, force?)`

**Backend Controller**: `app/Http/Controllers/CargoController.php`
- CRUD operations; `delete()` returns `requires_confirmation` with employee count if employees assigned and `force` not set

**Backend Service**: `app/Services/CargoService.php`
- `delete(id, force)` — counts employees with this cargo; returns `{requires_confirmation, msg, dependencies}` if > 0 and not forced; sets `id_cargo = null` on employees before deleting

**Backend Model**: `app/Models/Cargo.php`
- Fillable: `cargo`
- Relationships: `empleados()` (HasMany)

**Routes**: `routes/cargo.php`
- Standard CRUD: `GET /cargo/index`, `POST /cargo/store`, `PUT /cargo/update/{id}`, `DELETE /cargo/{id}/delete`

---

### 6. Rol Module

**Frontend Component**: `components/rol/`
- `rol.ts` (196 lines) — CRUD with privilege assignment (checkboxes); loads privilegios on init; `selectedPrivs` array; calls `RolePrivilegioService.store()` after save
- `rol.html` (128 lines) — Table + Form with privilege grid (2 columns, scrollable)

**Frontend Service**: `services/rol.ts`
- `index()`, `store(data)`, `update(id, data)`, `delete(id, force?)`

**Backend**: `RolController`, `RolService`, `Role` model
- `Role` fillable: `role`
- Relationships: `empleados()` (BelongsToMany via role_empleados), `privilegios()` (BelongsToMany via role_privilegios)

**Routes**: `routes/rol.php`
- Standard CRUD

---

### 7. Privilegio Module

**Frontend Service**: `services/privilegio.ts`
- `index()`, `store(data)`, `update(id, data)`, `delete(id, force?)`

**Backend Model**: `app/Models/Privilegio.php`
- Fillable: `privilegio`
- Relationships: `roles()` (BelongsToMany via role_privilegios)

**Routes**: `routes/privilegio.php` — Standard CRUD

---

### 8. Role-Privilegio Module

**Frontend Component**: `components/role-privilegio/`
- `role-privilegio.ts` (68 lines) — Dropdown selects role, shows privilege checkboxes, toggle to assign/remove
- `role-privilegio.html` — Placeholder: `<!-- TODO: Diseño pendiente -->`

**Frontend Service**: `services/role-privilegio.ts`
- `showByRoleId(roleId)` → `GET /role-privilegio/showByRoleId/{roleId}`
- `store(data)` → `POST /role-privilegio/store`

**Backend Controller**: `RolePrivilegioController`
- `store()` — accepts `roleId` (encrypted) + `arrPrivilegioId` (JSON array encrypted IDs); decrypts both; syncs `RolePrivilegio` table
- `showByRoleId(roleId)` — returns all privileges for a role

**Backend Service**: `RolePrivilegioService`
- `store($data)` — calls `RolePrivilegio::where('id_role', $roleId)->delete()` then insert new records
- `showByRoleId($roleId)` — returns with encrypted IDs

**Backend Model**: `app/Models/RolePrivilegio.php`
- Fillable: `id_role`, `id_privilegio`

**Routes**: `routes/role-privilegio.php`
- `POST /role-privilegio/store`, `GET /role-privilegio/showByRoleId/{roleId}`

---

### 9. Horario Module

**Frontend Component**: `components/horario/`
- `horario.ts` (156 lines) — CRUD with day checkboxes (lunes–domingo), empleado dropdown (loaded on form open), 4 time fields (hora_entrada, hora_salida, hora_entrada_tolerada, hora_salida_tolerada)
- `horario.html` (163 lines) — Table view (7 columns) + Form with day checkboxes (stylized labels), time inputs, empleado select

**Frontend Service**: `services/horario.ts`
- `index()`, `store(data)`, `update(id, data)`, `delete(id, force?)`

**Backend Controller**: `app/Http/Controllers/HorarioController.php`
- CRUD; validates `id_empleado`, `dia` (array of distinct strings), `hora_entrada`/`hora_salida` (nullable), `hora_entrada_tolerada`/`hora_salida_tolerada` (required)

**Backend Service**: `app/Services/HorarioService.php`
- `store(data)` — decrypts `id_empleado`; checks for duplicate (same employee + same day via `whereJsonContains('dia', $day)`); creates if unique
- `update(id, data)` — decrypts id; checks for duplicate on same employee+day excluding self

**Backend Model**: `app/Models/Horario.php`
- Fillable: id_empleado, dia, hora_entrada, hora_salida, hora_entrada_tolerada, hora_salida_tolerada
- Casts: `dia` → `array` (JSON column); all times → string
- Relationships: `empleado()` (BelongsTo)

**Routes**: `routes/horario.php` — Standard CRUD

---

### 10. Feriado Module

**Frontend Component**: `components/feriado/`
- `feriado.ts` (108 lines) — CRUD with date + descripcion form
- `feriado.html` (104 lines) — Table (#, Fecha, Descripción, Acciones) + Form (date input, descripcion text input)

**Frontend Service**: `services/feriado.ts`
- `index()`, `store(data)`, `update(id, data)`, `delete(id, force?)`

**Backend Controller**: `app/Http/Controllers/FeriadoController.php`
- CRUD; accepts date in `Y-m-d` or `d/m/Y` format; uses Validator facade

**Backend Service**: `app/Services/FeriadoService.php`
- `store(data)` — parses date (supports both `Y-m-d` and `d/m/Y`); checks duplicate
- `update(id, data)` — same date parsing; updates if no duplicate

**Backend Model**: `app/Models/Feriado.php`
- Fillable: `fecha`, `descripcion`
- Casts: `fecha` → `date:Y-m-d`

**FeriadoSeeder**: 83 records covering Venezuelan holidays 2025–2030

**Routes**: `routes/feriado.php` — Standard CRUD

---

### 11. Inasistencia Module

**Frontend Component**: `components/inasistencia/`
- `inasistencia.ts` (63 lines) — Read-only table with delete; shows empleado name via relationship
- `inasistencia.html` (46 lines) — Table: #, Empleado, Fecha, Justificación, Acciones (delete)

**Frontend Service**: `services/inasistencia.ts`
- `index()`, `store(data)`, `update(id, data)`, `delete(id, force?)`

**Backend Controller**: `app/Http/Controllers/InasistenciaController.php`
- CRUD; validates: id_empleado, fecha, justificacion

**Backend Service**: `app/Services/InasistenciaService.php`
- `index()` — loads with `empleado:id,nombre,apellido`
- `store(data)`, `update(id, data)` — checks duplicate by id_empleado + fecha

**Backend Model**: `app/Models/Inasistencia.php`
- Fillable: `id_empleado`, `fecha`, `justificacion`
- Casts: `fecha` → `date:Y-m-d`
- Relationships: `empleado()` (BelongsTo)

**GenerarInasistencias Command** (`app/Console/Commands/GenerarInasistencias.php`):
- Runs daily at 18:00 via `schedule->command('inasistencias:generar')->dailyAt('18:00')`
- Skips weekends and feriados
- For each empleado with no asistencia and no existing inasistencia on today's date, creates an inasistencia record with justificación `"Inasistencia automática"`

**Routes**: `routes/inasistencia.php` — Standard CRUD

---

### 12. Kiosko Module

**Frontend Component**: `components/kiosko-overlay/`
- `kiosko-overlay.ts` (13 lines) — Subscribes to `KioskoService.status$` observable
- `kiosko-overlay.html` (34 lines) — Modal overlay (fixed inset, bg-black/60 backdrop): shows spinning icon for `info`, checkmark for `success`, X for `error`; auto-hides after timeout

**Frontend Service**: `services/kiosko.ts` (138 lines)
- Uses `window.Fingerprint.WebApi` (SDK) for fingerprint acquisition
- `init()` → `startListening()` → creates WebApi instance, sets up `onSamplesAcquired` callback, enumerates devices, starts acquisition with `SampleFormat.PngImage`
- `onSample(e)` — parses samples JSON; gets PNG base64 via `Fingerprint.b64UrlTo64()`; sends `POST /kiosko/match` with `X-Kiosko-Key` header; on match calls `registrarAsistencia()`; on no match shows error overlay for 3s
- `registrarAsistencia(idEmpleado, nombre)` — `POST /kiosko/verificar`; shows success/error overlay for 4s
- `dispose()` — stops listening, disconnects WebChannel
- `pause()` / `resume()` — for temporary stop/start during empleado fingerprint capture
- `status$` BehaviorSubject drives overlay visibility

**Frontend Service**: `services/huella.ts` (135 lines)
- Standalone fingerprint capture for empleado registration (not kiosko)
- `capture()` → returns `Observable<string>` (PNG base64); 30s timeout; single-sample; stops acquisition after capture
- `cancelCapture()` — stops acquisition
- `dispose()` — cleanup

**Backend Controller**: `app/Http/Controllers/KioskoController.php`
- `verificar(req)` → `KioskoService::verificar(id_empleado)` — validates `id_empleado` string
- `match(req)` → `KioskoService::matchFingerprint(huella)` — validates `huella` string
- All catch blocks have `Log::error()` calls

**Backend Service**: `app/Services/KioskoService.php` (206 lines)
- `verificar(encryptedId)` — Core attendance logic:
  1. Decrypts `id_empleado`
  2. Anti-spam: `Cache::add('kiosko_spam_' . id, true, 3)` → 3 min TTL per employee
  3. Checks existing asistencia for today
  4. If none: creates with `hora_entrada = now`, `status = presente` (or `pending_approval` if >15min past tolerada), `tipo_marcacion = 'kiosko'`
  5. If exists and no `hora_salida`: updates with `hora_salida = now`
  6. If `hora_salida` already set: returns "Ya completó su jornada"
  7. Returns `{error, msg, tipo, status}`
- `matchFingerprint(huella)` — Server-side GD comparison:
  1. `imagecreatefromstring()` from base64
  2. Resize to 32×32 (`imagecopyresampled`)
  3. Convert to grayscale (average RGB)
  4. Min-max normalize contrast to [0.0, 1.0]
  5. Compare against ALL employees with stored fingerprints (pulgar + índice)
  6. Score = average pixel difference (lower = better match)
  7. Threshold = 0.25; returns `{id_empleado (encrypted), nombre, score}` if below threshold
- `normalizeFingerprint(img, w, h)` — resize + grayscale + contrast normalize
- `compareNormalized(a, b)` — average absolute pixel difference

**Backend Middleware**: `app/Http/Middleware/KioskoMiddleware.php`
- Validates `X-Kiosko-Key` header against `config('kiosko.api_key')`
- Registered as `kiosko` route middleware alias via `$routeMiddleware` in `Kernel.php`

**Config**: `config/kiosko.php` — returns `['api_key' => env('KIOSKO_API_KEY')]`

**Routes**: `routes/kiosko.php`
- `POST /kiosko/verificar` → `verificar` (middleware: `kiosko`)
- `POST /kiosko/match` → `match` (middleware: `kiosko`)

---

### 13. Menu Module

**Frontend Component**: `components/menu/`
- `menu.ts` (9 lines) — Empty shell component
- `menu.html` (13 lines) — Welcome message: "Bienvenido al Sistema Dactilar INCES" with INCES icon, description "Seleccione un módulo en la barra de navegación superior"

---

## Database Schema (Key Tables)

| Table | Key Columns | Notes |
|-------|------------|-------|
| `empleados` | id, id_cargo (FK), nombre, apellido, identificacion, correo, contraseña, foto (longText), huella_pulgar (longText), huella_indice (longText), sexo, telefono | `foto`/`huellas` migrated to `longText` via `2026_06_03_000001_fix_foto_huellas_to_longtext.php` |
| `cargos` | id, cargo | |
| `roles` | id, role | |
| `role_empleados` | id_empleado (FK), id_role (FK) | Pivot table |
| `role_privilegios` | id_role (FK), id_privilegio (FK) | Pivot table |
| `privilegios` | id, privilegio | |
| `asistencias` | id, id_empleado (FK), fecha, hora_entrada, hora_salida (nullable), status, tipo_marcacion | `hora_salida` made nullable via `2026_06_03_180000_make_hora_salida_nullable_on_asistencias.php`; `status` defaults to `presente` |
| `horarios` | id, id_empleado (FK), dia (JSON), hora_entrada, hora_salida, hora_entrada_tolerada, hora_salida_tolerada | `dia` is JSON array |
| `feriados` | id, fecha, descripcion | |
| `inasistencias` | id, id_empleado (FK), fecha, justificacion | |

---

## Key Migration Files

1. `2026_06_03_000001_fix_foto_huellas_to_longtext.php` — Changes `foto`, `huella_pulgar`, `huella_indice` columns from `text` to `longText`
2. `2026_06_03_180000_make_hora_salida_nullable_on_asistencias.php` — Makes `hora_salida` nullable on `asistencias` table
3. Migration(s) that added `status` and `tipo_marcacion` columns to `asistencias` (removed `id_supervisor`, `motivo_rechazo`, `bitacora_asistencias` references)

---

## Critical Bug Fixes & Key Behaviors

### `roleId` Must Be Unset Before `update()`/`create()`
- `EmpleadoService::store()` (line 105) and `update()` (line 161): `roleId` is a pivot-table field (for `role_empleados`), NOT a column in `empleados` table
- Fix: save `roleId` to local var, `unset($empleado['roleId'])` before `Empleado::create()`/`->update()`, then use local var for `$this->assignRoles()`

### `hora_salida` Made Nullable
- Kiosko creates attendance with only `hora_entrada`; `hora_salida` is set on second kiosko touch
- MySQL strict mode rejects INSERT without value for NOT NULL column with no default → 500 error
- Fix: migration `2026_06_03_180000_make_hora_salida_nullable_on_asistencias.php`

### GD Fingerprint Matching (No FMD SDK)
- `ExtractFmd`, `FmdFormat`, `FmdProcessingLogic`, `StringToFmd`, `Matcher` functions were never available in the Fingerprint SDK
- All matching is server-side via GD library: resize 32×32 → grayscale → contrast normalize → pixel diff → threshold 0.25

### Logging
- `Log::error()` added to all catch blocks in `EmpleadoController` (index, update, search, delete) and `KioskoController` (verificar, match)

---

## Validation Rules Summary

| Field | Frontend (Angular) | Backend (Laravel) |
|-------|-------------------|-------------------|
| identificacion | `pattern(/^\d+$/)` | `regex:/^\d+$/` |
| contraseña | `minLength(8)` on new/create | `min:8`; required on store, sometimes on update |
| correo | `Validators.email` | `email` |
| telefono | Custom `venezuelanPhoneValidator` (`^0\d{10}$`) | `string|max:255` |
| foto | Max 2MB client check | `validateBase64Image` 2048KB |
| roleId | JSON.stringify([value]) | Decrypt JSON, array of IDs |

---

## Route Structure

All route files are under `routes/`:
- `api.php` — auth routes
- `empleado.php`, `asistencia.php`, `cargo.php`, `rol.php`, `privilegio.php`, `role-privilegio.php`, `horario.php`, `feriado.php`, `inasistencia.php`, `kiosko.php`

Every route is named with the module prefix (e.g., `empleado.index`, `asistencia.store`, `kiosko.verificar`).

---

## Component Tree (Angular)

```
app-root
├── app-login          (/login)
├── app-menu           (/menu)
├── app-empleado       (/empleados)
├── app-asistencia     (/asistencias)
├── app-aprobaciones   (/aprobaciones)
├── app-cargo          (/cargos)
├── app-rol            (/roles)
├── app-role-privilegio (/role-privilegio)
├── app-horario        (/horarios)
├── app-feriado        (/feriados)
├── app-inasistencia   (/inasistencias)
└── app-kiosko-overlay (global, no route needed; shows on fingerprint detection)
```

---

## Configuration

- **`.env`**: Database connection, JWT secret (JWT_SECRET), Kiosko API key (KIOSKO_API_KEY), app URL
- **`config/kiosko.php`**: `return ['api_key' => env('KIOSKO_API_KEY')]`
- **`config/jwt.php`**: Tymon JWT config
- **Angular constants**: `URL_API = 'http://localhost/sistemaDactilarInces/public/api'`, `KIOSKO_API_KEY` from environment

---

## Build & Run

**Backend**:
```bash
cd C:/xampp/htdocs/sistemaDactilarInces
php artisan serve  # or use Apache pointing to /public
php artisan schedule:run  # for GenerarInasistencias
```

**Frontend**:
```bash
cd C:/UISistemaDactilarInces
ng serve --port 4200
ng build --configuration production  # for deployment
```

Apache must have `extension=gd` enabled in `php.ini` for fingerprint matching.
