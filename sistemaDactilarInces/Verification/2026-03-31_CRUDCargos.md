# CRUD Cargos

**Fecha**: 2026-03-31  
**Autor**: Antigravity  
**Tipo**: Nueva funcionalidad

---

## Resumen
Implementación del CRUD completo de Cargos adaptado a la nomenclatura base de `FeriadoController`, implementando separación de responsabilidades a través de Servicios y archivos de rutas dependientes.

---

## Archivos modificados

| Archivo | Tipo | Cambio |
|---------|------|--------|
| `app/Http/Controllers/CargoController.php` | Controller | Modificado |
| `app/Services/CargoService.php` | Service | Nuevo |
| `routes/cargo.php` | Route | Nuevo |
| `routes/api.php` | Route | Modificado |
| `tareas.md` | Doc | Modificado |

---

## Cambios detallados

### 1. Implementación del Patrón Controller-Service
**Archivos**: `app/Http/Controllers/CargoController.php`, `app/Services/CargoService.php`

**Qué hace**: Crea el CRUD con 5 funciones exactas mapeando la arquitectura objetivo: `findAll`, `findById`, `stored`, `updated` y `deleted`. Los identificadores pasan como `string $id` al Service el cual gestiona transacciones y validaciones.

**Por qué**: Necesitamos desacoplar la capa de solicitudes (`CargoController`) de las validaciones de negocio e integridad en DB (`CargoService`). Esto ademas adecua la nomenclatura en un marco común como el ya reflejado en `FeriadoController`.

**Cómo**: Inyección de dependencias `__construct(protected CargoService $service)`. Manejo exhaustivo con bloques `try...catch` en Controller, el Service retorna un array estructurado (error / msg / results).

**Impacto**: 
- Efecto positivo en el sistema al promover testeo y modularidad de la capa Service. Garantiza un API uniforme en tipo y respuesta.

**Buenas prácticas aplicadas**:
- [x] Arquitectura (Services/Controllers)
- [x] Seguridad (hash, validación)
- [x] Nomenclatura (métodos/variables en inglés)
- [x] Patrones de respuesta consistentes
- [x] Manejo de errores (try-catch)
- [x] Inyección de dependencias

---

### 2. Estructuración Modular de Rutas
**Archivos**: `routes/cargo.php`, `routes/api.php`

**Qué hace**: Separa las definiciones de rutas de Cargos en un archivo en solitario importado dinámicamente en el preámbulo de `api.php`.

**Por qué**: `api.php` incrementa rápido con las definiciones GET/POST/PUT/DELETE. Sub-estructurar el proyecto en sub-rutas alivia la carga visual y conflictos de nombres.

**Cómo**: `Route::prefix('cargo')->group(base_path('routes/cargo.php'));` incluido directamente en `api.php`. Por instrucciones específicas, no se encuentra bajo el middleware de protección `jwt.auth`.

**Impacto**: 
- Facilidad de lectura y delegación clara. Cada módulo posee su scope aislado. La ruta se maneja como pública actualmente.

**Buenas prácticas aplicadas**:
- [x] Arquitectura (separación modular de rutas)
