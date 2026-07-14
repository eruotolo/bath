# Plan — Sidebar rebranding (Blanco Servicios)

**Origen:** prototipo `rebranding/src/components/Sidebar.tsx` (React + Tailwind), generado en Google AI Studio.
**Objetivo:** traducir el diseño y la UX de ese sidebar al stack real del proyecto (PHP procedural + Bootstrap 4 + vanilla CSS), logrando un resultado **visualmente idéntico** al prototipo.
**Estado:** 📋 plan — nada implementado todavía.
**Audiencia:** cualquier IA o desarrollador que vaya a ejecutar una fase, sin contexto previo de la sesión.

---

## 0. Cómo usar este documento

Cada fase es independiente y auto-contenida: lista los archivos exactos a tocar, el código exacto a agregar, y un checklist de aceptación. **No implementar dos fases a la vez** — el proyecto no tiene tests automatizados, cada fase se verifica manualmente en `http://localhost` antes de pasar a la siguiente.

### 0.1 Reglas del proyecto (aplican a todo este documento)

De `AGENTS.md` del proyecto, no se repiten en cada fase:

- Conversación en español; código, variables, funciones y mensajes de commit en inglés.
- `snake_case` para variables/funciones PHP, SQL en MAYÚSCULAS.
- Después de cualquier cambio de código PHP: `docker-compose restart php` (o `up -d --force-recreate php` si se tocó `composer.json`/`src/`) + smoke test manual en `http://localhost`.
- No se introduce build tooling (Sass, PostCSS, bundlers, Tailwind) — el CSS se edita directo en archivos `.css` planos. **El Tailwind del prototipo se traduce a vanilla CSS a mano.**
- No tocar `app/public/archive/` ni librerías vendorizadas existentes.
- No commitear ni pushear sin pedido explícito de Edgardo.

---

## 1. Decisiones de diseño

### 1.1 Paleta — INDIGO (idéntico al prototipo)

El prototipo usa **indigo** (`#4f46e5` / Tailwind `indigo-600`) como color de marca y acción. El sistema de diseño vigente del proyecto (`plan-diseno-claude.md`, `style.css`) usa **teal** (`#2D5C6C`).

**Decisión de Edgardo (2026-07-13):** el sidebar va **idéntico al prototipo** → **indigo**.

**Tradeoff documentado:** el sidebar queda temporalmente desacoplado cromáticamente del resto de la app (badges, botones, KPIs seguirán teal hasta que se migren). Esto es esperable: el sidebar es el **primer paso del rebrand completo**. Cuando se migre el resto de la shell (header, vistas), todo converge a indigo.

Tokens indigo del sidebar (fuente: `Sidebar.tsx` + escala Tailwind):

| Token | Valor | Uso |
|---|---|---|
| `--sb-primary` | `#4f46e5` | bg del icono de marca, links activos, dot pulsante, botón soporte hover |
| `--sb-primary-light` | `#eef2ff` | bg de ítem activo (`indigo-50`), hover sutil |
| `--sb-primary-mid` | `#6366f1` | iconos de ítem activo, glow (`indigo-500`) |
| `--sb-primary-dark` | `#4338ca` | hover de botón soporte (`indigo-700`) |
| `--sb-ink` | `#0f172a` | caja de soporte (`slate-900`) |

Escala neutral (slate, ya usada por el proyecto via Bootstrap):

| Token | Valor | Notas |
|---|---|---|
| `--sb-bg` | `#ffffff` | fondo del aside |
| `--sb-surface` | `rgba(248,250,252,0.5)` | brand header + footer (`slate-50/50`) |
| `--sb-border` | `#e2e8f0` | borde derecho (`slate-200`) |
| `--sb-border-soft` | `#f1f5f9` | bordes internos (`slate-100`) |
| `--sb-text` | `#0f172a` | texto principal (`slate-900`) |
| `--sb-text-muted` | `#64748b` | texto secundario (`slate-500`) |
| `--sb-text-faint` | `#94a3b8` | captions, category labels (`slate-400`) |

### 1.2 Tipografía

El prototipo carga tres fuentes de Google Fonts:

| Fuente | Uso en el sidebar |
|---|---|
| **Inter** (400–800) | nombres de ítem, texto de marca, texto de soporte |
| **JetBrains Mono** (400–700) | labels de categoría (uppercase tracking), subtítulos de marca, footer |
| **Outfit** | declarada en el prototipo pero **no usada en el sidebar** — no se carga |

Se cargan vía `<link>` de Google Fonts en `head-style.php` (mismo mecanismo que el `@import` de Open Sans ya existente en `style.css`).

### 1.3 Estructura — flat con categorías (sin sub-menús)

El prototipo agrupa los 8 ítems en **4 categorías con items planos** (sin `has-arrow` / `sub-menu` colapsable como el MetisMenu actual). Las páginas de alta/edición (`dash-*-add.php`, `dash-*-edit.php`) se alcanzan desde los botones dentro de cada vista, no desde el sidebar.

Mapeo prototipo → páginas PHP reales:

| Categoría | Label prototipo | Icono Lucide | URL PHP principal | Páginas que marcan activo |
|---|---|---|---|---|
| General | Tablero Principal | `layout-dashboard` | `index.php` | `index.php` |
| Operaciones | Clientes | `users` | `dash-customers.php` | `dash-customers.php`, `dash-customers-add.php`, `dash-customers-item.php` |
| Operaciones | Baños Químicos | `bath` | `dash-bathrooms.php` | `dash-bathrooms.php`, `dash-bathrooms-add.php`, `dash-bathrooms-edit.php`, `dash-bathrooms-contracts.php`, `dash-bathrooms-contracts-status.php` |
| Operaciones | Obras & Contratos | `file-text` | `dash-contracts.php` | `dash-contracts.php`, `dash-contracts-add.php`, `dash-contracts-edit.php`, `dash-contracts-item.php` |
| Operaciones | Servicios & Ruta | `clipboard-check` | `dash-services.php` | `dash-services.php`, `dash-services-add.php`, `dash-services-edit.php`, `dash-services-bath.php`, `dash-services-print.php` |
| Finanzas | Facturas | `receipt` | `dash-invoices-list.php` | `dash-invoices-list.php`, `dash-invoices-add.php`, `dash-invoices-edit.php`, `dash-invoices-upload.php`, `dash-invoices-upload-preview.php`, `dash-invoices-upload-result.php`, `dash-invoices-detail.php` |
| Finanzas | Certificados m³ | `file-check-2` | `dash-certificates.php` | `dash-certificates.php`, `dash-certificates-add.php`, `dash-certificates-item.php` |
| Administración | Personal & Roles | `users-2` | `dash-users-list.php` | `dash-users-list.php`, `dash-users-add.php`, `dash-users-edit.php`, `dash-users-profile.php` |

### 1.4 Iconografía — Lucide (vendorizar)

El prototipo usa `lucide-react`. El proyecto tiene Feather (predecesor de Lucide) en `assets/libs/feather-icons/`, pero Feather no incluye todos los íconos del sidebar (`layout-dashboard`, `users-2`, `file-check-2`, `bath`). Para un resultado idéntico, **se vendoriza Lucide** como UMD standalone (reemplaza elementos `<i data-lucide="...">` automáticamente, mismo patrón que Feather).

### 1.5 Scope — solo sidebar izquierdo

`vertical-menu.php` contiene hoy **dos bloques**: el topbar (`#page-topbar`) y el sidebar izquierdo (`.vertical-menu`). Este plan reemplaza **únicamente el sidebar izquierdo**. El topbar (logo, hamburger, search, dropdown de usuario) se queda como está — su rediseño es otro plan futuro (equivale al `Header.tsx` del prototipo).

Lo único que se toca del topbar es **reconectar el botón hamburger** (`#vertical-menu-btn`) al nuevo toggle del sidebar mobile.

---

## 2. Anatomía del sidebar (referencia rápida)

Estructura traducida de `Sidebar.tsx:37-148`:

```
<aside class="sb-sidebar">                      ← contenedor, off-canvas en mobile
  <div class="sb-brand">                        ← header de marca
    <div class="sb-brand-icon"> <i bath> </div> ← caja indigo + ícono
    <div class="sb-brand-text">
      <span class="sb-brand-name">Blanco</span>
      <span class="sb-brand-tagline">Servicios Ambientales</span>
    </div>
    <button class="sb-close"> <i x> </button>   ← solo mobile
  </div>

  <nav class="sb-nav">
    por cada categoría (General, Operaciones, ...):
      <h3 class="sb-category">Categoría</h3>
      <ul class="sb-nav-group">
        por cada ítem:
          <li>
            <a class="sb-nav-item [is-active]">
              <i data-lucide="..."> </i>
              <span>Nombre</span>
              [ <span class="sb-nav-dot"></span> ]   ← solo si active
            </a>
          </li>
      </ul>
  </nav>

  <div class="sb-support-wrap">
    <div class="sb-support">
      <p class="sb-support-label">Blanco Soporte</p>
      <p class="sb-support-text">¿Necesitas ayuda...?</p>
      <button class="sb-support-btn">Contactar Soporte</button>
    </div>
  </div>

  <div class="sb-footer">
    <p>© 2026 Blanco Servicios.</p>
    <p class="sb-footer-tag">Plataforma Eco-Sostenible v2.0</p>
  </div>
</aside>
<div class="sb-backdrop"></div>                  ← solo visible en mobile abierto
```

---

## 3. Fases de implementación

### Fase S1 — Vendorizar Lucide + cargar fuentes

**Por qué va primero:** el markup y el CSS del sidebar dependen de que `data-lucide` resuelva íconos y de que Inter/JetBrains Mono estén disponibles. Sin esto, las fases siguientes no se pueden verificar visualmente.

**S1.1 — Vendorizar Lucide**

Descargar el build UMD de Lucide dentro de `app/public/assets/libs/lucide/`:

```
app/public/assets/libs/lucide/
├── lucide.min.js      ← UMD global (window.lucide)
└── lucide.min.js.map  ← source map (opcional)
```

Fuente: `https://unpkg.com/lucide@latest/dist/umd/lucide.min.js` (versión mayor estable, la misma familia de íconos que usa `lucide-react` 0.546 del prototipo). Fijar la versión exacta al descargar (no usar `@latest` en el archivo final — reemplazar por la versión concreta, ej. `lucide@0.544.0`, para reproducibilidad).

**S1.2 — Cargar Lucide en `vendor-scripts.php`**

Agregar después de la línea de Feather (`vendor-scripts.php:7`):

```html
<!-- sidebar rebranding — iconos Lucide (reemplazo de Feather para el sidebar nuevo) -->
<script src="assets/libs/lucide/lucide.min.js"></script>
```

**S1.3 — Cargar fuentes Inter + JetBrains Mono en `head-style.php`**

Agregar al final de `head-style.php` (después del bloque de GLightBox, línea 27):

```html
<!-- sidebar rebranding — fuentes del nuevo sidebar -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
```

**Checklist de aceptación:**
- [ ] `app/public/assets/libs/lucide/lucide.min.js` existe y pesa ~50KB
- [ ] DevTools → Network: `lucide.min.js` carga con 200 al abrir `http://localhost`
- [ ] DevTools → Network: las dos familias de Google Fonts cargan con 200
- [ ] En consola, `typeof lucide === 'object'` es true
- [ ] Ninguna regresión visual: Feather sigue renderizando sus íconos existentes (no se borró `feather.min.js`)

---

### Fase S2 — CSS del sidebar (`sidebar.css`)

**Archivos a crear:** `app/public/assets/css/sidebar.css` (archivo nuevo, aislado y reversible).
**Archivos a tocar:** `app/public/layouts/head-style.php` (agregar el `<link>`).

Se crea un archivo separado en vez de meter todo en `style.css` porque: (a) el sidebar es un bloque grande y autónomo, (b) permite revertirlo quitando un solo `<link>`, (c) no contamina el `style.css` que es propiedad del plan de diseño teal vigente.

**S2.1 — Contenido completo de `sidebar.css`**

```css
/* ===== Sidebar rebranding — vanilla CSS traducido de rebranding/src/components/Sidebar.tsx ===== */
/* No usa Tailwind. Paleta indigo del prototipo, no la teal del resto de la app. */

.sb-sidebar {
    --sb-primary: #4f46e5;
    --sb-primary-light: #eef2ff;
    --sb-primary-mid: #6366f1;
    --sb-primary-dark: #4338ca;
    --sb-ink: #0f172a;

    --sb-bg: #ffffff;
    --sb-surface: rgba(248, 250, 252, 0.5);
    --sb-border: #e2e8f0;
    --sb-border-soft: #f1f5f9;
    --sb-text: #0f172a;
    --sb-text-muted: #64748b;
    --sb-text-faint: #94a3b8;

    position: fixed;
    inset: 0 auto 0 0;
    z-index: 1035;
    display: flex;
    flex-direction: column;
    width: 288px;
    background: var(--sb-bg);
    border-right: 1px solid var(--sb-border);
    color: var(--sb-text-muted);
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-family: 'Inter', sans-serif;
}

.sb-sidebar.is-open { transform: translateX(0); }

/* Desktop: siempre visible, estático dentro del layout */
@media (min-width: 992px) {
    .sb-sidebar {
        position: static;
        height: 100%;
        transform: none;
    }
}

/* Backdrop mobile */
.sb-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(4px);
    z-index: 1030;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease;
}
.sb-backdrop.is-visible { opacity: 1; visibility: visible; }
@media (min-width: 992px) { .sb-backdrop { display: none; } }

/* Brand header */
.sb-brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--sb-border-soft);
    background: var(--sb-surface);
}
.sb-brand-left { display: flex; align-items: center; gap: 12px; }
.sb-brand-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: var(--sb-primary);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
    color: #fff;
}
.sb-brand-icon svg { width: 20px; height: 20px; }
.sb-brand-name {
    display: block;
    font-weight: 700;
    font-size: 18px;
    letter-spacing: -0.025em;
    color: var(--sb-text);
    line-height: 1.1;
}
.sb-brand-tagline {
    display: block;
    margin-top: 2px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 9px;
    font-weight: 700;
    color: var(--sb-primary);
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.sb-close {
    display: inline-flex;
    padding: 6px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: var(--sb-text-faint);
    cursor: pointer;
    transition: all 0.15s;
}
.sb-close:hover { color: var(--sb-text); background: #f1f5f9; }
@media (min-width: 992px) { .sb-close { display: none; } }

/* Nav */
.sb-nav {
    flex: 1 1 auto;
    overflow-y: auto;
    padding: 24px 16px;
}
.sb-nav-section + .sb-nav-section { margin-top: 28px; }
.sb-category {
    padding: 0 12px;
    margin: 0 0 8px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    color: var(--sb-text-faint);
    letter-spacing: 0.1em;
    text-transform: uppercase;
}
.sb-nav-group { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.sb-nav-item {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 10px 12px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    color: var(--sb-text-muted);
    text-decoration: none;
    transition: all 0.2s;
}
.sb-nav-item:hover { color: var(--sb-text); background: #f8fafc; }
.sb-nav-item svg { width: 16px; height: 16px; color: var(--sb-text-faint); transition: color 0.2s, transform 0.2s; flex-shrink: 0; }
.sb-nav-item:hover svg { color: var(--sb-primary-mid); transform: scale(1.1); }

/* Active state */
.sb-nav-item.is-active {
    background: var(--sb-primary-light);
    color: var(--sb-primary-dark);
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.sb-nav-item.is-active svg { color: var(--sb-primary); }
.sb-nav-dot {
    position: absolute;
    right: 12px;
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--sb-primary);
    animation: sb-pulse 2s infinite;
}
@keyframes sb-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* Support widget */
.sb-support-wrap {
    padding: 16px;
    border-top: 1px solid var(--sb-border-soft);
    margin-top: auto;
    background: rgba(248, 250, 252, 0.3);
}
.sb-support {
    background: var(--sb-ink);
    color: #fff;
    border-radius: 16px;
    padding: 16px;
}
.sb-support-label {
    margin: 0 0 4px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 9px;
    font-weight: 700;
    color: var(--sb-primary-mid);
    letter-spacing: 0.05em;
    text-transform: uppercase;
}
.sb-support-text { margin: 0 0 12px; font-size: 12px; font-weight: 500; line-height: 1.4; }
.sb-support-btn {
    width: 100%;
    padding: 8px;
    border: none;
    border-radius: 12px;
    background: var(--sb-primary-mid);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.sb-support-btn:hover { background: var(--sb-primary); }

/* Footer */
.sb-footer {
    padding: 16px;
    border-top: 1px solid var(--sb-border-soft);
    background: var(--sb-surface);
    text-align: center;
}
.sb-footer p { margin: 0; font-family: 'JetBrains Mono', monospace; font-size: 9px; color: var(--sb-text-faint); }
.sb-footer-tag { margin-top: 2px !important; font-size: 8px !important; color: rgba(99, 102, 241, 0.8) !important; font-weight: 700 !important; }
```

**S2.2 — Cargar `sidebar.css` en `head-style.php`**

Agregar después del bloque de fuentes (S1.3), al final del archivo:

```html
<!-- sidebar rebranding -->
<link href="assets/css/sidebar.css" rel="stylesheet" type="text/css">
```

**Checklist de aceptación:**
- [ ] `assets/css/sidebar.css` existe y carga con 200 en DevTools → Network
- [ ] DevTools → Elements: una clase `.sb-sidebar` temporal de prueba resuelve las variables CSS a los valores indigo correctos (no "invalid property")
- [ ] `docker-compose restart php` + recargar `http://localhost` sin regresiones visuales (las clases nuevas no matchean nada todavía, así que la app se ve igual)

---

### Fase S3 — Markup PHP del sidebar

**Archivos a crear:** `app/public/layouts/sidebar.php` (nuevo).
**Archivos a tocar:** `app/public/layouts/vertical-menu.php` (reemplazar el bloque `.vertical-menu` por un `include` del nuevo archivo).

Se extrae el sidebar a un archivo propio para mantener `vertical-menu.php` enfocado en el topbar y permitir revertir el cambio tocando un solo `include`.

**S3.1 — Contenido de `layouts/sidebar.php`**

```php
<?php
$current_script = basename($_SERVER['PHP_SELF']);

$nav_sections = [
    'General' => [
        [
            'label' => 'Tablero Principal',
            'icon' => 'layout-dashboard',
            'url' => 'index.php',
            'match' => ['index.php'],
        ],
    ],
    'Operaciones' => [
        [
            'label' => 'Clientes',
            'icon' => 'users',
            'url' => 'dash-customers.php',
            'match' => ['dash-customers.php', 'dash-customers-add.php', 'dash-customers-item.php'],
        ],
        [
            'label' => 'Baños Químicos',
            'icon' => 'bath',
            'url' => 'dash-bathrooms.php',
            'match' => ['dash-bathrooms.php', 'dash-bathrooms-add.php', 'dash-bathrooms-edit.php', 'dash-bathrooms-contracts.php', 'dash-bathrooms-contracts-status.php'],
        ],
        [
            'label' => 'Obras & Contratos',
            'icon' => 'file-text',
            'url' => 'dash-contracts.php',
            'match' => ['dash-contracts.php', 'dash-contracts-add.php', 'dash-contracts-edit.php', 'dash-contracts-item.php'],
        ],
        [
            'label' => 'Servicios & Ruta',
            'icon' => 'clipboard-check',
            'url' => 'dash-services.php',
            'match' => ['dash-services.php', 'dash-services-add.php', 'dash-services-edit.php', 'dash-services-bath.php', 'dash-services-print.php'],
        ],
    ],
    'Finanzas' => [
        [
            'label' => 'Facturas',
            'icon' => 'receipt',
            'url' => 'dash-invoices-list.php',
            'match' => ['dash-invoices-list.php', 'dash-invoices-add.php', 'dash-invoices-edit.php', 'dash-invoices-upload.php', 'dash-invoices-upload-preview.php', 'dash-invoices-upload-result.php', 'dash-invoices-detail.php'],
        ],
        [
            'label' => 'Certificados m³',
            'icon' => 'file-check-2',
            'url' => 'dash-certificates.php',
            'match' => ['dash-certificates.php', 'dash-certificates-add.php', 'dash-certificates-item.php'],
        ],
    ],
    'Administración' => [
        [
            'label' => 'Personal & Roles',
            'icon' => 'users-2',
            'url' => 'dash-users-list.php',
            'match' => ['dash-users-list.php', 'dash-users-add.php', 'dash-users-edit.php', 'dash-users-profile.php'],
        ],
    ],
];
?>

<!-- ===== Sidebar rebranding (reemplaza .vertical-menu legacy) ===== -->
<div class="sb-backdrop" id="sb-backdrop"></div>

<aside class="sb-sidebar" id="sb-sidebar">
    <div class="sb-brand">
        <div class="sb-brand-left">
            <div class="sb-brand-icon">
                <i data-lucide="bath"></i>
            </div>
            <div>
                <span class="sb-brand-name">Blanco</span>
                <span class="sb-brand-tagline">Servicios Ambientales</span>
            </div>
        </div>
        <button type="button" class="sb-close" id="sb-close-btn" aria-label="Cerrar menú">
            <i data-lucide="x"></i>
        </button>
    </div>

    <nav class="sb-nav">
        <?php foreach ($nav_sections as $category => $items): ?>
            <div class="sb-nav-section">
                <h3 class="sb-category"><?php echo $category; ?></h3>
                <ul class="sb-nav-group">
                    <?php foreach ($items as $item): ?>
                        <?php $is_active = in_array($current_script, $item['match'], true); ?>
                        <li>
                            <a href="<?php echo $item['url']; ?>" class="sb-nav-item <?php echo $is_active ? 'is-active' : ''; ?>">
                                <i data-lucide="<?php echo $item['icon']; ?>"></i>
                                <span><?php echo $item['label']; ?></span>
                                <?php if ($is_active): ?>
                                    <span class="sb-nav-dot"></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </nav>

    <div class="sb-support-wrap">
        <div class="sb-support">
            <p class="sb-support-label">Blanco Soporte</p>
            <p class="sb-support-text">¿Necesitas ayuda con las rutas, m³ o facturas?</p>
            <button type="button" class="sb-support-btn" onclick="alert('Soporte Blanco: Contactando con la central de operaciones de Castro...')">
                Contactar Soporte
            </button>
        </div>
    </div>

    <div class="sb-footer">
        <p>&copy; 2026 Blanco Servicios.</p>
        <p class="sb-footer-tag">Plataforma Eco-Sostenible v2.0</p>
    </div>
</aside>
<!-- ===== End sidebar rebranding ===== -->
```

**S3.2 — Editar `vertical-menu.php`: reemplazar el bloque `.vertical-menu`**

En `vertical-menu.php`, el bloque que arranca con `<!-- ========== Left Sidebar Start ========== -->` (línea 74) y termina con `<!-- Left Sidebar End -->` (línea 185) se reemplaza por:

```php
<?php include 'layouts/sidebar.php'; ?>
```

El `#page-topbar` (líneas 1-72) **se queda intacto**. Solo se reemplaza la sección del sidebar izquierdo.

**Checklist de aceptación:**
- [ ] `docker-compose restart php` + `http://localhost` → el sidebar nuevo se renderiza con la marca, las 4 categorías, los 8 ítems y el widget de soporte
- [ ] Los íconos Lucide renderizan como SVG (no como `<i>` vacío) — si no, falta el `lucide.createIcons()` (se agrega en Fase S4)
- [ ] El ítem de la página actual tiene `is-active` (fondo indigo claro + dot pulsante) — verificar en `index.php`, `dash-customers.php`, `dash-bathrooms.php`
- [ ] Navegar a una sub-página (ej. `dash-customers-add.php`) mantiene "Clientes" como activo
- [ ] El sidebar viejo (MetisMenu con sub-menús) ya **no aparece** — fue reemplazado
- [ ] El topbar sigue visible e intacto
- [ ] No hay errores 500 ni warnings PHP en `docker-compose logs php`

---

### Fase S4 — JS de toggle mobile + init Lucide

**Archivos a crear:** `app/public/assets/js/components/sidebar.js` (nuevo).
**Archivos a tocar:** `app/public/layouts/vendor-scripts.php` (agregar la carga).

El sidebar actual usa `#vertical-menu-btn` (en el topbar) para toggle, gestionado por el JS del template Skote. El sidebar nuevo necesita su propio toggle porque cambia las clases y maneja un backdrop separado.

**S4.1 — Contenido de `sidebar.js`**

```javascript
(function () {
    'use strict';

    var sidebar = document.getElementById('sb-sidebar');
    var backdrop = document.getElementById('sb-backdrop');
    if (!sidebar || !backdrop) return;

    function open() {
        sidebar.classList.add('is-open');
        backdrop.classList.add('is-visible');
    }

    function close() {
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
    }

    function toggle() {
        if (sidebar.classList.contains('is-open')) {
            close();
        } else {
            open();
        }
    }

    // Reconectar el botón hamburger del topbar al nuevo sidebar
    var hamburger = document.getElementById('vertical-menu-btn');
    if (hamburger) {
        hamburger.addEventListener('click', function (e) {
            e.preventDefault();
            toggle();
        });
    }

    // Cerrar desde el botón X del sidebar y desde el backdrop
    var closeBtn = document.getElementById('sb-close-btn');
    if (closeBtn) closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    // Escape cierra el sidebar (accesibilidad)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('is-open')) close();
    });

    // Inicializar íconos Lucide del sidebar (y de toda la página)
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
})();
```

**S4.2 — Cargar `sidebar.js` en `vendor-scripts.php`**

Agregar al final de `vendor-scripts.php` (después de la línea de GLightBox):

```html
<!-- sidebar rebranding — toggle + init Lucide -->
<script src="assets/js/components/sidebar.js"></script>
```

**Orden de carga importante:** `sidebar.js` debe ir **después** de `lucide.min.js` (cargado en S1.2) para que `window.lucide.createIcons()` esté disponible.

**Checklist de aceptación:**
- [ ] En desktop (≥992px): el sidebar es siempre visible, no hay backdrop, el hamburger no hace nada visible
- [ ] En mobile (<992px) con DevTools: el hamburger abre el sidebar (slide-in) + muestra backdrop blur
- [ ] Click en backdrop cierra el sidebar
- [ ] Botón X cierra el sidebar
- [ ] Tecla Escape cierra el sidebar
- [ ] Los íconos Lucide renderizan como SVG con `stroke` correcto (color hereda del CSS)
- [ ] Al navegar entre páginas, el sidebar mantiene su estado (siempre abierto en desktop, cerrado por defecto en mobile)
- [ ] El widget "Contactar Soporte" dispara el `alert` al hacer click

---

### Fase S5 — Ajustes de layout del contenedor principal

**Por qué es necesario:** el sidebar actual (MetisMenu) era parte del sistema de layout del template Skote, que usa `.vertical-menu` + `.main-content` con márgenes calculados por el JS del template. El sidebar nuevo es `position: static` en desktop dentro del flujo normal, pero el `#layout-wrapper` del template puede esperar un flex/grid específico.

**Archivos a tocar:** `app/public/assets/css/sidebar.css` (ajustes finales tras QA visual).

**Qué verificar y ajustar:**
1. En desktop, el contenido principal (`.main-content` / `.page-content`) debe quedar **a la derecha del sidebar** sin superponerse. Si el template Skote calculaba el margin-left por JS, puede ser necesario agregar en `sidebar.css`:
   ```css
   @media (min-width: 992px) {
       .main-content { margin-left: 0 !important; }
       /* o ajustar el #layout-wrapper a flex si el sidebar se sale del flujo */
   }
   ```
2. El topbar (`#page-topbar`) debe ocupar el ancho restante a la derecha del sidebar, no todo el viewport.
3. Verificar que el scroll vertical del sidebar (`.sb-nav { overflow-y: auto }`) funciona con `simplebar` o nativo.

**Este es el paso de QA visual más importante** — traducir un componente React que controla su propio layout a un PHP que se inserta en un layout legacy existente tiene aristas. Si la estructura del template Skote (`.vertical-menu` flotante con margin-left en `.main-content`) rompe, ajustar aquí.

**Checklist de aceptación:**
- [ ] En desktop, sidebar (288px) + contenido principal llenan el viewport sin overlap ni gap raro
- [ ] En mobile, el contenido ocupa todo el ancho; el sidebar se superpone al abrirse
- [ ] No aparece scroll horizontal fantasma en desktop
- [ ] El topbar se alinea al ancho del contenido, no se desborda

---

## 4. Verificación de integración final

Cuando las 5 fases están implementadas:

1. **Identidad:** el sidebar muestra "Blanco / Servicios Ambientales" con icono indigo de bath — idéntico al `Sidebar.tsx:55-67`.
2. **Navegación:** las 4 categorías con labels mono uppercase, los 8 ítems con íconos Lucide correctos — comparar lado a lado con el prototipo corriendo (`cd rebranding && npm install && npm run dev`).
3. **Estado activo:** la página actual se highlighting con bg indigo claro + dot pulsante — verificar en al menos una página por categoría.
4. **Responsive:** mobile muestra off-canvas con backdrop; desktop muestra sidebar fijo.
5. **Sin regresión:** el topbar, las vistas `dash-*.php`, los DataTables y los modales siguen funcionando.
6. **Sin MetisMenu residual:** `grep -rn "metismenu\|vertical-menu" app/public/layouts/` devuelve solo el include del topbar y el `#vertical-menu-btn` (que se reusa como trigger).

---

## 5. Fuera de alcance de este plan

- **Rediseño del topbar** (`#page-topbar` en `vertical-menu.php`): logo, search, dropdown de usuario, notifications bell — corresponde al `Header.tsx` del prototipo. Plan futuro separado.
- **Migración del resto de la app a indigo:** badges, botones, KPIs siguen teal. Este sidebar es el primer paso del rebrand; la convergencia total es otro plan.
- **Eliminación de Feather Icons:** se conserva `feather.min.js` para los 3 íconos legacy del header (`home`, `search`, `users`). Se elimina cuando se rediseñe el topbar.
- **El widget de soporte con un endpoint real:** el `alert()` del botón "Contactar Soporte" es mock idéntico al prototipo. Conectar a un backend real es.feature futura.
- **Internacionalización de los labels del sidebar:** el prototipo está en español hardcodeado. El sidebar actual usa `$language["..."]`. Este plan usa español hardcodeado para ser idéntico al prototipo; si se necesita i18n, agregar después.
- **Dark mode:** el prototipo no lo tiene. Las variables `[data-bs-theme=dark]` no se tocan.
- **`right-sidebar.php`:** existe un archivo `layouts/right-sidebar.php` que no se auditó en este plan — si está en uso, queda fuera de scope.

---

## 6. Referencias

- **Prototipo fuente:** `rebranding/src/components/Sidebar.tsx` (149 líneas, React + Tailwind)
- **Sistema visual del prototipo:** `rebranding/src/index.css` (fuentes, theme), análisis completo en sesión del 2026-07-13.
- **Plan de diseño vigente (teal):** `.doc/Viejos/plan-diseno-claude.md` y `.doc/Viejos/plan-diseno-sistema-visual.md` — referencia de convenciones de fases y checklists, NO de paleta (este plan la sobreescribe para el sidebar).
- **Sidebar actual a reemplazar:** `app/public/layouts/vertical-menu.php:74-185` (bloque `.vertical-menu` con MetisMenu).
