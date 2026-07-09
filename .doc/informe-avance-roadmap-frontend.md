# Informe de avance — Roadmap interno: mejoras frontend

**Proyecto:** php-bathroom (Blanco Servicios e Inversiones SPA)
**Referencia:** `.doc/plan-mejoras-frontend.md` — primer ítem del roadmap interno (frontend → DDD → PHP 8.5), **no forma parte de la cotización del cliente**
**Última actualización:** 09-07-2026
**Estado general:** Sprint 1, 2, 3 y 4 de 4 completos — plan de mejoras frontend cerrado, probado en el ambiente de testing.

---

## Resumen de avance

| Sprint | Contenido | Estado |
|---|---|---|
| Sprint 1 — Quick wins | Feedback (SweetAlert2), validación de formularios (PristineJS), máscara de RUT | ✅ Completo |
| Sprint 2 — Tabla potenciada | `datatable.js` compartido, filtros por columna, persistencia de estado | ✅ Completo |
| Sprint 3 — Selects con buscador | Choices.js, selects en cascada cliente→contrato | ✅ Completo |
| Sprint 4 — Date pickers + extras | Flatpickr, modal de edición unificado, Glightbox, Dropzone | ✅ Completo |

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

## Sprint 2 — Tabla potenciada

### Filtros por columna
Las tablas de facturas y de servicios/seguimientos (esta última con más de 1600 filas) ahora tienen un buscador individual arriba de cada columna, además del buscador general que ya existía — mucho más rápido para encontrar, por ejemplo, todas las facturas de un cliente puntual sin tener que revisar página por página.

### Los filtros y el orden se mantienen al recargar la página
Antes, si se ordenaba o filtraba una tabla y se recargaba la página (o se volvía después de entrar a otra pantalla), se perdía todo y había que volver a aplicarlo. Ahora se mantiene, en las 4 tablas principales (baños, baños con contrato, servicios y facturas).

### Un solo motor de tablas para las 4 vistas
Antes cada una de las 4 tablas principales tenía su propia configuración copiada y pegada por separado. Ahora comparten un mismo componente, así que un ajuste futuro (por ejemplo, agregar una función nueva a todas las tablas) se hace en un solo lugar en vez de cuatro.

*No se tocaron los filtros rápidos de Estado/Asignado del listado de baños (agregados días atrás) — se verificó que siguen funcionando igual.*

---

## Sprint 3 — Selects con buscador

### Buscador en los selectores de cliente, contrato, servicio y otros catálogos
Los 13 selectores de catálogo del sistema (elegir cliente, contrato, servicio, región, estado, baño a asignar) ahora tienen buscador integrado en vez de ser una lista larga para desplazarse a mano — importante sobre todo en el de clientes, que hoy tiene cerca de 90.

### Selección de cliente → contrato más confiable
Al crear una factura, un certificado o un servicio, elegir el cliente sigue auto-completando el segundo selector con solo los contratos de ese cliente (esto ya existía). Al revisar el código para agregarle el buscador, se encontraron **2 pantallas donde ese autocompletado estaba roto** (nueva factura y nuevo certificado) por un error de tipeo en el código que impedía que el script corriera — se corrigió de paso.

### Un archivo cargando internet de más
La pantalla de nueva factura cargaba una copia de una librería (jQuery) desde internet, además de la que ya tiene el sistema instalada localmente — sin necesidad, y con el riesgo de que la pantalla se rompa si en algún momento no hay conexión a ese sitio externo. Se eliminó esa carga de más.

---

## Sprint 4 — Date pickers + modal unificado + previsualización de PDF + Dropzone

### Fechas consistentes en todos los formularios
Los campos de fecha del sistema (contratos, baños, servicios, facturas, certificados) ahora usan un mismo selector de fecha en español, con un calendario visual en vez de depender del control nativo de cada navegador (que en Safari se veía distinto que en Chrome). Además, "Fecha del Servicio" al crear un servicio nuevo no permite elegir una fecha pasada.

### Reemplazo del "Imprimir" de facturas y certificados por una vista previa en PDF
Antes, el botón "Imprimir" de facturas y certificados abría una copia completa del sistema (con el menú lateral y todo) en una pestaña nueva, pensada para usar el comando de imprimir del navegador. Ahora genera un PDF real con el mismo formato (logos, datos del cliente, detalle de servicios o del certificado) y lo muestra en una vista previa dentro de la misma pantalla, con las herramientas de imprimir/descargar del propio visor de PDF del navegador — sin salir del listado.

### Carga de foto de perfil con arrastrar y soltar
Al crear o editar un usuario, la foto de perfil ahora se puede arrastrar directamente a la pantalla (con una vista previa antes de guardar), en vez de usar el selector de archivos genérico del sistema operativo.

### Detalle técnico relevante
Durante la implementación aparecieron dos problemas de infraestructura que no eran evidentes desde el plan original: la librería de vista previa (Glightbox) tenía un comportamiento no documentado que hubo que resolver por prueba directa en el navegador, y el servidor no tenía instalada la librería de imágenes (GD) que necesita la generación de PDF — se agregó al `Dockerfile`. **Este último cambio hay que replicarlo en el servidor de producción/testing antes de que ese ambiente use la vista previa de PDF**, si no la generación va a fallar igual que falló acá antes de instalarla.

## Pendiente

- El RUT no se auto-formatea todavía cuando se abre desde los modales de edición/vista rápida de cliente y contacto (el dato llega por una carga automática que no dispara el formateo).
- Quedaron 3 listados (usuarios, clientes, contratos) usando el motor de tablas viejo — no estaban en el alcance original del Sprint 2, se pueden migrar en una pasada aparte.
- No se centralizaron las consultas de los selects en un helper único — tiene más sentido hacerlo junto con el refactor DDD, para no duplicar el trabajo.
- `controller/bath-get.php` quedó sin uso (el modal de edición de baño se reemplazó por una página completa) — a definir si se elimina.
- Las páginas viejas de impresión (`dash-invoices-print.php`, `dash-certificates-item.php` en su rol de "Imprimir") quedaron sin enlazar desde ningún listado, pero no se borraron.

---

*Este documento es de uso interno — a diferencia de `avance-mejoras-julio-2026.md`, no corresponde a trabajo cotizado ni se envía al cliente. Detalle técnico completo en `.doc/checklist-frontend-sprint1-quickwins.md`, `.doc/checklist-frontend-sprint2-datatable.md`, `.doc/checklist-frontend-sprint3-selects.md` y `.doc/checklist-frontend-sprint4-datepicker-modal-glightbox-dropzone.md`.*
