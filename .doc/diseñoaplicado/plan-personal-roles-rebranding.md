# Plan — Rebranding de Personal y Roles (drawers CRUD + rol de 3 niveles)

**Estado:** ✅ EJECUTADO Y CERRADO (Orca) — QA en navegador real verificado
**Fecha:** 2026-07-18

## Context

`dash-users-list.php` ("Personal y Roles") es una de las últimas vistas en el shell viejo DataTables/Bootstrap. Objetivo: migrarla al lineamiento ya aplicado en Baños/Contratos/Servicios/Facturas/**Certificados**: listado con `native-table.js`, **crear y editar usuarios en drawer lateral embebido**, y el **dropdown de acciones con el diseño nuevo** (tarjeta redondeada + sombra, no el bare-Bootstrap viejo).

A diferencia de certificados, usuarios **sí tiene CRUD completo en DDD** (`insert`, `update`, `find`, `deactivate`, `setCategory`). El caso de referencia es **`dash-services.php`** (drawer con modos `new` y `edit`). Dos complicaciones propias: (1) los formularios llevan **subida de avatar** (dropzone + `multipart/form-data`) — primer drawer del proyecto con file upload; (2) **selección de rol con 3 niveles** (SuperAdministrador nuevo, Administrador, Usuario), que toca `CreateUser`/`UpdateUser` y requiere la fila SuperAdministrador en `category`.

## 0. Estado actual (hallazgos)

| Archivo | Rol hoy | Tras el plan |
|---|---|---|
| `dash-users-list.php` | Listado, shell viejo DataTables + dropdown bare | `native-table.js` + drawer + dropdown nuevo |
| `dash-users-add.php` | Página completa "Nuevo Usuario" (avatar upload) → `user-new.php` | **Huérfana** (drawer `?action=new`) |
| `dash-users-edit.php` | Página completa "Editar Usuario" (avatar upload) → `user-update.php` | **Huérfana** (drawer `?action=edit&id_User=X`) |
| `dash-users-profile.php` | Perfil propio (dt-card viejo) + modal password | **Restyle** (queda, no es huérfana) |
| `controller/user-new.php` | Crea (`CreateUser`, hardcodea `category=2`) | Reutilizar + aceptar `category` + fix redirects |
| `controller/user-update.php` | Edita (`UpdateUser`, no toca category) | Reutilizar + setear `category` + fix redirects |
| `controller/user-inactive.php` | Soft-delete (`DeactivateUser`) | Reutilizar (label **"Inactivar"**) |
| `controller/user-default-pass.php` | Reset password | Reutilizar (queda en el dropdown) |
| `controller/user-setadmin.php` | Toggle 1↔2 (`ToggleUserAdmin`) | **Retirado** (rol se edita en el drawer) → huérfano |

**Hallazgos clave:**
- **DDD completo:** `UserRepositoryInterface` tiene `insert`, `update(id, email, username, name, lastname, image)`, `find(id)`, `listActiveWithCategory()`, `deactivate(id, hash)`, `setCategory(id, category)`, `updatePassword`. Entidad `User` `readonly`. Use cases: `CreateUser::handle(array,string):int` (hardcodea `category:2`), `UpdateUser::handle(int,array,?string)` (no toca category), `FindUser`, `ListActiveUsers`, `DeactivateUser`, `ResetUserPassword`, `ToggleUserAdmin`.
- **Avatar upload** en add (incondicional) y edit (condicional, `$_FILES['file']['error']==0`). Dir `app/public/uploads/users/`. Dropzone `data-dropzone-target="#file"` + `<input type=file name=file hidden>`.
- **Dropdown viejo** en `dash-users-list.php` (bare `dropdown-menu`, `dropdown-item`, `text-danger`). Gate `$_SESSION['category'] == 1` muestra acciones admin; el resto ve spans `disabled`.
- **Redirects rotos:** `user-new.php` (error → `index.php`), `user-update.php` (echo antes de header + → `index.php`), `user-default-pass.php` (error → `index.php`).
- **`category` solo tiene (1, Administrador), (2, Usuario)** — falta SuperAdministrador.
- **NO hay Choices.js** en estos forms → el selector de rol es un `<select>` plano estilizado (sin gotcha de scoping). La única novedad de componente es el dropzone.

## 1. Alcance

**Incluye:** migrar el listado a `native-table.js` + dropdown nuevo; drawer `#user-drawer` new+edit con avatar dropzone + selector de rol (3 niveles); migración chica que agrega `(3,'SuperAdministrador')` a `category`; backend de rol (`CreateUser` acepta `category`, `UpdateUser` setea `category`); fix de redirects; restyle de `dash-users-profile.php` + su modal; registrar huérfanos.

**No incluye:** el enforcement RBAC (permisos por acción, elevación step-up, "solo SuperAdmin otorga SuperAdmin") — es el `.doc/plan-roles-permisos.md` separado; y borrado real (se mantiene soft-delete "Inactivar").

## 2. Listado (`dash-users-list.php`)

Calco de `dash-services.php` (tabla con acciones, sin pills ni grid):
- Quitar `<link>`/scripts DataTables + `DataTable.init`; cargar `assets/js/components/native-table.js`.
- Toolbar: "Personal y Roles (N)" + buscador `data-table-search-input` + botón "Agregar Usuario" → `?action=new`.
- Tabla `data-table-native-wrap`, card `rounded-3xl border shadow-sm`. Columnas: Usuario (avatar+username), Nombre, Email, Categoría (badge), Acciones.
- Acciones (preservar gate `$_SESSION['category'] == 1`): **Editar** (pencil → `?action=edit&id_User=X`); dropdown **nuevo diseño** con **Password Default** + **Inactivar** (SweetAlert2, rose). **Retirar "Set Admin/User"**. No-admin siguen viendo `disabled`.

## 3. Drawer `#user-drawer` (new + edit)

Calco de `dash-services.php`. `<form method=post enctype=multipart/form-data>` → `user-new.php` (new) / `user-update.php` (edit). Campos: `id` hidden (edit); `useremail`, `username`, `name`, `lastname`; `password` (solo new); **`category`** `<select>` iterando `SELECT id_category, name_category FROM category` (preselección del rol actual en edit); **avatar** dropzone (edit muestra avatar actual, opcional). Prefill edit vía `FindUser`. **⚠️ Verificar dropzone dentro del drawer** (foco de QA).

## 4. Migración `category`

`mysql/migrations/2026-07-superadmin-category.sql` idempotente:
```sql
INSERT INTO `category` (`id_category`, `name_category`)
VALUES (3, 'SuperAdministrador')
ON DUPLICATE KEY UPDATE `name_category` = VALUES(`name_category`);
```
Compatible con la Fase 0 del `.doc/plan-roles-permisos.md`.

## 5. Backend de rol (crítico)

- `CreateUser::handle`: tomar `category` de `$input`, **validado** contra `category.id_category` (o {1,2,3}); default `2` si falta/inválido.
- `UpdateUser`: setear categoría además de los campos actuales — reutilizar `MysqliUserRepository::setCategory(id, category)`, misma validación.
- **Seguridad:** validar el `category` server-side. "Solo SuperAdmin otorga SuperAdmin" NO va acá (llega con el plan de roles).

## 6. Redirects (fix)

- `user-new.php`: éxito → `dash-users-list.php?flash=success&msg=...`; error/else → `?flash=error` (hoy `index.php`).
- `user-update.php`: quitar el `echo '<script>alert...'`; éxito → `?flash=success`; error → `?flash=error`.
- `user-inactive.php` / `user-default-pass.php`: confirmar vuelta a la lista con flash.

## 7. Huérfanos a registrar

Cluster "Personal y Roles" en `.doc/orfanatos-pendientes.md`: 🔴 `dash-users-add.php`, 🔴 `dash-users-edit.php`, 🔴 `controller/user-setadmin.php` + `ToggleUserAdmin`. Puntos a tocar al limpiar: `layouts/sidebar.php` (`match`) y `layouts/header.php` (breadcrumbs add/edit). Registrar, no tocar aún.

## 8. Fases / Orquestación secuencial (Orca)

Terminales `opencode` en el worktree activo (sin worktree nuevo). Modelo por complejidad (`AGENTS.md § Orquestación de planes`); la criticidad de seguridad manda. **Guardrail en cada spec:** prohibir leer `.env`/login real; verificar con sesión efímera inyectada + `curl --cookie`.

| Tarea | Entregable | Riesgo | Modelo | Depende de |
|---|---|---|---|---|
| **T0** | Migración `(3,'SuperAdministrador')` en `category` + correr en local | Bajo | MiniMax-M3 | — |
| **T1** | `dash-users-list.php` → `native-table.js` + dropdown nuevo (Editar→drawer, Password Default, Inactivar; retirar Set Admin/User); preservar gate `category==1` | Medio | GLM-5.2 | T0 |
| **T2** | Drawer `#user-drawer` new+edit (avatar dropzone + `<select>` rol desde `category`, `multipart`), reemplaza add+edit | Medio | GLM-5.2 | T1 |
| **T3** | Backend rol: `CreateUser` acepta `category` validado; `UpdateUser` setea `category` (vía `setCategory`) | Alto (seguridad) | Sonnet 5 | T2 |
| **T4** | Fix redirects `user-new.php`/`user-update.php` | Bajo | MiniMax-M3 | T3 |
| **T5** | Restyle `dash-users-profile.php` + `layouts/modal-new-password.php` | Medio | GLM-5.2 | T1 |
| **T6** | Registrar cluster en `.doc/orfanatos-pendientes.md` | Bajo | MiniMax-M3 | T2, T3 |
| **T7** | QA navegador (crear con avatar+rol incl. SuperAdmin, editar prefill+cambio rol/avatar, inactivar, reset password, perfil, buscar/paginar, dropdown nuevo) | Medio | Sonnet 5 | T4, T5, T6 |

Secuencial estricto T0→…→T7. Coordinador cierra manualmente workers atascados/desviados tras verificar el diff en disco.

## 9. Decisiones (confirmadas 2026-07-18)

1. **Borrado:** mantener **"Inactivar"** tal cual (soft-delete, SweetAlert2). Sin hard delete.
2. **Rol:** selector de **3 roles** (SuperAdministrador/Administrador/Usuario) en ambos drawers, dinámico desde `category`. Este plan agrega la fila SuperAdministrador (T0). "Set Admin/User" se **retira**.
3. **Perfil propio:** **incluido** (restyle, T5).
4. **RBAC/elevación:** fuera de alcance (plan de roles separado). Cualquier admin asigna cualquier rol por ahora.

## 10. Verificación (T7)

Smoke test manual en `http://localhost` con sesión inyectada (sin login real): listado (buscar/paginar + dropdown nuevo), crear (avatar dropzone en drawer + rol SuperAdmin persiste `category=3`), editar (prefill rol/avatar + cambios persisten, sin password), inactivar (SweetAlert2), reset password + gate `category==1`, perfil restyleado + cambio password, y que no queda navegación a add/edit huérfanas.
