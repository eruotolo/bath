# Plan — Rebranding de Certificados (drawer + impresión, sin páginas extras)

**Estado:** propuesta (pendiente de aprobación) — **decisión de alcance confirmada (§9)**
**Fecha:** 2026-07-18
**Objetivo:** migrar la sección Certificados al mismo lineamiento de las vistas ya rebrandeadas (Baños, Contratos, Servicios, Facturas): listado con `native-table.js`, **creación en drawer lateral embebido** e **impresión vía lightbox**, eliminando la dependencia de páginas de página completa para las acciones. Los archivos que queden huérfanos se registran en `.doc/orfanatos-pendientes.md`.

---

## 0. Estado actual (hallazgos)

| Archivo | Rol hoy | Estado tras el plan |
|---|---|---|
| `dash-certificates.php` | Listado, shell viejo **DataTables + Bootstrap** | Se migra a `native-table.js` + drawer |
| `dash-certificates-add.php` | Página completa "Nuevo Certificado" (form → `certificate-new.php`) | **Huérfana** (reemplazada por drawer `?action=new`) |
| `dash-certificates-item.php` | Página completa de preview imprimible (`FindCertificateForPrint`) | **Huérfana** (reemplazada por lightbox sobre el PDF) |
| `controller/certificate-new.php` | Crea el certificado (DDD `CreateCertificate`) | Se **reutiliza**; solo cambian sus redirects |
| `controller/certificate-pdf.php` | Genera el PDF con TCPDF | Se **reutiliza** + se **corrige el bug de imágenes** |
| `controller/certificate-remove.php` | Elimina (DDD `DeleteCertificate`) | Se mantiene |

**Hallazgos clave:**
- **Certificados NO tiene edición** y **no se va a agregar** (§9). El `CertificateRepositoryInterface` solo expone `insert / delete / findForPrint / listWithDetails / count / nextCorrelativeForToday` — no hay `update` ni `findById`. El drawer cubre **solo creación**.
- **No hay estados** (activo/inactivo/facturado) en certificados → el listado **no** lleva pills ni toggle grid/tabla. Es el caso simple: tabla + buscador + paginación, como `dash-contracts.php` / `dash-bathrooms-contracts.php` (no como Servicios/Baños que sí tienen grid+pills).
- **Bug de TCPDF preexistente en `certificate-pdf.php`** (líneas 37-38 y 57): las imágenes se insertan con `<img src="{ruta filesystem}">` dentro de `writeHTML()`, patrón que **no renderiza bajo PHP-FPM** → el logo y la firma salen en blanco. Ya documentado en el cluster "Impresión de Servicios" de `orfanatos-pendientes.md`. Se corrige acá con `$pdf->Image()` nativo, como se hizo en `service-pdf.php`.

---

## 1. Alcance

**Incluye:**
- Migrar `dash-certificates.php` al patrón `native-table.js` (buscador + paginación client-side, tarjeta redondeada, toolbar, acciones por fila con íconos Lucide).
- **Drawer de creación** embebido (`?action=new`) que reemplaza `dash-certificates-add.php`, con el cascade Cliente → Contrato (Choices.js).
- **Impresión vía lightbox** (glightbox) apuntando a `certificate-pdf.php`, reemplazando `dash-certificates-item.php`, **más la corrección del bug de imágenes** del PDF.
- **Eliminar** con confirmación SweetAlert2 (ya existe, se revalida).
- **Registrar huérfanos** en `.doc/orfanatos-pendientes.md`.

**No incluye (confirmado §9):**
- **Edición de certificados** — no se agrega el path DDD de update. El drawer no tiene modo `edit`.

---

## 2. Diseño del listado (`dash-certificates.php`)

Calcado de `dash-contracts.php` (el análogo más cercano: tabla con acciones por fila, sin grid ni pills):

- **`<head>`:** quitar los `<link>` de DataTables (`dataTables.bootstrap4`, `responsive.bootstrap4`).
- **Toolbar:** título "Certificados (N)" + buscador (`data-table-search-input`) + botón "Agregar Nuevo Certificado" que abre el drawer (`?action=new`), no navega a otra página.
- **Tabla:** dentro de `table-card` redondeado, marcada para `native-table.js` (`data-table-native-wrap`). Columnas actuales: Nro. Certificado, Cliente, RUT, Obra, Fecha del Servicio, Acciones.
- **Acciones por fila:**
  - **Imprimir** → `controller/certificate-pdf.php?...` con `data-glightbox-preview data-type="external" data-width="900px" data-height="90vh"` (abre el PDF en lightbox sobre la lista).
  - **Eliminar** → `controller/certificate-remove.php?id_Certificado=X` con `data-confirm-delete` (SweetAlert2).
  - Se **quita** el botón "Ver" (`eye` → `dash-certificates-item.php`): esa página desaparece; su función (ver el documento) la cumple ahora "Imprimir" (preview del PDF en lightbox).
- **Scripts:** quitar todo el bloque DataTables/buttons/jszip/pdfmake y el `DataTable.init`. Cargar `assets/js/components/native-table.js` (mismo patrón que Servicios/Contratos).

---

## 3. Drawer de creación (`?action=new`)

- Drawer lateral embebido en `dash-certificates.php` (mismo patrón que `#contract-drawer` / `#bath-drawer`), id p. ej. **`#certificate-drawer`**.
- Contenido = el form actual de `dash-certificates-add.php`: `id_Cliente` (select con buscador), `id_Contrato` (cascade dependiente), `mts_Certificado`, `fecha_Servicio`, + el `fechahoy_Certificado` hidden. POST a `controller/certificate-new.php`.
- **Cascade Cliente → Contrato:** se mantiene `SelectEnhanced.cascade({ parent:'id_Cliente', child:'id_Contrato', endpoint:'controller/obtener_contratos.php', paramName:'idCliente' })`.
- **⚠️ Gotcha Choices.js (crítico):** ambos selects usan `data-enhanced-select` → Choices.js construye su propio DOM y **ignora las clases Tailwind del `<select>`**. El re-skin real vive scoped por id de drawer en `assets/css/tw/components.css` (~L716). **Hay que agregar `#certificate-drawer` a esa lista de ~8 selectores scoped** y correr `pnpm tw:build`, o los selects se ven con el tema crudo de Choices al desplegarse. (Ver memoria/AGENTS `§Selects con buscador (Choices.js) en drawers`.)

---

## 4. Impresión + corrección del PDF (`certificate-pdf.php`)

- **Wiring:** el botón "Imprimir" de la fila abre `certificate-pdf.php` en glightbox (igual que Servicios). No se navega a `dash-certificates-item.php`.
- **Fix del bug de imágenes:** reemplazar los `<img src="{ruta}">` embebidos en el HTML (logo_zl, logo_rc, firma) por llamadas nativas `$pdf->Image($ruta, $x, $y, $w, ...)` posicionadas manualmente, dejando en el `writeHTML()` solo el texto. Esto renderiza las imágenes bajo PHP-FPM (el `writeHTML` con `<img>` de filesystem no lo hace). Verificar en navegador que logo y firma aparecen.
- Quitar el `ob_start()/ob_end_clean()` innecesario si estorba, cuidando no romper el `Output(..., 'I')` inline.

---

## 5. Ajuste de redirects de controllers

- **`certificate-new.php`:** hoy redirige a `dash-certificates.php` en éxito (verificar) y probablemente a la add page en error. Ajustar **ambos** paths para volver siempre a `dash-certificates.php?status=...&msg=...` (o reabrir el drawer con flash), evitando el mismo bug que quedó pendiente en `service-new.php` (redirigir a una página huérfana en el path de error).
- **`certificate-remove.php`:** confirmar que redirige a `dash-certificates.php` con flash.

---

## 6. Huérfanos a registrar (Fase T5)

Al terminar la migración, agregar a `.doc/orfanatos-pendientes.md` un **cluster "Certificados"** siguiendo la convención existente:

- 🔴 `app/public/dash-certificates-add.php` — reemplazada por el drawer `?action=new`. Controller `certificate-new.php` **no** queda huérfano (lo reutiliza el drawer).
- 🔴 `app/public/dash-certificates-item.php` — reemplazada por el lightbox sobre `certificate-pdf.php`.
- **Puntos a tocar al limpiar:** entradas de estas 2 páginas en el `match` de `layouts/sidebar.php` y en el breadcrumb de `layouts/header.php` (localizar líneas exactas al momento de limpiar).

> No se registran como huérfanas **ahora**: siguen en uso hasta que la migración aterrice. T5 las registra recién cuando el reemplazo esté construido y probado (misma política que los clusters anteriores).

---

## 7. Fases / Orquestación secuencial (Orca)

Cada fase es una **tarea secuencial de orquestación Orca** (T N depende de N-1). Modelo por complejidad (ver `AGENTS.md § Orquestación de planes`): **Sonnet 5** = complejo/crítico, **GLM-5.2** = mediano, **MiniMax-M3** = rápido/repetitivo.

| Tarea | Fase — Entregable | Riesgo | Modelo (Orca) | Depende de |
|---|---|---|---|---|
| **T0** | Migrar `dash-certificates.php` a `native-table.js` (quitar DataTables, card redondeada, toolbar, acciones por fila) | Medio | GLM-5.2 | — |
| **T1** | Drawer de creación `?action=new` (form + cascade Cliente→Contrato) + `#certificate-drawer` en el re-skin Choices de `components.css` + `pnpm tw:build` | Medio | GLM-5.2 | T0 |
| **T2** | Ajustar redirects de `certificate-new.php` (éxito y error → lista con flash) | Bajo | MiniMax-M3 | T1 |
| **T3** | Impresión por lightbox: cablear el botón "Imprimir" a `certificate-pdf.php` con `data-glightbox-preview` | Bajo | MiniMax-M3 | T0 |
| **T4** | **Fix bug imágenes TCPDF** en `certificate-pdf.php` (`$pdf->Image()` nativo para logo_zl/logo_rc/firma; texto queda en `writeHTML`) | Medio (delicado) | **Sonnet 5** | T3 |
| **T5** | Eliminar con SweetAlert2 (revalidar `data-confirm-delete`) + flash de `certificate-remove.php` | Bajo | MiniMax-M3 | T0 |
| **T6** | Registrar cluster "Certificados" en `.doc/orfanatos-pendientes.md` (add + item + puntos sidebar/header) | Bajo | MiniMax-M3 | T1, T4 |
| **T7** | QA en navegador: crear (cascade + Choices), imprimir (**logo y firma visibles**), eliminar, buscar/paginar; verificar que no queda navegación a las 2 páginas huérfanas | Medio | Sonnet 5 | T2, T4, T5, T6 |

> Secuencial: T0 → T1 → T2 → T3 → T4 → T5 → T6 → T7. La columna "Depende de" marca la precedencia mínima real. **T4 (fix TCPDF) va en Sonnet 5** por ser la corrección más delicada (posicionamiento de imágenes nativo que hoy falla bajo PHP-FPM).

**QA (sin tests automatizados):** smoke test manual en `http://localhost` (`docker-compose restart php`) por tarea. Foco especial en T6: el bug de imágenes del PDF solo se ve al abrir el lightbox y mirar el logo/firma, y el tema roto de Choices solo se nota al **desplegar** el select dentro del drawer.

---

## 8. Consideraciones

- **Escape de outputs:** el listado ya escapa con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`; mantener en el drawer.
- **Read en la vista:** el `SELECT * FROM clientes` del form es una lectura en la vista (aceptable); se puede dejar como está para no ampliar alcance.
- **Consistencia visual:** reutilizar tokens/clases ya definidos (`table-card`, `dt-input`, `dt-select`, `dt-btn-add`, `dt-cell-action`) — no inventar estilos nuevos.
- **Cross-check obligatorio:** antes de tocar CSS/layout, diffear contra `dash-contracts.php` / `dash-services.php` (así se detectaron en migraciones previas `container-fluid` sin padding y `datatable.js` colgado).

## 9. Decisiones (confirmadas 2026-07-18)

1. **Edición:** ✅ **No se agrega.** Certificados queda como crear + imprimir + eliminar. El drawer cubre solo creación (no se toca el repositorio/interfaz para sumar `update`).
2. **Listado:** sin pills ni toggle grid/tabla (certificados no tiene estados) — patrón simple `native-table.js`.
3. **Impresión:** vía lightbox sobre `certificate-pdf.php` (no página full-screen), con corrección del bug de imágenes TCPDF.
4. **Huérfanos:** `dash-certificates-add.php` y `dash-certificates-item.php` se registran en `orfanatos-pendientes.md` al cerrar la migración (T5), no antes.
