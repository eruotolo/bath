# Informe de avance — Roadmap interno: mejoras frontend

**Proyecto:** php-bathroom (Blanco Servicios e Inversiones SPA)
**Referencia:** `.doc/plan-mejoras-frontend.md` — primer ítem del roadmap interno (frontend → DDD → PHP 8.5), **no forma parte de la cotización del cliente**
**Última actualización:** 08-07-2026
**Estado general:** Sprint 1 de 4 completo — probado en el ambiente de testing.

---

## Resumen de avance

| Sprint | Contenido | Estado |
|---|---|---|
| Sprint 1 — Quick wins | Feedback (SweetAlert2), validación de formularios (PristineJS), máscara de RUT | ✅ Completo |
| Sprint 2 — Tabla potenciada | `datatable.js` compartido, selección múltiple, filtros por columna, persistencia de estado | Pendiente |
| Sprint 3 — Selects con buscador | Choices.js, `select_options.php`, selects en cascada | Pendiente |
| Sprint 4 — Date pickers + extras | Flatpickr, modal de edición unificado, Glightbox, Dropzone | Pendiente |

---

## Sprint 1 — Quick wins

### Feedback visual (reemplazo de `alert()`)
Los 14 controllers que usaban `alert()` nativo del navegador para avisar errores ahora redirigen con un mensaje que se muestra como un cuadro de diálogo prolijo (SweetAlert2), en vez del cartel gris del navegador. Cuando una operación se completa bien (crear/editar cliente, baño, contrato, servicio, factura, etc.), ahora aparece además un aviso de confirmación en la esquina de la pantalla — antes no había ningún aviso de éxito, la página simplemente cambiaba sin indicar que la carga funcionó.

### Confirmación antes de borrar
Antes, los botones de eliminar (baños) y anular (facturas) actuaban al primer click, sin ningún tipo de confirmación — un click accidental eliminaba el registro sin posibilidad de arrepentirse. Ahora ambos piden confirmación explícita antes de ejecutar la acción.

### Validación de formularios en español
Los 12 formularios principales (alta y edición de clientes, baños, contratos, servicios, facturas, certificados y usuarios) ahora validan los campos obligatorios antes de enviar el formulario, mostrando el mensaje de error junto a cada campo en español. Antes, la validación no funcionaba en absoluto (el sistema tenía la clase CSS puesta pero nunca se inicializaba), así que un campo vacío se enviaba igual y el error aparecía recién del lado del servidor.

### RUT chileno con formato automático
Los 5 campos de RUT del sistema (alta de cliente, contactos, edición de cliente) ahora formatean automáticamente mientras se escribe (`12.345.678-9`) y validan el dígito verificador al salir del campo, marcando en rojo si está mal. Antes eran campos numéricos comunes, sin formato ni validación, y ni siquiera aceptaban el dígito verificador `K`.

---

## Pendiente

- **Sprint 2, 3 y 4** del plan de mejoras frontend.
- El RUT no se auto-formatea todavía cuando se abre desde los modales de edición/vista rápida de cliente y contacto (el dato llega por una carga automática que no dispara el formateo) — se corrige solo cuando se unifique el modal de edición en el Sprint 4.

---

*Este documento es de uso interno — a diferencia de `avance-mejoras-julio-2026.md`, no corresponde a trabajo cotizado ni se envía al cliente. Detalle técnico completo en `.doc/checklist-frontend-sprint1-quickwins.md`.*
