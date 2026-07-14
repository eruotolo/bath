# Orfanatos pendientes de limpieza

Registro vivo de archivos que quedan huérfanos o semi-huérfanos a medida que avanza el rediseño de la app. La idea es acumular acá y hacer una limpieza por lotes cuando el resto de las páginas involucradas estén migradas al nuevo diseño.

**Convención de estados:**
- 🔴 **Huérfano** — ningún archivo del proyecto lo referencia/usa. Seguro de borrar.
- 🟡 **Semi-huérfano** — referenciado solo por otros archivos que también son candidatos a borrar (cascada).
- 🟢 **Pendiente de decisión** — se usa hoy pero se sabe que va a salir del diseño nuevo.

---

## Cambios aplicados

### 2026-07-14 — Eliminado botón "Ver ficha cliente" (ojo) en `dash-customers.php`

- **Hecho:** se eliminó el `<a>` con ícono `eye` en la columna Acciones del listado de clientes (`app/public/dash-customers.php`, era líneas 224-230).
- **Motivo:** el nuevo diseño ya no usa el botón de visualización en el listado.
- **Quedó:** solo el botón de Editar (`square-pen`) en esa columna.
- Los enlaces del **RUT** (línea 182) y del **Nombre** (línea 187) siguen apuntando a `dash-customers-item.php` — **no se tocaron** porque la ficha sigue existiendo por ahora.

---

## Cluster "ficha de cliente" (candidato a limpieza futura)

Origen: la página `dash-customers-item.php` (ficha de detalle del cliente) va a desaparecer del nuevo diseño. Hoy es la **única UI** para el CRUD de **contactos** del cliente y para la edición de cliente vía modal. Cuando el nuevo diseño cubra ese flujo, todo este cluster queda eliminable.

**No borrar todavía** — esperar a que las páginas nuevas involucren/reemplacen este flujo.

### Página principal
- 🟢 `app/public/dash-customers-item.php` — la ficha. Referenciada desde `dash-customers.php` (RUT, Nombre), `layouts/sidebar.php:36` (match menú), `layouts/header.php:9` (breadcrumb) y 4 controllers que redirigen a ella.

### Modales (solo los incluye `dash-customers-item.php`)
- 🟡 `app/public/layouts/modal-edit-customer.php` → POST a `controller/customer-update.php`
- 🟡 `app/public/layouts/modal-nuevo-contacto.php` → POST a `controller/contact-new.php`
- 🟡 `app/public/layouts/modal-ver-contacto.php`
- 🟡 `app/public/layouts/modal-editar-contacto.php` → POST a `controller/contact-update.php`

### Controllers que redirigen a la ficha (al borrarla, hay que re-apuntarlos o borrarlos)
- 🟡 `app/public/controller/customer-update.php` — editar cliente (nadie más lo llama fuera del modal)
- 🟡 `app/public/controller/customer-get.php` — endpoint AJAX, solo lo usa la ficha
- 🟡 `app/public/controller/contact-get.php` — endpoint AJAX, solo lo usa la ficha
- 🟡 `app/public/controller/contact-new.php` — crear contacto
- 🟡 `app/public/controller/contact-update.php` — editar contacto
- 🟡 `app/public/controller/contact-remove.php` — eliminar contacto

### Otros puntos a tocar al limpiar
- `app/public/layouts/sidebar.php:36` — quitar `'dash-customers-item.php'` del array `match`.
- `app/public/layouts/header.php:9` — quitar entrada del breadcrumb.
- `app/public/dash-customers.php:182` y `:187` — los enlaces de RUT/Nombre (convertir a texto plano o re-apuntar).
