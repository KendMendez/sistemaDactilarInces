# Tareas Pendientes - Backend Sistema Dactilar INCES

---

## 🔴 ALTA PRIORIDAD

| #   | Tarea                                                                 | Categoría      |
| --- | --------------------------------------------------------------------- | -------------- |
| 1   | **CRUD Empleados** (listar, crear, editar, eliminar, buscar)          | Administración |
| 2   | **Registro de Asistencia** (entrada/salida con huella)                | Asistencia     |
| 3   | **Consulta de Asistencias** (listar por empleado/fecha/rango)         | Asistencia     |
| 4   | **Middleware de Roles/Privilegios** (verificar permisos en cada ruta) | Seguridad      |

---

## 🟡 MEDIA PRIORIDAD

| #   | Tarea                                                         | Categoría      |
| --- | ------------------------------------------------------------- | -------------- |
| 5   | ~~**CRUD Cargos**~~ (crear, listar, editar, eliminar cargos) ✅ | Administración |
| 6   | **CRUD Horarios** (asignar horarios a empleados por día)      | Administración |
| 7   | **CRUD Inasistencias** (registrar y gestionar inasistencias)  | Asistencia     |
| 8   | **CRUD Feriados** (gestionar días festivos)                   | Administración |
| 9   | **CRUD Roles** (crear y gestionar roles)                      | Seguridad      |
| 10  | **CRUD Privilegios** (crear y gestionar privilegios)          | Seguridad      |
| 11  | **Asignar Roles a Empleados**                                 | Seguridad      |
| 12  | **Asignar Privilegios a Roles**                               | Seguridad      |
| 13  | **Perfil de Empleado** (actualizar datos, cambiar contraseña) | Usuario        |

---

## 🟢 BAJA PRIORIDAD

| #   | Tarea                                                   | Categoría |
| --- | ------------------------------------------------------- | --------- |
| 14  | **Reportes de Asistencia** (resumen mensual, ausencias) | Reportes  |
| 15  | **Notificaciones** (recordatorios, alertas)             | Extras    |
| 16  | **Logs de Actividad** (auditoría de acciones)           | Seguridad |

---

## ✅ COMPLETADO

- Login / Logout
- Recuperación de contraseña por email
- Middleware JWT básico
- Modelos y migraciones
- LoginLog (auditoría de intentos)
- Refactorización de nomenclatura (Authentication + EmpleadoAuthService)

---

## Notas

- Ver buenaPracticas_SKILL.md para las 8 prácticas del proyecto
- Ver AGENTS.md para comandos de build/test/lint
- Documentar cambios en Verification/ después de cada feature
