# Checklist — Fase 1: Ajustes rápidos de listados y validaciones

**Origen:** `.doc/cotizacion-mejoras-julio-2026.md` (ítems 1-6) + `.doc/tecnico-mejoras-julio-2026.md`
**Estado:** ✅ Fase 1 completa (ítems 1-7) — implementado y verificado en navegador (2026-07-08).

---

## Prerrequisito transversal

- [ ] Migración: agregar `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` a `certificados`, `facturas`, `servicios` (contratos queda para Fase 2)
- [ ] Definir si se documenta la migración en `mysql/migrations/` o se aplica a mano y se anota en README (fuera de alcance original, pero a decidir)

## 1. Certificados: orden y botón de impresión — ✅ implementado y verificado en navegador (2026-07-08)

- [x] Migración `created_at` en `certificados` (aplicada contra la DB de desarrollo)
- [x] `dash-certificates.php`: `ORDER BY CR.created_at DESC, CR.id_Certificado DESC` — **el fallback por `id_Certificado` es obligatorio**: la migración deja el mismo `created_at` en los 660 registros existentes (todos migrados en el mismo instante), así que sin el fallback el `ORDER BY` no discrimina nada entre ellos
- [x] **Bug encontrado y corregido durante QA en navegador:** DataTables aplica sort por defecto (columna 0 ascendente) al inicializar, pisando el orden que viene del SQL. Llamar `.order([]).draw()` *después* de `$(...).DataTable({...})` NO alcanza — hay que pasar `order: []` directamente dentro del constructor. Se reemplazó el include del script compartido `assets/js/pages/datatables.init.js` por un init inline propio de esta vista (mismo patrón que ya usan `dash-bathrooms.php`/`dash-invoices-list.php`), con `order: []` explícito
- [x] Botón "Imprimir" nuevo en columna Acciones, junto a "Ver"/"Eliminar" → enlaza a `dash-certificates-item.php?id_Certificado=...&id_Contrato=...` con `target="_blank"` (esa página ya tiene `window.print()`, no se creó vista nueva)
- [x] Confirmado: `controller/certificate-remove.php` no se toca ni se ve afectado
- [x] Decisión tomada: `target="_blank"` para no perder el listado
- [x] Verificado en navegador (Chrome, sesión real): listado muestra `#06072026A3, #06072026A2, #06072026A1, ...` (más reciente primero), botón imprimir presente y funcional
- [ ] **Pendiente:** revisar visualmente el resto de las 27 vistas que usan el script compartido `assets/js/pages/datatables.init.js` (`dash-customers.php`, `dash-contracts.php`) — mismo riesgo de sort-por-defecto si en el futuro se les agrega `created_at`

## 2. Facturas: orden por fecha de creación — ✅ implementado y verificado en navegador (2026-07-08)

- [x] Migración `created_at` en `facturas` (aplicada contra la DB de desarrollo)
- [x] `dash-invoices-list.php`: `ORDER BY FT.created_at DESC, FT.id_Factura DESC` (mismo fallback que certificados, por la misma razón: todas las facturas existentes quedaron con `created_at` idéntico tras la migración)
- [x] Esta vista SÍ tenía script inline propio (a diferencia de certificados) con `order: [[1, "desc"]]` forzando sort por columna "Fecha Factura" — se reemplazó por `order: []` directamente en el constructor, aplicando la misma lección aprendida en el ítem 1
- [x] Verificado en navegador: listado muestra `#1896, #1897, #1848, #1891, ...` (orden por `id_Factura DESC`, ya que todas comparten `created_at`)

## 3. Servicios: orden por fecha de creación — ✅ implementado y verificado en navegador (2026-07-08)

- [x] Migración `created_at` en `servicios` (aplicada contra la DB de desarrollo)
- [x] `dash-services.php`: `ORDER BY SR.created_at DESC, SR.id_Servicio DESC` (mismo fallback que en los ítems 1 y 2)
- [x] Esta vista también tenía script inline propio con `order: [[4, "desc"]]` (columna Fecha Seguimiento) — reemplazado por `order: []` en el constructor
- [x] Verificado en navegador y contra la base de datos: primeras filas `id_Servicio 2754, 2753, 2752, 2751, 2750` (orden descendente), coincide exactamente con lo mostrado en pantalla

## 4. Nuevo Servicio: cliente en orden alfabético — ✅ implementado y verificado en navegador (2026-07-08)

- [x] `dash-services-add.php`: `SELECT * FROM clientes ORDER BY nombre_Cliente ASC`
- [x] Verificado en navegador: el select ahora lista "AGONI CONSTRUCCIONES LIMITADA, AGONI CONSTRUCCIONES LTDA, APIA SPA, ARIDOS MARDONES, ..." en orden alfabético

## 5. Nuevo Servicio: tipo "Retiro de Baños" — ✅ implementado y verificado end-to-end (2026-07-08)

- [x] Migración: `ALTER TABLE tipo_servicio ADD COLUMN retiro_Tipo TINYINT(1) DEFAULT 0`
- [x] `dash-services-add.php`: checkbox nuevo, mismo patrón que los 8 tipos existentes
- [x] `controller/service-new.php`: variable + columna al INSERT
- [x] `dash-services-edit.php` + `controller/service-update.php`: checkbox con estado `checked` reflejando el valor guardado + columna al UPDATE
- [x] `dash-services-print.php`: nuevo `<li>Retiro de Baños</li>` condicional, mismo patrón que los otros 8 tipos
- [x] **Prueba end-to-end real:** se creó un servicio de prueba con "Retiro de Baños" marcado → verificado en DB (`retiro_Tipo = 1`) → verificado que el formulario de edición lo muestra tildado → registro de prueba eliminado al finalizar
- [ ] Vista de impresión (`dash-services-print.php`) verificada solo por revisión de código (requiere que el servicio tenga baños asignados vía `dash-services-bath.php`, paso fuera de alcance de este ítem) — recomendable que Edgardo la revise con un servicio real que tenga baños asignados

## 6. Nuevo Baño: código único — ✅ implementado y verificado end-to-end (2026-07-08)

- [x] **Bloqueante ya no aplica:** se verificó `codigo_Bath` actual (100 registros, 100 códigos distintos) — el duplicado `AT060` que reportaba el documento técnico ya no existe en la base de desarrollo actual
- [x] Migración: `ALTER TABLE bathrooms ADD UNIQUE KEY codigo_Bath_unique (codigo_Bath)`
- [x] `controller/bath-new.php`: validación con prepared statement (`SELECT COUNT(*) WHERE codigo_Bath = ?`) antes del INSERT + mensaje de error amigable (alert + redirect al formulario) — de paso se migró el INSERT a prepared statement (ya que se estaba tocando el archivo)
- [x] `controller/bath-update.php`: misma validación, excluyendo el propio `id_Bath` (`AND id_Bath != ?`) para no marcar como duplicado el propio código sin cambios — de paso se migró el UPDATE a prepared statement
- [x] **Prueba end-to-end real (4 casos):** (1) crear con código duplicado → bloqueado, sin insert; (2) crear con código único → éxito; (3) editar cambiando a un código de otro baño → bloqueado, sin update; (4) editar sin cambiar el código → éxito, sin falso positivo. Los 4 verificados contra la base de datos real
- [x] Registro de prueba eliminado (confirmado por Edgardo, DB de testing)

## 7. Baños: "No Asignado" → "Disponible" — ✅ implementado y verificado en navegador (2026-07-08)

- [x] Nueva clase CSS `.item-disponible` (azul `#3B82F6`) agregada a `assets/css/style.css`, junto a `.item-activo`/`.item-inactivo` existentes
- [x] Decisión de Edgardo: "Disponible" aplica por consistencia en **las 3 vistas**, sin excepción de contexto
- [x] `dash-bathrooms.php`: badge `No Asignado` → `Disponible` (clase `item-disponible`)
- [x] `dash-contracts-item.php`: mismo cambio en la columna "Asignado" del detalle de obra
- [x] `dash-bathrooms-contracts.php`: mismo cambio (este archivo cambia de rol/nombre a "Histórico" en Fase 2, pero el contenido no se toca en ese momento — ver checklist Fase 2)
- [x] **Bug de caché detectado y resuelto durante QA:** el navegador mostraba la columna en blanco tras el cambio — no era un bug de código, el CSS estático (`style.css`) estaba cacheado por el navegador desde antes del cambio. Con hard-reload se ve correcto. Verificado con `fetch(..., {cache:'no-store'})` que el HTML/CSS servido por el server siempre fue correcto
- [x] Verificado en navegador: `dash-bathrooms.php` muestra "Disponible" en azul junto a "Asignado" en verde; `dash-contracts-item.php` y `dash-bathrooms-contracts.php` revisados (en los datos de prueba disponibles, todas las filas de estas dos últimas vistas están asignadas, no se pudo ver un caso real de "Disponible" ahí, pero el código es idéntico al de `dash-bathrooms.php` que sí se vio funcionando)

---

## Reglas a respetar durante la implementación (de `CLAUDE.md`)

- SQL nuevo o tocado → siempre prepared statements (`prepare()` + `bind_param()`), nunca interpolación
- Vistas (`dash-*.php`) tocadas → verificar `htmlspecialchars()` en los outputs que se agreguen/modifiquen
- Mantener estilo procedural, `snake_case`, sin introducir clases
- Commit atómico por ítem (no por archivo ni por fase completa)
- No commitear sin pedido explícito

---

## Notas / cambios pendientes de agregar por el usuario

*(espacio para que Edgardo sume o modifique ítems antes de arrancar)*

-
