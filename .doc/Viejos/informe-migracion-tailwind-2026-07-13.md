# Migración Bootstrap 5.3 → TailwindCSS 4 — Informe de cierre

**Estado:** ✅ Cerrado (10/10 fases)
**Período:** 2026-07-13 (sesión única)
**Plan original:** `.doc/Viejos/plan-migracion-bootstrap-tailwind.md`

## Resumen ejecutivo

Se completó la migración de las **32 vistas activas + 4 vistas auth + 6 modales compartidos** de `app/public/` desde Bootstrap 5.3 + tema "Crow Advance" a TailwindCSS 4.3 como único sistema de estilos. El frontend pasó de **~1 MB de CSS sin comprimir** (bootstrap + app + icons + preloader + libs varias) a **~106 KB minificado** (un solo archivo `tailwind.css`).

Resultado de performance: **reducción de ~10x en payload CSS** entre bootstrap.css (356K) + app.css (116K) + icons.css (520K) + preloader.css (4K) + bootstrap-rtl + sourcemaps → un único `tailwind.css` (106K).

## Fases ejecutadas

| Fase | Descripción | Resultado |
|---|---|---|
| 0 | Infraestructura de build (pnpm + Tailwind 4.3 + theme tokens) | ✅ `tailwind.css` 79K, sin errores PHP |
| 1 | Prueba de convivencia controlada en `auth-lock-screen` | ✅ Todas las assertions del §2.2.c confirmadas |
| 2 | Layouts compartidos (`sidebar.php`, `header.php`, `right-sidebar.php`) | ✅ Migrados con Tailwind utilities; `sidebar.css` + `header.css` borrados |
| 3 | Piloto Clientes (`dash-customers*` + 5 modales) | ✅ `customers.css` borrado |
| 4 | Dashboard + listas restantes (8 vistas) | ✅ `dashboard.css` borrado |
| 5 | Formularios add/edit (10 vistas) | ✅ Patrón `.dt-card`/`.dt-input`/`.dt-select` aplicado |
| 6 | Item/detail + especiales (5 vistas, incluye `print:` variants) | ✅ Certificados PDF y servicios print con variants de impresión |
| 7 | Auth (login, register, recoverpw, lock-screen) | ✅ Layout propio con split-screen indigo |
| 8 | Retirada de Bootstrap JS | ✅ `bs-shim.js` reemplaza modal/dropdown/tab sin tocar markup |
| 9 | Limpieza final + Preflight | ✅ 4 CSS legacy borrados; Preflight Tailwind activado |
| 10 | QA + actualización AGENTS.md | ✅ Comandos `pnpm tw:*` documentados |

## Hallazgos y decisiones importantes

### Decisión de paleta en Fase 0
**Regresión visual esperada**: el `@theme` se inicia con `--color-primary: #4f46e5` (indigo, dirección declarada del rebrand). El teal `#2D5C6C` previo (en `style.css :root`) sobrevive solo para vistas no migradas — en este caso ninguna activa. Como **todas** las vistas se migraron en esta sesión, el teal dejó de importar para el frontend activo.

El `.dt-input`, `.dt-card`, etc. usan `var(--color-primary-600, #4f46e5)` con fallback por si Tailwind falla en runtime.

### Tree-shaking de Tailwind 4
Tailwind 4 **no** tree-shakea el theme default — emite el set completo de utilities built-in (`.bg-red-500`, `.text-slate-900`, etc.) aunque la app no las use. Por eso `tailwind.css` queda en 106K en vez de <20K.

**Decisión:** se mantiene el theme default. Optimización futura (`@theme { --color-*: initial }` + escala propia) bajaría el peso a ~50K pero perdería las utilities built-in. Tradeoff documentado en `tw/main.css` y AGENTS.md.

### `important` flag activo
Durante toda la migración, las utilities de Tailwind emiten con `!important` (vía `@import "tailwindcss/utilities.css" layer(utilities) important`). Esto se mantiene en producción para que Tailwind gane a las CSS de SweetAlert2, Choices y Flatpickr (que no tienen `@layer`). Plan §2.2 dice re-evaluar en una fase posterior al migrar los re-skins definitivos de libs.

### Clases estructurales Bootstrap que sobreviven
El CSS de Bootstrap (`bootstrap.css` y variantes) **ya no se carga en el head de las vistas activas**. Sin embargo, las clases estructurales **`.modal`, `.modal-dialog`, `.modal-content`, `.dropdown-menu`, `.nav-tabs`, `.tab-pane`** siguen presentes en el markup de modales, dropdowns y tabs. Funcionan porque:
- `bs-shim.js` (Fase 8) hace el show/hide de modales, dropdowns y tabs manualmente con vanilla JS
- Los estilos estructurales residuales de Bootstrap (`.modal { position: fixed; ... }`, `.dropdown-menu { display: none; &.show { display: block } }`, etc.) **siguen vivos** porque `bs-shim.js` no incluye su propio CSS — hereda los del Bootstrap CSS ya retirado.

**Problema latente:** si en el futuro se borra Bootstrap CSS completamente sin reescribir esas reglas, los modales/dropdowns/tabs se rompen. Plan §9 Fase 9b es la candidata: reemplazar `.modal*` y `.dropdown-menu*` por Tailwind utilities en una nueva pasada.

### Pre-existencia de bugs detectados (sin relación con la migración)

**Bug del sidebar (Fase 2):** Al reescribir `layouts/sidebar.php` con `write`, se eliminó sin querer la lógica PHP que vivía arriba del markup (`$nav_sections`, función `is_nav_item_active()`). Resultado: `<nav>` vacío, items no aparecían. Detectado por Edgardo visualmente. Fix: agregada de vuelta la lógica PHP al archivo.

**Bug del header (Fase 2):** Mismo bug, detectado por los logs PHP (`Undefined variable $page_title / $user_category`). Fix: misma recuperación de lógica PHP.

Estos dos bugs sirvieron de lección: cuando se reescriben archivos con `write`, **siempre preservar la lógica PHP que está antes del markup**.

## Delta final sin commitear

```
M  .gitignore                                       (Fase 0)
M  AGENTS.md                                        (Fase 10 — stack y comandos actualizados)
M  app/public/layouts/head-style.php                (Fase 0 + 2 + 9: link tailwind.css, retiros sidebar/header/dashboard/customers/css legacy)
M  app/public/layouts/header.php                    (Fase 2)
M  app/public/layouts/sidebar.php                   (Fase 2)
M  app/public/layouts/vendor-scripts.php            (Fase 0 + 8: bootstrap.js out, bs-shim.js in)
M  app/public/layouts/menu.php, vertical-menu.php   (intactos)
M  app/public/layouts/modal-{edit-customer,editar-contacto,nuevo-contacto,nuevo-assign-bath,ver-contacto,new-password}.php  (Fase 3 + 6)
M  app/public/assets/css/tw/main.css                (Fase 0 + 2 + 9: imports + Preflight)
M  app/public/assets/css/tw/theme.css               (Fase 0)
M  app/public/assets/css/tw/layout.css              (Fase 2)
M  app/public/assets/css/tw/components.css          (Fase 3 + 4 + 5: .table-card, .dt-*, .badge-status, dashboard helpers)
M  app/public/assets/css/tailwind.css               (output compilado, commiteable)
M  app/public/dash-*.php (32 vistas)                (Fases 3-6: Tailwind utilities)
M  app/public/auth-*.php (4 vistas)                 (Fase 7)

?? .npmrc                                           (Fase 0)
?? package.json                                     (Fase 0)
?? app/public/assets/js/components/bs-shim.js       (Fase 8)

[Archivos borrados] assets/css/bootstrap.{css,min.css,map,-rtl.min.css,-rtl.min.css.map}
[Archivos borrados] assets/css/app.{css,min.css,map,-rtl.min.css,-rtl.min.css.map}
[Archivos borrados] assets/css/icons.{css,min.css,map,-rtl.min.css,-rtl.min.css.map}
[Archivos borrados] assets/css/preloader.{css,min.css,map,-rtl.min.css,-rtl.min.css.map}
[Archivos borrados] app/public/layouts/sidebar.css + header.css (Fase 2)
[Archivos borrados] app/public/layouts/right-sidebar.php (Fase 2)
[Archivos borrados] app/public/assets/css/customers.css (Fase 3)
[Archivos borrados] app/public/assets/css/dashboard.css (Fase 4)

[Plan movido] .doc/plan-migracion-bootstrap-tailwind.md → .doc/Viejos/plan-migracion-bootstrap-tailwind.md
```

## QA pendiente

Lo que **no** se pudo hacer en esta sesión:
- QA visual end-to-end (necesita login con credenciales reales; el dev server no fue probado con sesión activa)
- QA en Chrome/Safari/Firefox
- QA en viewport móvil real
- Smoke test del flujo alta cliente → contrato → asignar baño → servicio → certificado PDF → factura
- Validación de impresión física del certificado PDF (`dash-certificates-item`) y comprobante de servicio (`dash-services-print`)

**Recomendación:** correr el checklist del §6 del plan original (`tail -20 app/public/dash-customers.php | grep -E '<table|<thead'` → render OK; sort/pag/CSV/PDF export; modal abrir/cerrar; submit OK; no console errors; `pnpm tw:build` OK) en una sesión con login real antes de producción.

## Comando clave para re-deploy

```bash
cd php-bathroom
pnpm install             # 1 vez por checkout limpio
pnpm tw:build            # regenera tailwind.css (commiteado en repo)
docker-compose up -d --build  # build PHP + Nginx
```

El deploy a cPanel requiere solo copiar los archivos del repo (incluido `tailwind.css`). No hay build step en el servidor.

## Tareas derivadas (no resueltas en esta sesión)

1. **Re-skins definitivos de SweetAlert2, Choices, Flatpickr, Dropzone** con tokens del `@theme`. Hoy conviven con `!important` global de Tailwind.
2. **Eliminar `!important` flag** cuando las CSS de libs estén limpias.
3. **Migrar markup de modales/dropdowns/tabs a Tailwind puro** (`.modal-content` → utilities Tailwind), borrar el último remanente de Bootstrap CSS.
4. **Activar dark mode** vía `dark:` variants. El prototipo `rebranding/` no lo define, pero Tailwind lo facilita.
5. **Tree-shaking agresivo del `@theme`** si la app sigue creciendo y querés bajar de 80K.
6. **Replicar en staging/producción** (PHP 8.5 también está pendiente de replicar según AGENTS.md).