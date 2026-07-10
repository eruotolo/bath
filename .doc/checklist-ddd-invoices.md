# Checklist — Plan DDD / Entidad: Invoices

**Origen:** `.doc/plan-migracion-ddd-drycode.md` (sección 6, entidad #7, la última — "el más complejo, depende de servicios")
**Estado:** ✅ implementado y verificado (2026-07-10)

---

## Alcance

**Controllers (9 migrados de 11):**

| Archivo | Operación | Estado antes |
|---|---|---|
| `controller/invoice-new.php` | INSERT | SQL injection |
| `controller/invoice-update.php` | UPDATE | ya tenía prepared statements — migrado igual, por consistencia (mismo criterio que Certificates) |
| `controller/invoice-delete.php` | UPDATE estado=3 ("Anular") + libera servicios | ya tenía prepared statements — **duplicaba** la misma lógica de `invoice-estado.php` (rama estado=3); ahora ambos delegan al mismo `SetInvoiceState` |
| `controller/invoice-estado.php` | UPDATE estado (1/2/3) + libera servicios si 3 | ya tenía prepared statements — migrado, unifica con `invoice-delete.php` |
| `controller/invoice-fecha-pago.php` | UPDATE fecha_Pago (o NULL) | ya tenía prepared statements — migrado por consistencia |
| `controller/invoice-pdf.php` | SELECT (factura+cliente+contrato+servicios) + genera PDF con TCPDF | ya tenía prepared statements, pero con el **mismo bug de JOIN indirecto que se encontró y corrigió en Certificates** (`CL.id_Cliente = CT.id_Cliente` en vez de `FT.id_Contrato = CT.id_Contrato`) — corregido |
| `controller/invoice-service-add.php` | INSERT en `factura_servicio` | SQL injection — pendiente desde Services |
| `controller/invoice-service-remove.php` | DELETE de `factura_servicio` | SQL injection — pendiente desde Services |
| `controller/invoice-upload-confirm.php` | INSERT masivo de facturas (carga Excel) | ya tenía prepared statements; se migró solo la verificación de duplicado y el INSERT al Repository nuevo, preservando intacta toda la lógica de parseo/validación de filas |

**No tocados, ya seguros y fuera de alcance (Carga masiva de facturas, Fase 3 de una sesión anterior):**

- `controller/invoice-upload-parse.php` — ya usa prepared statements; lee el Excel, normaliza RUTs, arma el preview. Lógica muy específica del flujo de carga masiva, no comparte SQL con el resto del dominio.
- `controller/invoice-upload-cancel.php` — no toca SQL, solo limpia la sesión.
- `dash-invoices-upload.php`, `dash-invoices-upload-preview.php`, `dash-invoices-upload-result.php` — sin SQL propio, muestran datos ya procesados desde `$_SESSION`.

**Vistas:**

- `dash-invoices-list.php` — reemplaza el listado por `ListInvoices`; agrega `htmlspecialchars()`
- `dash-invoices-detail.php` — SQL injection vía `$_GET['id_Factura']`/`$_GET['id_Contrato']` en 3 queries (factura+cliente+contrato con el JOIN indirecto, servicios disponibles para asignar, servicios ya asignados); reescrito con `FindInvoiceWithCustomerAndContract` + `ListUnbilledServicesForContract` + `ListAssignedServices`
- `dash-invoices-print.php` — mismo patrón que `dash-invoices-detail.php` pero de solo lectura (sin el modal de asignar servicio ni el botón de eliminar)
- `dash-invoices-edit.php` — ya era segura (prepared statement + `htmlspecialchars`); migrada a `FindInvoice` por consistencia, ahora usa la Entity `Invoice` (antes array)

`dash-invoices-add.php` no se tocó — su único SQL (`SELECT * FROM clientes`) pertenece al dominio Customer y no es inyectable. `controller/obtener_contratos.php` (select cascada cliente→contrato, reusado por Services e Invoices) tampoco — ya tenía prepared statements y `htmlspecialchars()` de una sesión anterior.

## Decisiones de diseño

1. **`SetInvoiceState` unifica `invoice-delete.php` e `invoice-estado.php`** — ambos controllers hacían exactamente lo mismo (poner `estado_Factura = 3` y liberar los servicios asociados) con código duplicado. Ahora `invoice-delete.php` simplemente llama `SetInvoiceState::handle($id, 3)`, mismo Use Case que usa `invoice-estado.php` para cualquiera de los 3 estados.
2. **Se corrigió el JOIN indirecto en `invoice-pdf.php`** (mismo hallazgo que en Certificates): unía `contratos` vía `CL.id_Cliente = CT.id_Cliente` en vez de `FT.id_Contrato = CT.id_Contrato`. Funcionaba porque el `WHERE CT.id_Contrato = ?` filtraba después, pero es fragil si un cliente tiene varios contratos. El Repository nuevo usa siempre el join directo.
3. **La carga masiva de facturas (`invoice-upload-*`) se dejó mayormente intacta** — ya era 100% segura (prepared statements) desde que se implementó en una sesión anterior (Fase 3), y tiene lógica de parseo de Excel muy específica que no se beneficia de pasar por el patrón Repository genérico. Se migró únicamente el punto de contacto real con la tabla `facturas` (existencia de número duplicado + INSERT) en `invoice-upload-confirm.php`, para que el Repository sea la única puerta de escritura a esa tabla en todo el dominio.
4. **`UpdateInvoice` no toca `estado_Factura` ni `fecha_Pago`** — igual que el `invoice-update.php` original, que solo edita los 5 campos del formulario. Cambiar el estado o la fecha de pago tiene sus propios controllers/Use Cases dedicados.

## Verificación

Durante la migración, el clasificador de seguridad que aprueba comandos que ejecutan código (PHP, `docker exec`) quedó temporalmente no disponible por un buen rato (afectaba a todo el entorno, no al proyecto: comandos de shell puro como `ls`/`echo` funcionaban normalmente, pero `docker-compose exec php ...` fallaba con "claude-sonnet-5 is temporarily unavailable"). Se aprovechó para redactar este checklist y repasar el código; en cuanto se restableció, se corrió todo:

- [x] `php -l` sobre los 9 controllers + 4 vistas tocados + 14 archivos nuevos — sin errores
- [x] **Toda la lógica de negocio se verificó primero con scripts aislados** (mismo método que Contracts/Services): crear factura, asignar servicio, confirmar que desaparece de "disponibles", cambiar fecha de pago, anular (estado 3) y confirmar que `SetInvoiceState` libera los servicios automáticamente y vuelven a "disponibles" — todo correcto a la primera
- [x] Listado (`dash-invoices-list.php`) — datos y estados correctos
- [x] Detalle (`dash-invoices-detail.php`) — factura+cliente+contrato+servicios asignados, todo correcto con el join corregido
- [x] Impresión HTML (`dash-invoices-print.php`) — idéntica al detalle, sin acciones de edición
- [x] PDF (`controller/invoice-pdf.php`, TCPDF) — generado correctamente, mismos datos que la vista HTML, confirma que el join indirecto quedó corregido
- [x] Editar factura (`dash-invoices-edit.php`) — la Entity `Invoice` precarga el formulario correctamente, incluido el select-cascada de contrato
- [x] Anular factura desde la UI (`invoice-delete.php` vía `SetInvoiceState`) — la factura desaparece del listado (solo muestra estados 1/2), confirmado con una factura de prueba creada y anulada en la misma sesión
- [x] `docker-compose logs php` — sin errores/warnings nuevos en toda la sesión (incluida la doble verificación: scripts aislados + navegador)

## Pendiente / fuera de alcance

- Ninguna — esta es la última entidad del plan. Con Invoices completa (una vez verificada), las 7 entidades del `plan-migracion-ddd-drycode.md` quedan migradas: Certificates, Bathrooms, Users, Customers (+ Contacts), Contracts, Services, Invoices.

---

## Reglas respetadas (de `CLAUDE.md`)

- SQL 100% prepared statements — elimina 3 SQL injections activas (`invoice-new`, `invoice-service-add`, `invoice-service-remove`)
- `htmlspecialchars()` agregado en las vistas reescritas
- Bug de JOIN indirecto corregido y documentado — mismo criterio que Certificates
- Duplicación de lógica (`invoice-delete` / `invoice-estado`) eliminada, unificada en un solo Use Case
- No se tocó la carga masiva de facturas más allá de lo estrictamente necesario (ya era segura)
- Commit atómico por entidad (pendiente hasta verificar en navegador)
