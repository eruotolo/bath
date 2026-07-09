# Checklist — Plan DDD / Entidad: Users

**Origen:** `.doc/plan-migracion-ddd-drycode.md` (sección 6, entidad #3 — "cuidado extra por ser autenticación")
**Estado:** ✅ implementado y verificado (2026-07-09)

---

## ⚠️ Hallazgos de seguridad — decisión del usuario antes de implementar

Antes de tocar código se relevaron 3 vulnerabilidades reales en el dominio Users, más allá de la SQL injection sistemática ya conocida. Se consultó a Edgardo y las 3 correcciones fueron aprobadas explícitamente:

1. **IDOR / account takeover en `user-profile-newpassword.php`** — el modal "Cambiar Password" mandaba el `id` del usuario a modificar en un campo POST oculto, precargado con `$_SESSION['id']` pero **nunca validado contra la sesión real**. Cualquier usuario logueado podía cambiar el password de cualquier otro (incluido un admin) mandando un `id` distinto. **Corregido**: `ChangeOwnPassword::handle()` ahora compara `$sessionUserId` contra el `id` recibido y rechaza el cambio si no coinciden.
2. **Password real de producción reusado como password de aplicación en `user-inactive.php`** — al "Inactivar" un usuario, su password se pisaba con el string literal `'Guns026772'`, que es la misma contraseña real de la base de datos de producción (ya documentada como deuda crítica en `.doc/plan-migracion-php-8.5.md:58`). Como `auth-login.php` nunca chequea la columna `state`, un usuario "inactivado" podía seguir logueándose con esa contraseña conocida. **Corregido**: `DeactivateUser::handle()` genera un password aleatorio (`random_bytes(16)`) que se hashea y se descarta — nadie necesita conocerlo, el objetivo es bloquear la cuenta, no comunicar una contraseña.
3. **Otro password hardcodeado en `user-default-pass.php`** — resetear a `'JuanSanchez_2024'`, mismo patrón de riesgo (password fijo trackeado en git). **Corregido**: `ResetUserPassword::handle()` genera un password aleatorio de 16 hex chars y lo **devuelve** (a diferencia del caso anterior, acá sí hace falta comunicárselo al usuario) — se muestra al admin vía el mismo mecanismo de feedback ya usado en toda la app (`?status=success&msg=...`, leído y limpiado por `feedback.js`).

**No corregido, fuera del alcance acordado:**
- `auth-login.php` no verifica `state` antes de autenticar. No se tocó porque no fue parte de lo consultado — el fix del password aleatorio en `DeactivateUser` ya vuelve la cuenta inaccesible en la práctica (nadie conoce el password nuevo), pero valdría la pena agregar el chequeo explícito en algún momento.
- Upload de imagen de perfil sin validar tipo/extensión (`user-new.php`, `user-update.php`) — riesgo menor, no se tocó (no fue parte de las 3 preguntas).

## Alcance

6 controllers + 3 vistas (de las 4 `dash-users-*.php`; `dash-users-add.php` no tenía SQL, solo formulario). `auth-login.php`, `logout.php` y `layouts/session.php` **no se tocaron** — ya usaban prepared statements + `password_verify()`, no son parte del CRUD de administración de usuarios sino del flujo de login en sí.

**Controllers:**

| Archivo | Operación | Estado antes |
|---|---|---|
| `controller/user-new.php` | INSERT | SQL injection (concatenación directa) |
| `controller/user-update.php` | UPDATE (parcial, con lógica "vacío → NULL") | SQL injection |
| `controller/user-inactive.php` | UPDATE password + state=0 | SQL injection + password hardcodeado (hallazgo #2) |
| `controller/user-setadmin.php` | UPDATE category (toggle 1/2) | SQL injection |
| `controller/user-default-pass.php` | UPDATE password | SQL injection + password hardcodeado (hallazgo #3) |
| `controller/user-profile-newpassword.php` | UPDATE password (self-service) | SQL injection + IDOR (hallazgo #1) |

**Vistas:**

- `dash-users-list.php` — reemplaza el `SELECT * JOIN category` inline por `ListActiveUsers`, agrega `htmlspecialchars()` en 5 columnas que no lo tenían
- `dash-users-edit.php` — tenía SQL injection vía `$_GET['id_User']` **y** XSS (4 campos sin escapar); se corrigieron ambos con `FindUser`
- `dash-users-profile.php` — sin SQL (lee de `$_SESSION`), se agregó `htmlspecialchars()` a los 4 valores de sesión que se imprimían crudos (defensa en profundidad — esos valores vienen de la DB en el login, no de input directo del request actual)

## Archivos nuevos

```
app/src/
├── Domain/User/
│   ├── User.php                                   — Entity (id, useremail, username, passwordHash, token, name, lastname, image, category, state)
│   └── UserRepositoryInterface.php
├── Application/User/
│   ├── CreateUser.php                             — hashea password, genera token, inserta
│   ├── UpdateUser.php                             — preserva el comportamiento "campo vacío → NULL" del original
│   ├── DeactivateUser.php                         — fix #2: password aleatorio en vez de hardcodeado
│   ├── ToggleUserAdmin.php                        — toggle category 1/2
│   ├── ResetUserPassword.php                      — fix #3: password aleatorio, lo devuelve para mostrarlo
│   ├── ChangeOwnPassword.php                      — fix #1: valida sessionUserId === requestedId
│   ├── FindUser.php                               — usado por dash-users-edit.php
│   └── ListActiveUsers.php                        — usado por dash-users-list.php
└── Infrastructure/Persistence/
    └── MysqliUserRepository.php                   — único archivo con SQL de users, 100% prepared statements
```

## Decisiones de diseño

1. **`UpdateUser` preserva el bug de "campo vacío limpia la columna"** del código original (si `useremail`/`username`/`name`/`lastname` llegan vacíos, se guardan como `NULL`). Para `useremail`/`username` (columnas `NOT NULL`) esto ya fallaba antes (silenciosamente, con `mysqli_query` sin chequear el resultado); ahora falla igual pero de forma explícita vía `mysqli_sql_exception` capturada en el controller. No se pidió corregir este comportamiento, así que se mantuvo igual.
2. **El password temporal de `ResetUserPassword` viaja en la query string** del redirect (`?status=success&msg=...`), mismo mecanismo que usa toda la app para mostrar feedback (`feedback.js` lo lee y limpia la URL al toque). Es el único lugar de la app donde un dato sensible pasa por ahí — quedó documentado como matiz a tener en cuenta, no se construyó un mecanismo de flash-session nuevo solo para este caso (hubiera sido una abstracción extra no usada en ningún otro lado del código).
3. **No se creó `ContractRepository` ni nada cruzado** — a diferencia de Certificates/Bathrooms, Users no necesitó leer de otras entidades salvo el join a `category` (tabla catálogo chica, sin entidad propia en el plan).

## Verificación

- [x] `php -l` sobre los 6 controllers + 3 vistas tocadas + 10 archivos nuevos — sin errores
- [x] Listado (`dash-users-list.php`): 3 usuarios reales sin tocar, se creó un usuario de prueba (`testdddusr`) para no arriesgar las cuentas reales (incluida la sesión logueada de Edgardo)
- [x] Crear usuario de prueba — categoría "Usuario" (2) por defecto, aparece en el listado
- [x] Editar (apellido) — se refleja en el listado
- [x] Set Admin/User — toggle 2→1 confirmado visualmente
- [x] Password Default — confirmado en DB que el hash cambió a un valor bcrypt válido (60 chars, `$2y$`) y que `state` no se tocó
- [x] Inactivar — confirmado en DB que el hash cambió (distinto del anterior) y `state` pasó a 0; desaparece del listado (`WHERE state = 1`)
- [x] **IDOR (fix #1), caso negativo**: se invocó `ChangeOwnPassword::handle(sessionUserId: eruotolo, requestedId: jsanchez, ...)` directamente (sin pasar por HTTP, para no exponer ningún hash real en el transcript) — devolvió `false` y el password de `jsanchez` quedó sin cambios
- [x] **IDOR (fix #1), caso positivo**: se invocó `ChangeOwnPassword::handle(sessionUserId: testdddusr, requestedId: testdddusr, ...)` — devolvió `true` y el hash cambió correctamente
- [x] `dash-users-profile.php` carga bien con el `htmlspecialchars()` agregado
- [x] `docker-compose logs php` sin errores/warnings nuevos

**No verificado por precaución**: el flujo completo de "Cambiar Password" desde el modal real de `dash-users-profile.php` con la sesión de Edgardo — hacerlo hubiera cambiado el password real de producción/testing de la cuenta con la que estaba logueado, con riesgo de bloqueo. Se verificó la misma lógica (`ChangeOwnPassword`) de forma segura invocándola directamente vía script, ver arriba.

**Nota operativa**: durante esta migración el stack de Docker se detuvo solo (los 4 contenedores del proyecto salieron con código 0 simultáneamente, causa no identificada — no fue un comando ejecutado en esta sesión). Se relevantó con `docker-compose up -d` sin pérdida de datos ni de la sesión de navegador.

## Pendiente / fuera de alcance

- Customers, Contracts, Services, Invoices — siguen en el orden del plan.
- `auth-login.php` no verifica `state` — no se tocó, ver nota en hallazgos de seguridad arriba.
- Validación de tipo/extensión en upload de imagen de perfil — no se tocó.
- El usuario de prueba `testdddusr` quedó en la tabla como inactivo (`state = 0`) — no hay flujo de "eliminar usuario" en la app (solo "Inactivar"), así que no se puede limpiar sin acceso directo a la DB. No afecta el listado (solo muestra `state = 1`).

---

## Reglas respetadas (de `CLAUDE.md`)

- SQL 100% prepared statements — elimina 6 SQL injections activas
- `htmlspecialchars()` agregado en las vistas reescritas
- Antes de modificar código de autenticación, se relevó a fondo y se consultó al usuario en vez de decidir unilateralmente sobre los 2 hallazgos que cambian comportamiento (passwords hardcodeados) y el que corrige una falla de autorización (IDOR)
- No se commiteó nada sin pedido explícito
