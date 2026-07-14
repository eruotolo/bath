# Checklist — Roadmap interno / Mejoras frontend / Sprint 1: Quick wins

**Origen:** `.doc/Viejos/plan-mejoras-frontend.md` (sección 11, Sprint 1) — roadmap interno, no cotizado al cliente
**Estado:** ✅ implementado y verificado en navegador (2026-07-08)

---

## Alcance de este sprint

Los 3 quick wins del plan (sección 11.1):
1. `feedback.js` (SweetAlert2) → reemplazar los `alert()` en controllers + confirmación antes de borrar
2. `form-validator.js` (PristineJS) → validación en español en los forms principales
3. `rut-mask.js` → formato y validación de RUT chileno en los inputs de RUT

## Decisiones tomadas antes/durante de codear

- **RutMask no usa la librería IMask** (desviación del plan original): el RUT chileno tiene largo variable (7-8 dígitos de cuerpo) + dígito verificador que puede ser la letra `K`, algo que el motor de máscaras declarativas de IMask no resuelve bien. Se implementó formateo + validación módulo 11 a mano (~80 líneas), documentado como nota técnica en el header de `rut-mask.js`. No se agregó ninguna dependencia nueva.
- **No se usó el atributo `data-validate`** que proponía el plan como marcador de formularios a validar. En su lugar, `form-validator.js` se auto-inicializa sobre el selector `form.needs-validation`, que ya estaba presente (pero muerto) en 10 de los 12 forms reales del sistema. Se evitó tocar cada vista solo para agregar un atributo redundante.
- **`Feedback` y `RutMask` se auto-inicializan en `DOMContentLoaded`** y quedan incluidos globalmente en `layouts/vendor-scripts.php` (se cargan en todas las vistas). No hace falta agregar un `<script>` por vista.
- **Confirmación de borrado sin AJAX/POST:** el plan proponía convertir los links de eliminar en botones con `fetch POST`. Se descartó: los controllers `bath-delete.php` / `invoice-delete.php` solo leen `$_GET`, y migrarlos a POST es un cambio de backend fuera de alcance de un plan que se definió como "JS/CSS puros, cero cambios de backend" (sección 13 del plan). En cambio, `data-confirm-delete` intercepta el click, muestra el confirm de SweetAlert2, y solo si el usuario confirma navega al mismo `href` de siempre (GET). Mismo resultado para el usuario, cero cambios en los controllers de borrado.
- **Feedback de éxito (`status=success`) solo se agregó a los 14 controllers que ya se tocaban** por tener el `alert()` roto (punto 1 del sprint). El resto de los controllers del sistema, que ya redirigían "silenciosamente" sin alert() y sin errores, no se tocaron — quedan sin toast de éxito. Se puede sumar en una pasada posterior si se quiere feedback en el 100% de las operaciones.

## Hallazgos técnicos durante la implementación (2 bugs de integración corregidos vía QA en navegador)

1. **Faltaba `sweetalert2.min.css`.** Solo se había agregado el `.js` a `vendor-scripts.php`. El modal se armaba correctamente en el DOM (`swal2-popup`, clases correctas) pero sin su hoja de estilos quedaba invisible/mal posicionado. Se agregó `assets/libs/sweetalert2/sweetalert2.min.css` en `layouts/head-style.php`. Se detectó recién al verificar visualmente en el navegador — el `Swal.fire()` no tira ningún error en consola aunque falte el CSS.
2. **Desajuste entre PristineJS y las clases de validación de Bootstrap 5.** La config `classTo: 'mb-4'` hace que Pristine agregue `errorClass`/`successClass` al contenedor (`div.mb-4`), no al `<input>`. Pero el CSS de Bootstrap 5 para `.invalid-feedback` solo se activa cuando `.is-invalid` es **hermano directo** del input, no cuando es un ancestro. Resultado: los mensajes de error se insertaban en el DOM (confirmado por JS) pero no se veían nunca. Se agregaron 3 reglas puntuales en `assets/css/style.css` (`.mb-4.is-invalid .form-control`, `.mb-4.is-invalid .invalid-feedback`, `.mb-4.is-valid .form-control`) para puentear esto sin reescribir la config de Pristine.

## Archivos nuevos

- `app/public/assets/js/components/feedback.js` — `Feedback.toast/success/error/confirm` + listener automático de `?status=&msg=` en la URL + binding de `[data-confirm-delete]`
- `app/public/assets/js/components/form-validator.js` — wrapper de PristineJS en español, auto-init sobre `form.needs-validation`
- `app/public/assets/js/components/rut-mask.js` — formato `12.345.678-9` + validación módulo 11 a mano, auto-init sobre `[data-rut-mask]`

## Archivos tocados

**Includes globales:**
- `app/public/layouts/vendor-scripts.php` — agrega `sweetalert2.min.js`, `pristine.min.js` y los 3 componentes nuevos
- `app/public/layouts/head-style.php` — agrega `sweetalert2.min.css`
- `app/public/assets/css/style.css` — reglas de compatibilidad Pristine/Bootstrap 5 (ver hallazgo técnico #2)

**Controllers (14) — alert() roto reemplazado por `header(Location: ...?status=&msg=...)`, con toast de éxito agregado en el mismo header exitoso:**
`contract-new.php`, `invoice-estado.php`, `customer-new.php`, `invoice-delete.php`, `bath-new.php`, `bath-update.php`, `contact-new.php`, `customer-update.php`, `contract-update.php`, `contact-update.php`, `user-new.php`, `service-update.php`, `bath-delete.php`, `service-new.php`

**Vistas — `data-confirm-delete` en los 2 links de eliminar reales:**
- `dash-bathrooms.php`
- `dash-invoices-list.php` (de paso: el título del botón decía "Eliminar Factura" pero el controller solo anula/cambia estado — se corrigió a "Anular Factura")

**Vistas — `needs-validation` agregado donde faltaba (para que `form-validator.js` las tome):**
- `dash-bathrooms-edit.php`
- `dash-services-edit.php`

**Inputs de RUT — `type="number"` → `type="text"` + `data-rut-mask` (5 de los 6 estimados en el plan; no se encontró un 6to input real, solo texto de solo-lectura sin `<input>` en `dash-customers-item.php`):**
- `dash-customers-add.php`
- `layouts/modal-editar-contacto.php`
- `layouts/modal-nuevo-contacto.php`
- `layouts/modal-edit-customer.php`
- `layouts/modal-ver-contacto.php` (readonly — solo formateo visual, sin validación de blur)

## Checklist

- [x] Cero `alert(` en `app/public/controller/` (verificado con grep, 0 resultados)
- [x] Confirmación (SweetAlert2) antes de los 2 borrados reales del sistema (baños, facturas)
- [x] PristineJS inicializado en los 12 forms principales (10 que ya tenían `needs-validation` + 2 agregados), mensajes en español, verificado visualmente en navegador
- [x] RUT chileno con formato automático y validación de dígito verificador (módulo 11) en los 5 inputs reales, verificado en navegador con RUT válido e inválido
- [x] Toast de éxito + modal de error vía query string (`?status=success|error&msg=...`), con limpieza de la URL después de mostrarlo
- [x] **Verificado en navegador real** (sesión de Chrome de Edgardo, ya autenticado): validación de formulario vacío, formateo + validación de RUT, modal de confirmación de borrado, toast de éxito y modal de error — los 2 bugs de integración (CSS de SweetAlert2 faltante, clases de Pristine vs Bootstrap 5) se encontraron y corrigieron en este paso
- [x] Ningún dato real se modificó durante la verificación (el borrado de baño se probó y se canceló, no se confirmó)

## Pendiente / fuera de alcance de este sprint

- El RUT en los modales `modal-edit-customer.php` y `modal-ver-contacto.php` se puebla vía AJAX (`customer-edit.js`, `contactoVer.js`) con `.val(...)`, que no dispara el evento `input` — el auto-formateo de `rut-mask.js` no se aplica hasta que el usuario edita el campo a mano. Se resuelve naturalmente en el Sprint 4 (modal de edición unificado).
- Toast de éxito no está en el 100% de los controllers, solo en los 14 tocados por este sprint (ver "Decisiones tomadas").
- Sprint 2 (DataTable potenciado), Sprint 3 (Choices.js / selects con buscador) y Sprint 4 (datepickers, modal unificado, Glightbox, Dropzone) siguen pendientes.

---

## Reglas a respetar durante la implementación (de `CLAUDE.md`)

- SQL no se tocó en este sprint (es JS/CSS + output layer de los controllers) — cumplido
- Vistas tocadas → sin cambios de output no escapado nuevo — cumplido
- Sin Composer, sin npm, sin librerías nuevas más allá de las ya vendorizadas en `assets/libs/` — cumplido
- Estilo procedural, `snake_case`, sin introducir clases PHP — cumplido
- No se commiteó nada sin pedido explícito
