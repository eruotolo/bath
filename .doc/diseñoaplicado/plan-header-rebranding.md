# Plan — Header rebranding (Blanco Servicios)

**Origen:** prototipo `rebranding/src/components/Header.tsx` (React + Tailwind), generado en Google AI Studio.
**Objetivo:** traducir el diseño y la UX de ese header al stack real del proyecto (PHP procedural + Bootstrap 4 + vanilla CSS), logrando un resultado **visualmente idéntico** al prototipo, **sin perder funcionalidad crítica** que el prototipo omite (logout, perfil, lock screen).
**Estado:** ✅ implementado (2026-07-13) — versión simplificada por decisión de Edgardo: **sin search, sin pill eco, sin notificaciones** (elimina Fase H4 y reduce H1/H2). Desviaciones sobre este plan:
- Campo de rol: `$_SESSION['category']` (int FK a tabla `category`: 1=Administrador, 2=Usuario), mapeado con array estático en `layouts/header.php`. No existía `$_SESSION['rol']`.
- Avatar fallback: `avatar-1.jpg` (no existe `default.png` en `uploads/users/`).
- Reconciliación de layout no prevista en el plan: el topbar viejo era `position: fixed` con `.page-content` compensando 70px. Se agregó en `header.css`: `margin-left: 288px` al header en desktop y override `#layout-wrapper .page-content { padding-top: 1.5rem }` (scopeado, no afecta páginas auth). En `sidebar.css` el sidebar desktop pasó de `top: 70px` a `top: 0` (altura completa, como el prototipo).
- Se omitió `ring: 2px` de `.hdr-avatar` (propiedad inválida, Tailwind-ism); el `outline` da el efecto.
- Feather quedó sin consumidores en el shell — `feather.min.js` sigue cargado por posibles refs en `archive/` (dependencia muerta).
**Depende de:** `plan-sidebar-rebranding.md` (comparte Lucide, fuentes y paleta indigo) — ya implementado.

---

## 0. Cómo usar este documento

Mismo formato y reglas que `plan-sidebar-rebranding.md` sección 0. No se repiten. Aplican las mismas reglas del proyecto (sin build tooling, `docker-compose restart php` tras cambios, no commitear sin pedido).

### 0.1 Orden de ejecución respecto al plan de sidebar

Este plan **asume que el plan de sidebar ya está implementado** (Fases S1–S5). Comparte:

- **Lucide icons** vendorizados en S1 (`assets/libs/lucide/`).
- **Fuentes Inter + JetBrains Mono** cargadas en S1 (`head-style.php`).
- **El botón hamburger** ya reconectado al sidebar en S4 (`sidebar.js` escucha `#vertical-menu-btn`).
- **La marca** ya vive en el sidebar (`sb-brand`) → el header **no repite logo**, coincide con el prototipo.

Si se ejecuta antes que el sidebar, igual funciona (Lucide y fuentes son la única dependencia dura), pero el hamburger no abrirá nada hasta que el sidebar exista.

---

## 1. Decisiones de diseño

### 1.1 Paleta — INDIGO (idéntico al sidebar)

Mismos tokens que `sidebar.css`. Se duplican scopeados bajo `.hdr-header` para mantener el header auto-contenido y reversible (mismo criterio que el sidebar). Cuando la app migre toda a indigo, estos tokens convergen a un `:root` compartido.

| Token | Valor | Uso |
|---|---|---|
| `--hdr-primary` | `#4f46e5` | dot pulsante, focus ring del search, categoría del usuario |
| `--hdr-primary-light` | `#eef2ff` | pill eco (`indigo-50`), fondoSsutiles |
| `--hdr-primary-mid` | `#6366f1` | ring del avatar (`indigo-500/10`) |
| `--hdr-bg` | `#ffffff` | fondo del header |
| `--hdr-border` | `#f1f5f9` | borde inferior (`slate-100`) |
| `--hdr-text` | `#0f172a` | título (`slate-900`) |
| `--hdr-text-muted` | `#64748b` | subtítulo, iconos (`slate-500`) |
| `--hdr-text-faint` | `#94a3b8` | placeholder search (`slate-400`) |
| `--hdr-danger` | `#f43f5e` | badge del bell (`rose-500`) |
| `--hdr-amber` | `#f59e0b` | dot de notificación warning |
| `--hdr-blue` | `#3b82f6` | dot de notificación info |

### 1.2 Tipografía

Misma carga que el sidebar (Inter + JetBrains Mono, cargadas en S1.3 del plan de sidebar):

- **Inter** para título, nombre de usuario, items de dropdown.
- **JetBrains Mono** para subtítulo (`Operaciones Chiloé • Servidor Activo`), categoría del usuario, timestamps de notificaciones.

### 1.3 Desviaciones intencionales del prototipo

El prototipo es un mockup y **omite acciones críticas de cuenta**. Este plan las restaura por seguridad funcional:

| Elemento | Prototipo | Este plan | Motivo |
|---|---|---|---|
| **Logout** | No existe | ✅ En dropdown del avatar | Sin logout el usuario no puede cerrar sesión |
| **Lock screen** | No existe | ✅ En dropdown del avatar | Función existente en `auth-lock-screen.php` |
| **Acceso a perfil** | No existe | ✅ En dropdown del avatar | Función existente en `dash-users-profile.php` |
| **Dropdown de notificaciones** | React state + mock data | Bootstrap dropdown + mock data | Misma apariencia, mecanismo nativo del template |
| **Search** | Filtra la vista activa (React state) | **Decorativo** (sin backend) | No existe search global en el PHP — cada DataTable tiene el suyo. Ver Fase H4. |

Todo lo demás es **idéntico al prototipo**: título dinámico, subtítulo de estado, pill eco, bell con badge, avatar con ring indigo, separador vertical.

### 1.4 Scope

Se reemplaza **únicamente el `#page-topbar`** en `vertical-menu.php` (líneas 1–72). El sidebar izquierdo se gestionó en el plan de sidebar. El footer (`footer.php`) queda intacto.

### 1.5 Categoría del usuario — verificación previa

El prototipo muestra la categoría del usuario (`Administrador`/`Supervisor`/`Operador`) bajo el nombre. El topbar actual solo muestra `$_SESSION['name']` + `$_SESSION['lastname']`.

**Antes de Fase H2**, verificar con:
```bash
grep -rn "SESSION\['" app/public/layouts/vertical-menu.php app/public/controller/login*.php app/public/layouts/session.php
```

Si existe un campo de rol en `$_SESSION` (ej. `$_SESSION['rol']`, `$_SESSION['category']`, `$_SESSION['user_type']`), usarlo. Si no existe, **omitir la línea de categoría** en el markup (no inventar datos). Documentar el nombre real del campo en este plan antes de implementar.

---

## 2. Anatomía del header (referencia rápida)

Estructura traducida de `Header.tsx:39-145`:

```
<header class="hdr-header">
  <div class="hdr-left">
    <button class="hdr-hamburger" id="vertical-menu-btn">  ← mobile only, mismo ID que sidebar.js escucha
      <i data-lucide="menu"></i>
    </button>
    <div class="hdr-title-wrap">
      <h1 class="hdr-title">{título dinámico}</h1>
      <div class="hdr-subtitle">
        <span class="hdr-status-dot"></span>
        <span>Operaciones Chiloé • Servidor Activo</span>
      </div>
    </div>
  </div>

  <div class="hdr-actions">
    <div class="hdr-search">                                 ← md+ only
      <i data-lucide="search"></i>
      <input placeholder="Buscar en el módulo actual...">
    </div>

    <div class="hdr-eco-pill">                               ← lg+ only
      <i data-lucide="sparkles"></i>
      <span>98% Eficiencia Ecológica</span>
    </div>

    <div class="dropdown">
      <button class="hdr-bell" data-bs-toggle="dropdown">
        <i data-lucide="bell"></i>
        <span class="hdr-bell-badge"></span>
      </button>
      <ul class="dropdown-menu hdr-notifications">           ← mock data
        ...
      </ul>
    </div>

    <div class="dropdown hdr-user">
      <button class="hdr-user-trigger" data-bs-toggle="dropdown">
        <span class="hdr-user-name">{name}</span>
        <span class="hdr-user-cat">{category}</span>
        <img class="hdr-avatar" src="...">
      </button>
      <ul class="dropdown-menu dropdown-menu-end hdr-user-menu">
        <li><a href="dash-users-profile.php">Perfil</a></li>
        <li><a href="auth-lock-screen.php">Bloquear</a></li>
        <li><hr></li>
        <li><a href="logout.php" class="text-danger">Cerrar sesión</a></li>
      </ul>
    </div>
  </div>
</header>
```

---

## 3. Fases de implementación

### Fase H1 — CSS del header (`header.css`)

**Archivos a crear:** `app/public/assets/css/header.css`.
**Archivos a tocar:** `app/public/layouts/head-style.php` (agregar el `<link>`).

Mismo criterio que `sidebar.css`: archivo aislado, reversible quitando un `<link>`.

**H1.1 — Contenido completo de `header.css`**

```css
/* ===== Header rebranding — vanilla CSS traducido de rebranding/src/components/Header.tsx ===== */

.hdr-header {
    --hdr-primary: #4f46e5;
    --hdr-primary-light: #eef2ff;
    --hdr-primary-mid: #6366f1;
    --hdr-bg: #ffffff;
    --hdr-border: #f1f5f9;
    --hdr-text: #0f172a;
    --hdr-text-muted: #64748b;
    --hdr-text-faint: #94a3b8;
    --hdr-danger: #f43f5e;
    --hdr-amber: #f59e0b;
    --hdr-blue: #3b82f6;

    height: 80px;
    border-bottom: 1px solid var(--hdr-border);
    background: var(--hdr-bg);
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 1030;
    box-shadow: 0 1px 2px 0 rgba(241, 245, 249, 0.5);
    font-family: 'Inter', sans-serif;
}

/* Left section */
.hdr-left { display: flex; align-items: center; gap: 16px; min-width: 0; }

.hdr-hamburger {
    display: inline-flex;
    padding: 8px;
    border-radius: 12px;
    border: none;
    background: transparent;
    color: var(--hdr-text-muted);
    cursor: pointer;
    transition: all 0.15s;
}
.hdr-hamburger:hover { color: var(--hdr-text); background: #f8fafc; }
.hdr-hamburger svg { width: 20px; height: 20px; }
@media (min-width: 992px) { .hdr-hamburger { display: none; } }

.hdr-title { margin: 0; font-weight: 700; font-size: 20px; letter-spacing: -0.025em; color: var(--hdr-text); }
.hdr-subtitle { display: none; align-items: center; gap: 8px; margin-top: 2px; }
@media (min-width: 640px) { .hdr-subtitle { display: flex; } }
.hdr-status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--hdr-primary-mid); animation: hdr-pulse 2s infinite; }
.hdr-subtitle span:last-child {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    font-weight: 600;
    color: var(--hdr-text-muted);
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
@keyframes hdr-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

/* Right section */
.hdr-actions { display: flex; align-items: center; gap: 16px; }

/* Search */
.hdr-search { display: none; position: relative; width: 256px; }
@media (min-width: 768px) { .hdr-search { display: block; } }
@media (min-width: 992px) { .hdr-search { width: 320px; } }
.hdr-search > svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px; height: 16px;
    color: var(--hdr-text-faint);
    pointer-events: none;
}
.hdr-search-input {
    width: 100%;
    padding: 8px 16px 8px 40px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: rgba(248, 250, 252, 0.5);
    color: var(--hdr-text);
    transition: all 0.15s;
}
.hdr-search-input::placeholder { color: var(--hdr-text-faint); }
.hdr-search-input:focus {
    outline: none;
    border-color: var(--hdr-primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

/* Eco pill */
.hdr-eco-pill {
    display: none;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 9999px;
    background: var(--hdr-primary-light);
    border: 1px solid #c7d2fe;
    color: var(--hdr-primary);
    font-weight: 500;
    font-size: 11px;
}
@media (min-width: 992px) { .hdr-eco-pill { display: inline-flex; } }
.hdr-eco-pill svg { width: 14px; height: 14px; }

/* Bell */
.hdr-bell {
    position: relative;
    padding: 10px;
    border-radius: 12px;
    border: 1px solid var(--hdr-border);
    background: transparent;
    color: var(--hdr-text-muted);
    cursor: pointer;
    transition: all 0.15s;
}
.hdr-bell:hover { color: var(--hdr-text); background: #f8fafc; }
.hdr-bell svg { width: 16px; height: 16px; display: block; }
.hdr-bell-badge {
    position: absolute;
    top: 6px; right: 6px;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--hdr-danger);
    border: 2px solid #fff;
}

/* Notifications dropdown (Bootstrap .dropdown-menu override) */
.hdr-notifications {
    width: 320px;
    border-radius: 16px;
    border: 1px solid var(--hdr-border);
    box-shadow: 0 20px 25px -5px rgba(226, 232, 240, 0.5);
    padding: 0;
    overflow: hidden;
}
.hdr-notifications-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #f8fafc;
    border-bottom: 1px solid var(--hdr-border);
}
.hdr-notifications-header span:first-child { font-size: 12px; font-weight: 600; color: #334155; }
.hdr-notifications-count {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    color: var(--hdr-primary);
    background: var(--hdr-primary-light);
    padding: 2px 8px;
    border-radius: 9999px;
    text-transform: uppercase;
}
.hdr-notification-list { max-height: 320px; overflow-y: auto; }
.hdr-notification {
    display: flex;
    gap: 12px;
    padding: 14px;
    border-bottom: 1px solid #f8fafc;
    transition: background 0.15s;
}
.hdr-notification:hover { background: #f8fafc; }
.hdr-notification-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
.hdr-notification.is-warning .hdr-notification-dot { background: var(--hdr-amber); }
.hdr-notification.is-success .hdr-notification-dot { background: var(--hdr-primary); }
.hdr-notification.is-info .hdr-notification-dot { background: var(--hdr-blue); }
.hdr-notification-text { font-size: 12px; color: #475569; line-height: 1.5; }
.hdr-notification-time { font-family: 'JetBrains Mono', monospace; font-size: 9px; color: var(--hdr-text-faint); display: block; margin-top: 4px; }
.hdr-notifications-footer { padding: 8px 16px; background: #f8fafc; text-align: center; }
.hdr-notifications-footer button {
    border: none; background: transparent;
    font-size: 11px; font-weight: 500; color: var(--hdr-primary);
    cursor: pointer;
}
.hdr-notifications-footer button:hover { color: var(--hdr-primary-mid); }

/* User */
.hdr-user { padding-left: 8px; border-left: 1px solid var(--hdr-border); }
.hdr-user-trigger {
    display: flex;
    align-items: center;
    gap: 12px;
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 0;
}
.hdr-user-info { text-align: right; display: none; }
@media (min-width: 640px) { .hdr-user-info { display: block; } }
.hdr-user-name { display: block; font-size: 12px; font-weight: 700; color: #1e293b; line-height: 1; }
.hdr-user-cat {
    display: block;
    margin-top: 2px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    color: var(--hdr-primary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    line-height: 1;
}
.hdr-avatar {
    width: 40px; height: 40px;
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 4px 6px -1px #f1f5f9;
    ring: 2px;
    outline: 2px solid rgba(99, 102, 241, 0.1);
    outline-offset: -2px;
}

/* User dropdown (Bootstrap override) */
.hdr-user-menu {
    border-radius: 16px;
    border: 1px solid var(--hdr-border);
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    padding: 8px;
}
.hdr-user-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    color: #334155;
}
.hdr-user-menu .dropdown-item svg { width: 16px; height: 16px; }
.hdr-user-menu .dropdown-item:hover { background: #f8fafc; color: var(--hdr-text); }
.hdr-user-menu .dropdown-item.text-danger { color: var(--hdr-danger); }
.hdr-user-menu .dropdown-item.text-danger:hover { background: rgba(244, 63, 94, 0.1); color: var(--hdr-danger); }
.hdr-user-menu .dropdown-divider { margin: 4px 0; border-color: var(--hdr-border); }
```

**H1.2 — Cargar `header.css` en `head-style.php`**

Agregar después del `<link>` de `sidebar.css` ( cargado en S2.2 del plan de sidebar), al final del archivo:

```html
<!-- header rebranding -->
<link href="assets/css/header.css" rel="stylesheet" type="text/css">
```

**Checklist de aceptación:**
- [ ] `assets/css/header.css` existe y carga con 200 en DevTools → Network
- [ ] No hay regresiones visuales (las clases son nuevas, no matchean nada todavía)
- [ ] `docker-compose restart php` + recargar `http://localhost` sin errores

---

### Fase H2 — Markup PHP del header (`layouts/header.php`)

**Archivos a crear:** `app/public/layouts/header.php`.

**H2.0 — Verificación previa (ver sección 1.5)**

Confirmar el nombre del campo de rol en `$_SESSION`. Si no existe, dejar la línea de categoría comentada en el markup (no borrar — solo comentar para activarla cuando el backend exponga el rol).

**H2.1 — Contenido de `layouts/header.php`**

```php
<?php
$current_script = basename($_SERVER['PHP_SELF']);

// Título dinámico por vista — mismo mapeo que $nav_sections del sidebar
$page_titles = [
    'index.php' => 'Tablero Analítico',
    'dash-customers.php' => 'Directorio de Clientes',
    'dash-customers-add.php' => 'Directorio de Clientes',
    'dash-customers-item.php' => 'Directorio de Clientes',
    'dash-bathrooms.php' => 'Inventario de Baños Químicos',
    'dash-bathrooms-add.php' => 'Inventario de Baños Químicos',
    'dash-bathrooms-edit.php' => 'Inventario de Baños Químicos',
    'dash-bathrooms-contracts.php' => 'Inventario de Baños Químicos',
    'dash-bathrooms-contracts-status.php' => 'Inventario de Baños Químicos',
    'dash-contracts.php' => 'Gestión de Obras & Contratos',
    'dash-contracts-add.php' => 'Gestión de Obras & Contratos',
    'dash-contracts-edit.php' => 'Gestión de Obras & Contratos',
    'dash-contracts-item.php' => 'Gestión de Obras & Contratos',
    'dash-services.php' => 'Servicios en Terreno & Ruta',
    'dash-services-add.php' => 'Servicios en Terreno & Ruta',
    'dash-services-edit.php' => 'Servicios en Terreno & Ruta',
    'dash-services-bath.php' => 'Servicios en Terreno & Ruta',
    'dash-services-print.php' => 'Servicios en Terreno & Ruta',
    'dash-invoices-list.php' => 'Control de Facturación',
    'dash-invoices-add.php' => 'Control de Facturación',
    'dash-invoices-edit.php' => 'Control de Facturación',
    'dash-invoices-upload.php' => 'Control de Facturación',
    'dash-invoices-upload-preview.php' => 'Control de Facturación',
    'dash-invoices-upload-result.php' => 'Control de Facturación',
    'dash-invoices-detail.php' => 'Control de Facturación',
    'dash-certificates.php' => 'Certificados de Disposición m³',
    'dash-certificates-add.php' => 'Certificados de Disposición m³',
    'dash-certificates-item.php' => 'Certificados de Disposición m³',
    'dash-users-list.php' => 'Personal de Operaciones',
    'dash-users-add.php' => 'Personal de Operaciones',
    'dash-users-edit.php' => 'Personal de Operaciones',
    'dash-users-profile.php' => 'Personal de Operaciones',
];

$page_title = isset($page_titles[$current_script]) ? $page_titles[$current_script] : 'Blanco Servicios';

// TODO: verificar nombre real del campo de rol en $_SESSION (ver plan sección 1.5)
$user_category = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
?>

<header class="hdr-header">
    <div class="hdr-left">
        <button type="button" class="hdr-hamburger" id="vertical-menu-btn" aria-label="Abrir menú">
            <i data-lucide="menu"></i>
        </button>
        <div class="hdr-title-wrap">
            <h1 class="hdr-title"><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h1>
            <div class="hdr-subtitle">
                <span class="hdr-status-dot"></span>
                <span>Operaciones Chiloé • Servidor Activo</span>
            </div>
        </div>
    </div>

    <div class="hdr-actions">
        <!-- Search (decorativo — sin backend global, ver Fase H4) -->
        <div class="hdr-search">
            <i data-lucide="search"></i>
            <input type="text" class="hdr-search-input" placeholder="Buscar en el módulo actual..." aria-label="Buscar">
        </div>

        <!-- Eco pill (decorativo) -->
        <div class="hdr-eco-pill">
            <i data-lucide="sparkles"></i>
            <span>98% Eficiencia Ecológica</span>
        </div>

        <!-- Notificaciones (mock data) -->
        <div class="dropdown">
            <button type="button" class="hdr-bell" data-bs-toggle="dropdown" aria-expanded="false">
                <i data-lucide="bell"></i>
                <span class="hdr-bell-badge"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end hdr-notifications">
                <li class="hdr-notifications-header">
                    <span>Notificaciones</span>
                    <span class="hdr-notifications-count">3 Nuevas</span>
                </li>
                <li>
                    <div class="hdr-notification-list">
                        <div class="hdr-notification is-info">
                            <span class="hdr-notification-dot"></span>
                            <div>
                                <span class="hdr-notification-text">Baño AT055 asignado a Curaco de Vélez</span>
                                <span class="hdr-notification-time">Hace 10 min</span>
                            </div>
                        </div>
                        <div class="hdr-notification is-warning">
                            <span class="hdr-notification-dot"></span>
                            <div>
                                <span class="hdr-notification-text">Factura #1893 está vencida</span>
                                <span class="hdr-notification-time">Hace 1 hora</span>
                            </div>
                        </div>
                        <div class="hdr-notification is-success">
                            <span class="hdr-notification-dot"></span>
                            <div>
                                <span class="hdr-notification-text">Certificado CRT-06072026A3 firmado digitalmente</span>
                                <span class="hdr-notification-time">Hace 3 horas</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="hdr-notifications-footer">
                    <button type="button">Marcar todas como leídas</button>
                </li>
            </ul>
        </div>

        <!-- User profile + dropdown (acciones críticas: perfil, bloquear, logout) -->
        <div class="dropdown hdr-user">
            <button type="button" class="hdr-user-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="hdr-user-info">
                    <span class="hdr-user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['lastname'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($user_category): ?>
                        <span class="hdr-user-cat"><?php echo htmlspecialchars($user_category, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </span>
                <img class="hdr-avatar" src="uploads/users/<?php echo htmlspecialchars($_SESSION['image'] ?? 'default.png', ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil">
            </button>
            <ul class="dropdown-menu dropdown-menu-end hdr-user-menu">
                <li><a class="dropdown-item" href="dash-users-profile.php"><i data-lucide="user"></i> Perfil</a></li>
                <li><a class="dropdown-item" href="auth-lock-screen.php"><i data-lucide="lock"></i> Bloquear</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i data-lucide="log-out"></i> Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</header>
```

**Notas del markup:**
- El `id="vertical-menu-btn"` se mantiene (mismo que el topbar actual y que `sidebar.js` escucha) — **no duplicar** el `#hamburger-btn` del prototipo.
- Los iconos usan `data-lucide` (cargados por `sidebar.js` que ya llama `lucide.createIcons()` globalmente — no requiere JS nuevo en el header).
- Los dropdowns usan `data-bs-toggle="dropdown"` de Bootstrap (ya cargado en `vendor-scripts.php:3`) — toggle/close nativo, sin JS propio.
- Toda salida de `$_SESSION` se escapa con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- La imagen del avatar reusa la ruta existente `uploads/users/` (no cambiar).

**Checklist de aceptación:**
- [ ] El archivo existe y `php -l` no reporta errores
- [ ] Al incluirlo (Fase H3), el título cambia según la página navegada
- [ ] Dropdown del avatar abre/cierra con click (Bootstrap)
- [ ] "Cerrar sesión" navega a `logout.php` y cierra sesión efectivamente
- [ ] "Perfil" navega a `dash-users-profile.php`
- [ ] "Bloquear" navega a `auth-lock-screen.php`
- [ ] Dropdown de notificaciones abre/cierra con click (Bootstrap)
- [ ] Si `$_SESSION` no tiene rol, la línea de categoría no se renderiza (no deja un hueco vacío)

---

### Fase H3 — Integración en `vertical-menu.php`

**Archivos a tocar:** `app/public/layouts/vertical-menu.php`.

**H3.1 — Reemplazar el bloque `#page-topbar`**

El archivo `vertical-menu.php` (tras ejecutar el plan de sidebar) tiene esta estructura:

```php
<header id="page-topbar">
    <div class="navbar-header">
        ... (líneas 1-72, el topbar legacy) ...
    </div>
</header>

<?php include 'layouts/sidebar.php'; ?>
```

Reemplazar **todo el bloque `<header id="page-topbar">...</header>`** (líneas 1-72) por:

```php
<?php include 'layouts/header.php'; ?>
```

Quedando:

```php
<?php include 'layouts/header.php'; ?>

<?php include 'layouts/sidebar.php'; ?>
```

**H3.2 — Eliminar dependencias del topbar viejo**

El topbar viejo usaba:
- `.navbar-brand-box` → ya no se usa (la marca está en el sidebar).
- `data-feather="search"` / `data-feather="home"` / `data-feather="users"` → los 3 usos de Feather quedaban en el topbar. Tras este cambio, **Feather no tiene consumidores**. No eliminar `feather.min.js` todavía (puede haber referencias en `archive/`), pero documentar que queda como dependencia muerta.

**Checklist de aceptación:**
- [ ] `docker-compose restart php` + `http://localhost` → el header nuevo se renderiza arriba del sidebar nuevo
- [ ] El header viejo (`#page-topbar` con `.navbar-brand-box`) ya **no aparece**
- [ ] En desktop: header (80px) + sidebar (288px) + contenido llenan el viewport sin overlap
- [ ] En mobile (<992px): header visible, hamburger abre el sidebar (off-canvas), backdrop funciona
- [ ] El título del header cambia al navegar entre vistas (Tablero → Clientes → Baños...)
- [ ] Logout funciona → redirige al login
- [ ] No hay errores 500 ni warnings en `docker-compose logs php`
- [ ] `grep -rn "page-topbar\|navbar-brand-box" app/public/layouts/` devuelve 0 resultados

---

### Fase H4 — Search: decidir alcance (opcional)

El search del prototipo filtra la vista activa vía React state global. En el PHP real, cada DataTable ya tiene su propio input de búsqueda (DataTables.net `f` filtering). No existe un bus de búsqueda cross-vista.

**Estado actual tras Fase H3:** el input `.hdr-search-input` es decorativo — no hace nada al escribir.

**Opciones (a decidir con Edgardo, no implementar sin confirmación):**

| Opción | Esfuerzo | Resultado |
|---|---|---|
| **A — Dejar decorativo** | 0 | Igual al prototipo mock (mentira piadosa) |
| **B — Focus al search del DataTable** | Bajo | Al escribir en el header, redirige el foco/valor al `input[type=search]` del DataTable de la vista actual. No es cross-vista pero da feedback real |
| **C — Búsqueda global real** | Muy alto | Requiere un endpoint nuevo que busque en todas las tablas + UI de resultados. Proyecto aparte |

Si se elige **B**, agregar al final de `sidebar.js` (o un `header.js` nuevo):

```javascript
var hdrSearch = document.querySelector('.hdr-search-input');
if (hdrSearch) {
    hdrSearch.addEventListener('input', function () {
        var dtSearch = document.querySelector('.dataTables_filter input');
        if (dtSearch) {
            dtSearch.value = hdrSearch.value;
            dtSearch.dispatchEvent(new Event('input'));
        }
    });
}
```

**Checklist de aceptación (si se elige B):**
- [ ] Al escribir en el search del header, el DataTable de la vista actual se filtra en tiempo real
- [ ] Si la vista no tiene DataTable, escribir no rompe ni muestra error
- [ ] El search del header se limpia al cambiar de vista

---

## 4. Verificación de integración final

Cuando las fases H1–H3 están implementadas (más el plan de sidebar S1–S5):

1. **Shell completo:** header indigo + sidebar indigo forman la nueva identidad visual — comparar lado a lado con el prototipo corriendo (`cd rebranding && npm run dev`).
2. **Título dinámico:** navegar por las 8 secciones y verificar que el título del header cambia correctamente.
3. **Acciones de cuenta:** logout, lock y perfil funcionan (restauradas del topbar viejo, no perdidas).
4. **Responsive:** en mobile, header con hamburger → sidebar off-canvas → backdrop → close. En desktop, header sticky + sidebar fijo.
5. **Dropdowns:** notificaciones y user menu abren/cierran con Bootstrap (click fuera cierra, Escape cierra).
6. **Iconografía:** todos los iconos del header renderizan como SVG Lucide (menu, search, sparkles, bell, user, lock, log-out).
7. **Sin residuo del topbar viejo:** `grep -rn "page-topbar\|navbar-brand-box\|navbar-header" app/public/layouts/` devuelve 0.
8. **Sin regresión:** DataTables, modales, forms y controllers siguen funcionando.

---

## 5. Fuera de alcance de este plan

- **Búsqueda global real (Fase H4 opción C):** necesita backend nuevo — endpoint que busque en clientes, baños, contratos, facturas, etc. Es un proyecto aparte.
- **Notificaciones reales:** el dropdown es mock data hardcodeada. Conectarlo a un backend real requiere tabla `notificaciones`, triggers en controllers y polling. Plan futuro.
- **Pill eco real:** "98% Eficiencia Ecológica" es un número fijo. Definir qué mide y calcularlo es decisión de negocio, no de frontend.
- **Rediseño del footer** (`footer.php`): el footer actual ("© Blanco Servicios / Design by Crow Advance") no está en el prototipo. Queda intacto.
- **Dark mode:** el prototipo no lo tiene.
- **Eliminación de Feather Icons:** tras este plan, Feather queda sin consumidores en el shell, pero `archive/` puede referenciarlo. No se elimina hasta auditar `archive/`.
- **Internacionalización:** los títulos y labels del header van en español hardcodeado (idéntico al prototipo). Si se necesita i18n, agregar después.
- **Categoría del usuario desde backend:** si `$_SESSION` no expone el rol, hacer que el login lo cargue es cambio de `session.php`/`login.php` — fuera de scope visual.

---

## 6. Referencias

- **Prototipo fuente:** `rebranding/src/components/Header.tsx` (147 líneas, React + Tailwind).
- **Topbar actual a reemplazar:** `app/public/layouts/vertical-menu.php:1-72` (bloque `#page-topbar`).
- **Plan de sidebar (dependencia):** `.doc/plan-sidebar-rebranding.md` — comparte Lucide, fuentes, paleta y el botón hamburger.
- **Sistema visual vigente (teal):** `.doc/Viejos/plan-diseno-claude.md` — referencia de convenciones, NO de paleta (este plan la sobreescribe para el header).
- **Dropdown de usuario actual (a preservar):** `vertical-menu.php:53-68` — acciones Perfil/Lock/Logout que este plan mantiene en el nuevo dropdown.
