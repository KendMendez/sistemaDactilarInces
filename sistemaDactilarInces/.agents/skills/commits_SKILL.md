name: documentarCambios

description: Genera documentación markdown con los cambios realizados en el código, siguiendo un template estandarizado que incluye análisis detallado de cada modificación

#documentarCambios_function: 
Crear archivos de verificación/documentación de cambios en la carpeta Verification/

##where or when use it:
- Al completar una funcionalidad nueva
- Después de refactorizar código existente
- Antes de hacer commit
- Al implementar correcciones de bugs
- Al aplicar buenas prácticas

##how to use it:

### Paso 1: Inventario de cambios
```
□ Listar TODOS los archivos modificados
□ Clasificar por tipo (controllers, services, models, routes, migrations)
□ Identificar archivos nuevos vs modificados
□ Asignar título breve a cada cambio
```

### Paso 2: Análisis profundo
Para cada cambio documentar:
```
- Nombre del cambio
- Archivos afectados
- Qué hace el cambio
- Por qué es necesario
- Cómo se implementó
- Impacto en el sistema
- Buenas prácticas aplicadas (referenciar buenasPracticas_SKILL.md)
```

### Paso 3: Generar documentación
Ubicación: `Verification/YYYY-MM-DD_TituloDelFeature.md`

### Paso 4: Nombrar archivo
```
Formato: Verification/YYYY-MM-DD_TituloDescriptivo.md
Ejemplos:
  - Verification/2026-03-31_CRUDEEmpleados.md
  - Verification/2026-03-31_RefactorAuthNomenclature.md
  - Verification/2026-03-31_FixLoginBug.md
```

---

## Template de documentación

```markdown
# [Título del Feature]

**Fecha**: YYYY-MM-DD  
**Autor**: [nombre]  
**Tipo**: Nueva funcionalidad | Refactor | Bug fix | Mejora

---

## Resumen
Breve descripción (2-3 oraciones máximo)

---

## Archivos modificados

| Archivo | Tipo | Cambio |
|---------|------|--------|
| `app/...` | Controller | Modificado |
| `app/...` | Service | Modificado |
| `routes/...` | Route | Nuevo |

---

## Cambios detallados

### 1. [Nombre del cambio]
**Archivos**: `app/Http/Controllers/...`, `app/Services/...`

**Qué hace**: Descripción clara de la funcionalidad

**Por qué**: Razón del cambio (negocio, bug, mejora)

**Cómo**: Explicación técnica de la implementación

**Impacto**: 
- Efecto positivo en el sistema
- Posibles consideraciones

**Buenas prácticas aplicadas**:
- [ ] Arquitectura (Services/Controllers)
- [ ] Seguridad (hash, validación)
- [ ] Nomenclatura (métodos/variables en inglés)
- [ ] Patrones de respuesta consistentes
- [ ] Manejo de errores (try-catch)
- [ ] Inyección de dependencias

---

### 2. [Siguiente cambio]
...
```

---

## Checklist final

```
□ Archivo creado en carpeta Verification
□ Todos los archivos modificados listados
□ Cada cambio tiene: qué, por qué, cómo, impacto
□ Buenas prácticas identificadas y listadas
□ Estructura de carpetas incluida
□ Formato markdown limpio y legible
□ Código sigue convenciones de nomenclatura
□ Verificar con Pint si hay errores de formato
```

---

## Referencia rápida

**8 Buenas prácticas** (ver `buenasPracticas_SKILL.md`):
1. Arquitectura y separación de responsabilidades
2. Seguridad
3. Patrones de respuesta consistente
4. Eloquent ORM
5. Manejo de errores
6. Configuraciones
7. Inyección de dependencias
8. Consistencia en nomenclatura
