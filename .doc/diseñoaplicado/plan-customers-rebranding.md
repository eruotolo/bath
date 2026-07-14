# Plan — Clientes rebranding (Directorio de Clientes)

**Origen:** prototipo `rebranding/src/components/ClientesView.tsx` (React + Tailwind).
**Objetivo:** reestilizar `dash-customers.php` (tabla de clientes) para que visualmente converja con la paleta indigo del rebrand (sidebar/header/dashboard), **sin reescribir** el sistema de tabla homologado (`table-card`/`table-toolbar`, Sprint 2 `datatable.js`) ni las funciones ya operativas (alta, edición, inactivación, export CSV/Excel/PDF).
**Depende de:** `plan-sidebar-rebranding.md` (Lucide, Inter/JetBrains Mono) y `plan-header-rebranding.md` (el header ya muestra "Directorio de Clientes" como título de página) — ambos ya implementados.
**Estado:** ✅ implementado (2026-07-13). Verificado en Chrome: card indigo redondeado, columnas Contacto/Ubicación/Estado, buscador nativo con lupa (texto "Buscar:" colapsado, filtra correctamente: 87/90 al buscar "castro"), paginación con página activa indigo, y **aislamiento confirmado** — `dash-contracts.php` sigue 100% en teal (badges, buscador, botón "Agregar") sin fugas del scope `.cl-clients`. Sin warnings en `docker-compose logs php`.

**Ronda 2 (mismo día):** Edgardo marcó que los botones de export/columnas (Bootstrap outline-secondary + FontAwesome, texto+ícono) y la paginación (cuadrados Bootstrap pegados) no calzaban con el diseño indigo. Corregido:
- Export/Columnas: de `btn btn-outline-secondary btn-sm` con texto a `cl-icon-btn` — solo ícono Lucide (`file-text`/`file-spreadsheet`/`file-down`/`columns-3`), 34×34px, borde `#f1f5f9` casi invisible, hover indigo sutil. Se quitó la clase `btn-group` de Bootstrap (unía los bordes); ahora es un `flex` con `gap: 6px`, como pills sueltos.
- Paginación: `.dataTables_paginate .page-link` pasó de radius 6px + borde `--bs-border-color` (gris duro, cuadrados pegados) a radius 8px + borde `#f1f5f9` + `gap: 6px` entre pills — replica el estilo `rounded-lg border-slate-100` del prototipo. Activo ya usaba `var(--color-primary)` (ya indigo desde la Fase C1), solo se afinó el borde/radius.
- Footer del wrapper (info + paginación): fondo `rgba(248,250,252,0.5)` y el texto "Mostrando X de Y páginas" en JetBrains Mono mayúsculas, como el prototipo.

---

## 0. Reglas y convenciones

Mismas reglas que los planes hermanos (sin build tooling, `docker-compose restart php` tras cambios, no commitear sin pedido).

### 0.1 Decisión clave: scoping por sobreescritura de custom properties

`app/public/assets/css/style.css` ya tiene un sistema de tabla **homologado y compartido** por todas las vistas `dash-*-list` (comentario en el propio archivo: "Tabla homologada (todas las vistas dash-\*-list)"). Sus reglas (`.table-card`, `.table-toolbar`, badges, paginación, botones de export) usan **custom properties semánticas** (`--color-primary`, `--color-primary-bg-subtle`, `--color-success`, etc.) definidas en `:root` con la paleta teal actual (`#2D5C6C`).

En vez de reescribir esas reglas (que rompería Contratos, Servicios, Facturas, Baños, Certificados, Usuarios — todas comparten el mismo CSS), este plan **sobreescribe las custom properties dentro de un scope nuevo** `.cl-clients` que envuelve únicamente el contenido de `dash-customers.php`:

```css
.cl-clients {
    --color-primary: #4f46e5;           /* indigo-600, en vez de teal */
    --color-primary-bg-subtle: #eef2ff;
    --color-primary-border-subtle: #c7d2fe;
}
```

Como las reglas de `style.css` referencian `var(--color-primary)`, todo lo que ya funciona (hover de filas, botón activo de paginación, hover de botones de export, botón "Ver") se vuelve indigo automáticamente, **sin tocar una sola línea de `style.css`**. Mismo criterio de aislamiento/reversibilidad que `sidebar.css`/`header.css`/`dashboard.css`: borrar la clase `.cl-clients` o el link del CSS revierte 100% al teal compartido.

Esto es **más seguro que el patrón de esos planes hermanos**: ellos crean componentes nuevos desde cero; acá se reutiliza deliberadamente el sistema de tabla existente (menor superficie de cambio, cero riesgo de romper otras vistas de lista).

### 0.2 Desviaciones intencionales del prototipo

| Elemento | Prototipo | Este plan | Motivo |
|---|---|---|---|
| **Slide-over "Ver Ficha Cliente"** | Drawer lateral con JS state (contratos + baños del cliente) | Se mantiene el link a `dash-customers-item.php` (página completa ya funcional, con edición inline vía modal) | Reescribir como drawer duplicaría una vista que ya funciona; sin backend de contratos/baños por cliente en este scope |
| **Slide-over "Agregar Cliente"** | Drawer lateral con formulario propio | Se mantiene el link a `dash-customers-add.php` (formulario completo ya con selects mejorados, RUT mask) | Idem — no reinventar un formulario que ya está migrado (Sprint 3/4) |
| **Búsqueda** | State local de React, filtra el array en cliente | Se mantiene el buscador nativo de DataTables (`datatable.js`), reestilizado | Ya es funcional y cubre las mismas columnas (RUT, nombre, comuna vía texto de celda) |
| **Botones export CSV/Excel/PDF/Columnas** | No existen en el prototipo | Se mantienen (Sprint 2), solo reestilizados | Feature real ya usada, el prototipo es un mockup más simple |
| **Columna Acción** | Solo ícono "Ver" (ojo) | Se mantiene ícono "Ver" + dropdown "Inactivar" | Acción crítica de negocio que el prototipo (mock) omite |
| **Columna Estado** | Badge Activo/Inactivo | Se agrega, pero **siempre mostrará "Activo"** | `ListCustomers` usa `listActive()`, que ya filtra `estado_Cliente = 1` — no hay clientes inactivos en este listado hoy. Se agrega por fidelidad visual y quedará funcional el día que el listado incluya inactivos |
| **Paginación** | Botones estáticos hardcodeados (`1`, `Anterior`, `Siguiente` sin funcionalidad) | Se mantiene la paginación real de DataTables, reestilizada a la estética de pills indigo | La del prototipo es un mock no funcional |
| **Título "Directorio de Clientes"** | Lo muestra el Header | El header real (`layouts/header.php`) ya lo muestra vía `$page_titles['dash-customers.php']`. Se **elimina** el `<h4>Listado de Clientes</h4>` duplicado que tenía la vista | Evita repetir el mismo título dos veces en la misma pantalla |

---

## 1. Anatomía objetivo

```
<div class="cl-clients">                          ← scope de las custom properties indigo
  <div class="table-toolbar">                      ← YA EXISTE, solo se reestiliza
    <h5 class="table-toolbar-title">Clientes (90)</h5>
    <div class="table-toolbar-actions">
      <div class="table-toolbar-buttons btn-group"> CSV / Excel / PDF / Columnas (sin cambios funcionales)
      <div class="table-toolbar-search"></div>      ← JS reubica acá el buscador nativo de DataTables
      <a class="btn btn-light cl-btn-add">+ Agregar Nuevo Cliente</a>
    </div>
  </div>

  <div class="table-card">                         ← YA EXISTE, solo radius/border se ajustan
    <table id="datatable-buttons">
      <thead>RUT | Nombre Cliente | Contacto | Ubicación | Estado | Acción</thead>
      <tbody>
        RUT (mono)
        Nombre (bold) + ID: N (mono, debajo)
        Contacto: <phone icon> teléfono / <mail icon> email (apilado)
        Ubicación: <map-pin icon> comuna / ciudad (apilado)
        Estado: badge-status is-success "Activo"
        Acción: ojo (Ver) + dropdown (Inactivar) — YA EXISTE, sin cambios
      </tbody>
    </table>
  </div>
</div>
```

---

## 2. Fases de implementación

### Fase C1 — `assets/css/customers.css` (crear) + link en `head-style.php`

**Archivos a crear:** `app/public/assets/css/customers.css`.
**Archivos a tocar:** `app/public/layouts/head-style.php` (agregar `<link>`, mismo patrón que sidebar/header/dashboard — se carga global pero solo aplica donde exista `.cl-clients` en el DOM).

Contenido (ver sección 0.1 para la estrategia de custom properties):

- Scope `.cl-clients`: override de `--color-primary*`, `font-family: 'Inter'`.
- `.table-card` dentro del scope: `border-radius: 24px`.
- `.table-toolbar-title`: peso 700, color `#0f172a`; `.count` en JetBrains Mono, `#94a3b8`.
- `.table-toolbar-buttons .btn`: `border-radius: 10px`, texto 11px.
- Buscador nativo de DataTables reestilizado: `border-radius: 14px`, ícono lupa vía `::before` con SVG inline en `background`, texto "Buscar:" colapsado con `font-size: 0` en el label (el input recupera su tamaño explícito — no se toca `datatable.js` ni el i18n compartido).
- `.cl-btn-add`: botón sólido indigo, `border-radius: 14px`, ícono + texto, sombra sutil.
- Celdas: `.cl-rut` (mono), `.cl-name` (bold) + `.cl-name-id` (mono, faint), `.cl-meta-row` (ícono + texto, para Contacto/Ubicación), `.cl-comuna`/`.cl-city`.

**Checklist de aceptación:**
- [ ] `assets/css/customers.css` carga con 200 en Network
- [ ] Ninguna otra vista de lista (Contratos, Servicios, Baños, Facturas, Certificados, Usuarios) cambia de color — siguen en teal
- [ ] `docker-compose restart php` sin errores

---

### Fase C2 — Markup de `dash-customers.php`

**Archivos a tocar:** `app/public/dash-customers.php`.

**C2.1 — Eliminar el `<h4>Listado de Clientes</h4>` duplicado**

Quitar el bloque `page-title-box` (el header ya muestra "Directorio de Clientes").

**C2.2 — Envolver el contenido en `.cl-clients`**

El `<div class="table-toolbar">...</div>` y el `<div class="table-card mb-4">...</div>` quedan envueltos en `<div class="cl-clients"> ... </div>`.

**C2.3 — Botón "Agregar Nuevo Cliente" con ícono Lucide**

```php
<a href="dash-customers-add.php" class="btn btn-light cl-btn-add"><i data-lucide="plus"></i> Agregar Nuevo Cliente</a>
```
(Reemplaza el ícono `bx bx-plus` — Lucide ya se inicializa global vía `sidebar.js`, sin JS nuevo.)

**C2.4 — Columnas nuevas: Contacto (con íconos), Ubicación, Estado**

`<thead>`:
```php
<tr>
    <th scope="col">RUT</th>
    <th scope="col">Nombre Cliente</th>
    <th scope="col">Contacto</th>
    <th scope="col">Ubicación</th>
    <th scope="col">Estado</th>
    <th style="width: 90px; min-width: 90px;" class="text-center">Acción</th>
</tr>
```

`<tbody>` por fila:
```php
<tr>
    <td>
        <a href="dash-customers-item.php?id_Cliente=<?php echo $customer->id ?>" class="text-body cl-rut"><?php echo htmlspecialchars($customer->rut) ?></a>
    </td>
    <td>
        <span class="cl-name"><?php echo htmlspecialchars($customer->name) ?></span>
        <span class="cl-name-id">ID: <?php echo (int) $customer->id ?></span>
    </td>
    <td>
        <div class="cl-meta-row"><i data-lucide="phone"></i> <span><?php echo htmlspecialchars($customer->phone) ?></span></div>
        <div class="cl-meta-row"><i data-lucide="mail"></i> <span><?php echo htmlspecialchars($customer->email) ?></span></div>
    </td>
    <td>
        <div class="cl-meta-row"><i data-lucide="map-pin"></i> <span class="cl-comuna"><?php echo htmlspecialchars($customer->commune) ?></span></div>
        <span class="cl-city"><?php echo htmlspecialchars($customer->city) ?></span>
    </td>
    <td>
        <span class="badge-status <?php echo $customer->active ? 'is-success' : 'is-danger'; ?>">
            <?php echo $customer->active ? 'Activo' : 'Inactivo'; ?>
        </span>
    </td>
    <td class="text-center">
        <!-- SIN CAMBIOS: botón Ver + dropdown Inactivar ya existentes -->
    </td>
</tr>
```

Nota: `.badge-status` ya existe en `style.css` (usa `--color-success`/`--color-danger`, no forman parte del override indigo — se mantienen sus colores semánticos actuales).

**Checklist de aceptación:**
- [ ] `php -l dash-customers.php` sin errores
- [ ] La tabla carga las 90 filas, ordenamiento/búsqueda/paginación de DataTables siguen funcionando
- [ ] Export CSV/Excel/PDF siguen generando archivo (columnas nuevas incluidas)
- [ ] Los íconos Lucide (phone, mail, map-pin, plus) renderizan como SVG
- [ ] El botón "Ver" navega a `dash-customers-item.php`; el dropdown "Inactivar" sigue pidiendo confirmación y funcionando
- [ ] Sin regresión: ninguna otra vista de lista cambió de aspecto

---

## 3. Verificación de integración final

1. **Aislamiento:** abrir `dash-contracts.php` o `dash-services.php` — deben verse exactamente igual que antes (teal), confirmando que el scope `.cl-clients` no tiene fugas.
2. **Fidelidad visual:** comparar con `rebranding/src/components/ClientesView.tsx` corriendo (`cd rebranding && npm run dev`) — misma jerarquía tipográfica, mismo indigo, mismos íconos por fila.
3. **Funcional:** alta de cliente (`dash-customers-add.php`), ver ficha (`dash-customers-item.php`), inactivar cliente — sin regresión.
4. **Responsive:** en mobile, `dt-responsive` sigue colapsando columnas (ya configurado en el `<table>` original).
5. **Sin errores** en `docker-compose logs php` ni en consola del navegador.

---

## 4. Fuera de alcance de este plan

- **Slide-over drawers** (ver ficha / agregar cliente in-place): quedan como páginas completas existentes. Convertirlas en drawers es un proyecto aparte (requeriría exponer contratos/baños por cliente vía AJAX).
- **Aplicar el mismo tratamiento a otras vistas de lista** (Contratos, Servicios, Facturas, Baños, Certificados, Usuarios): el patrón de este plan (override de custom properties scopeado) es reutilizable 1:1 para cada una cuando Edgardo lo pida — no se hace ahora para no exceder el pedido puntual de Clientes.
- **Estado dinámico Activo/Inactivo en el listado:** hoy `listActive()` solo trae activos. Si se decide listar también inactivos (con filtro), el badge ya queda listo para mostrarlo correctamente.

---

## 5. Referencias

- **Prototipo fuente:** `rebranding/src/components/ClientesView.tsx` (472 líneas, React + Tailwind).
- **Vista a reestilizar:** `app/public/dash-customers.php`.
- **Sistema de tabla homologado (no se reescribe, se reutiliza):** `app/public/assets/css/style.css` líneas 270-494, `app/public/assets/js/components/datatable.js`.
- **Entidad/repositorio:** `app/src/Domain/Customer/Customer.php`, `app/src/Infrastructure/Persistence/MysqliCustomerRepository.php` (`listActive()` filtra `estado_Cliente = 1`).
- **Planes hermanos:** `plan-sidebar-rebranding.md`, `plan-header-rebranding.md`, `plan-dashboard-rebranding.md`.
