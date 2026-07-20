# Plan — Homologación de buscadores y acciones de listados

**Estado:** planificado. No implementado.

**Objetivo:** unificar la franja de acciones de los listados para que, en escritorio, el buscador quede a la izquierda y las acciones queden a la derecha en este orden: **Excel · PDF · acción secundaria existente (si aplica) · Agregar nuevo**. La adaptación debe conservar filtros, drawers, ordenamientos, paginación y permisos actuales.

**Convención de producto:** el botón visible se llamará **Excel**, pero descargará un CSV UTF-8 con BOM y separador `;`, igual que los reportes de clientes y baños ya existentes. No se incorpora una librería XLSX. El botón **PDF** generará un listado descargable con TCPDF; no reemplaza los PDF/impresiones individuales ya existentes.

## 1. Hallazgos del estado actual

- `dash-customers.php`, `dash-bathrooms-contracts.php` y `dash-bathrooms-contracts-status.php` ya materializan el patrón completo: buscador a la izquierda y exportaciones CSV/PDF más alta en la derecha. Los dos últimos lo hacen mediante `table_native_export_buttons()`.
- `dash-bathrooms.php` tiene el buscador a la izquierda y el alta a la derecha, pero **no** tiene los botones Excel/PDF. Por lo tanto no es aún una referencia completa, aunque se lo haya considerado así inicialmente.
- `dash-contracts.php`, `dash-services.php` y `dash-invoices-list.php` ya tienen el buscador a la izquierda y conservan sus filtros/controles propios; les faltan exclusivamente los dos botones de exportación.
- `dash-certificates.php` y `dash-users-list.php` ya tienen un buscador funcional de `native-table.js`, pero se ubica a la derecha, junto al título y a la acción de alta. Les faltan las exportaciones y el buscador debe trasladarse al lado izquierdo.
- Los exportadores existentes (`customer-export.php`, `bathroom-contract-history-export.php` y `bathroom-contract-status-export.php`) no aplican de forma homogénea `require_permission('export')`. Los nuevos endpoints y los que sirven de referencia deben quedar protegidos antes de exponer más listados.
- `controller/service-pdf.php`, `invoice-pdf.php` y `certificate-pdf.php` son documentos **individuales**. No sirven como exportación masiva. En particular, no se debe reutilizar `controller/servicio-pdf.php`: conserva SQL insegura y una relación de baños errónea, deuda explícitamente fuera del alcance de este plan.

## 2. Contrato funcional y visual a congelar

Cada toolbar resultante tendrá esta estructura, sin añadir títulos duplicados:

```text
Desktop (>= sm)
[ Buscar listado...                         ]   [ Excel ] [ PDF ] [ acción existente ] [ Agregar nuevo ]

Mobile
[ Buscar listado...                                                   ]
[ Excel ] [ PDF ] [ acción existente ] [ Agregar nuevo ]
```

Reglas de implementación:

1. Usar el shell existente de `layouts/native-table.php` cuando la vista ya usa tabla nativa. Su `table_native_open()` coloca el input a la izquierda y recibe las acciones mediante `actions_html`; no duplicar buscadores manuales.
2. En vistas de cards/tabla (`dash-bathrooms.php`, `dash-services.php`, `dash-invoices-list.php`) conservar el markup Tailwind de toolbar ya existente. Los filtros (pills), el toggle de vista y el selector de fechas permanecen a la izquierda o derecha según estén hoy; insertar Excel/PDF antes de las acciones de negocio.
3. Reutilizar exactamente `table_native_export_buttons($csvUrl, $pdfUrl, $idPrefix)` para todos los botones nuevos. Esto asegura iconos, orden, tamaños, IDs predecibles y respuesta responsive coherentes. Para toolbars que no invocan `table_native_open()`, la vista puede imprimir el resultado de la función, incluyendo `layouts/native-table.php` solo si aún no lo hace.
4. El listado exportado será el conjunto completo que corresponde al filtro **server-side** vigente, no solamente la página visible de `native-table.js` ni el texto buscado en el navegador. El buscador de las tablas nativas seguirá siendo cliente-side y no modifica la URL de exportación.
5. Preservar en los enlaces de exportación los parámetros server-side admitidos: `estado`, `sort` y `dir` en contratos; `filter` en servicios y facturas. Certificados, usuarios y el inventario de baños exportan su listado completo actual. Todo parámetro se valida con allowlists en el controller antes de llamar al caso de uso.
6. Cada endpoint de exportación hará `include('../layouts/session.php')`, cargará `layouts/permissions.php` y exigirá `require_permission('export')`. El de usuarios exigirá además `require_permission('manage_users')`; sus botones solo se renderizan para administradores. Registrar cada descarga con `log_activity_ctx()` sin incluir datos personales en `datos`.
7. El renderer compartido debe escapar valores con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` al construir HTML TCPDF. CSV se escribe con `fputcsv`; no interpolar SQL ni datos de request. Los repositorios/use cases DDD existentes son la única fuente de datos de las nuevas exportaciones.

## 3. Alcance por vista

- `dash-bathrooms.php`: agregar Excel/PDF de inventario (todos los baños que entrega `ListBathroomsWithAssignment`, no solo la página actual), antes del toggle de vista y de «Registrar Baño».
- `dash-contracts.php`: agregar Excel/PDF antes de «Agregar Nuevo Contrato». La exportación respeta el tab/filtro `estado` y el ordenamiento actual.
- `dash-services.php`: agregar Excel/PDF antes del toggle y de «Agregar Nuevo Servicio». Respeta `filter=todos|facturados|no-facturados` y conserva el toggle cards/tabla.
- `dash-invoices-list.php`: agregar Excel/PDF antes de «Subir Excel / CSV» y «Agregar Nueva Factura». Respeta el filtro de estado; el date picker actual no será exportable hasta que tenga un filtro server-side real.
- `dash-certificates.php`: reemplazar el toolbar manual por `table_native_open()`/`table_native_close()` o por markup idéntico al helper, dejando el buscador a la izquierda; insertar Excel/PDF y después «Agregar Nuevo Certificado» a la derecha.
- `dash-users-list.php`: misma reubicación con tabla nativa; exportaciones y «Agregar Usuario» a la derecha. Las acciones y la exportación solo se muestran a quien tenga `manage_users`.
- `dash-bathrooms-contracts.php` y `dash-bathrooms-contracts-status.php`: no se rediseñan; se usan como referencia y se endurecen sus guards de exportación en la fase de seguridad.
- `dash-customers.php`: no cambia visualmente; se endurece el guard de su endpoint existente para que el patrón de permisos sea uniforme.

## 4. Diseño técnico de exportación

Crear un renderer reutilizable bajo `app/src/Infrastructure/Export/` (namespace `App\Infrastructure\Export`), sin introducir clases en `app/public/`. Recibirá título, filename base, columnas y filas ya normalizadas por el controller, y expondrá dos salidas: CSV compatible con Excel y PDF TCPDF en horizontal. De esta forma se reutiliza la cabecera institucional, logos, escape HTML, márgenes y metadatos sin copiar siete veces la misma plantilla.

Los controllers nuevos permanecen delgados: validan `format` y filtros, verifican permiso, invocan el use case ya existente, filtran con lógica equivalente a su vista cuando corresponda, normalizan columnas de salida, registran actividad y delegan el stream al renderer. No requieren cambios de esquema ni de `composer.json`.

Endpoints previstos:

- `controller/bathroom-export.php`
- `controller/contract-export.php`
- `controller/service-export.php`
- `controller/invoice-export.php`
- `controller/certificate-export.php`
- `controller/user-export.php`

El conjunto de columnas se congela en el cierre de la ola 1 a partir de las columnas actuales de cada tabla. No se exportarán acciones, controles ni imágenes de avatar. Los valores monetarios se exportan como números sin formato visual; fechas como `d-m-Y`; estados como etiquetas legibles.

## 5. Fases y olas Orca

La columna **Modelo (Orca)** es obligatoria para el despacho. Sonnet 5 actúa como orquestador y no implementa código. Las tareas Codex-terra comparten modelo crítico: si Orca no tiene dos terminales concurrentes de ese modelo, se despachan de forma secuencial dentro de la misma ola. Al cierre de cada ola, Sonnet debe revisar el diff y ejecutar el QA indicado antes de liberar la siguiente.

| Fase | Entregable | Riesgo | Tarea | Ola | Modelo (Orca) | Depende de |
|---|---|---|---|---:|---|---|
| 0 | Contrato de exportación aprobado | Medio: decidir columnas y semántica de filtros antes de generar documentos | Relevar columnas de cada listado, confirmar qué roles ven la sección Usuarios y aprobar el contrato de §§2–4; registrar cualquier excepción real de datos. | 0 | Sonnet 5 | — |
| 1 | Base de exportación segura y referencias endurecidas | Alto: endpoint descargable sin guard o fuga de datos | Crear renderer reutilizable en `app/src/Infrastructure/Export/`; agregar `require_permission('export')` y logging homogéneo a los tres exportadores existentes de clientes/baños; lint y smoke de sus CSV/PDF. | 1 | Codex gpt-5.6-terra (high) | Fase 0 |
| 2 | Exportaciones DDD de Contratos y Baños | Alto: filtros/orden de request o datos contractuales inconsistentes | Crear `contract-export.php` y `bathroom-export.php` usando los repositorios/use cases existentes, filtros allowlist y renderer común; conectar la UI de ambas vistas sin romper pills/toggle/drawer. | 2 | Codex gpt-5.6-terra (high) | Fase 1 |
| 3 | Exportación DDD de Servicios | Alto: no reintroducir la SQL insegura de `servicio-pdf.php` | Crear `service-export.php` desde `ListServices`, aplicar allowlist de facturación y renderer; añadir botones a `dash-services.php`; comprobar que `service-pdf.php` individual y cards/tabla siguen independientes. | 3 | Codex gpt-5.6-terra (high) | Fase 1 |
| 4 | Exportación DDD de Facturas | Alto: coherencia entre estado listado y documento descargado | Crear `invoice-export.php` desde `ListInvoices`, aplicar el filtro permitido, renderer y log; agregar botones antes de carga masiva/alta sin tocar el flujo XLSX. | 4 | Codex gpt-5.6-terra (high) | Fase 1 |
| 5 | Exportaciones de Certificados y Usuarios | Alto en usuarios: PII y control de roles | Crear `certificate-export.php` y `user-export.php`; restringir el segundo a `manage_users`; conectar botones y confirmar que ninguno queda visible o accesible por URL para roles inferiores. | 5 | Codex gpt-5.6-terra (high) | Fase 1 |
| 6 | Toolbars homogéneas en tablas nativas | Medio: duplicar búsqueda o romper paginación client-side | Migrar los toolbars manuales de certificados y usuarios al contrato de `native-table.php`, conservando IDs, `data-table-search-input`, paginación y drawers; ajustar el render condicional de acciones de Usuarios. | 6 | OpenCode MiniMax-M3 | Fases 5 |
| 7 | Toolbars finales y QA visual/funcional | Medio: regresión responsive o URL de exportación incompleta | Revisar orden exacto de botones, responsive, filtros, permisos, descargas y logs en las nueve vistas; ejecutar smoke PHP/Docker y revisión de red/consola. Corregir solo defectos de integración descubiertos. | 7 | Sonnet 5 | Fases 2, 3, 4, 5, 6 |

### Orden de despacho

1. Despachar Fase 0 y cerrar la decisión de columnas/roles.
2. Despachar Fase 1. No abrir endpoints nuevos antes de validar guards y renderer.
3. Fases 2, 3, 4 y 5 son independientes por entidad, pero todas usan Codex-terra (high). Se pueden considerar una misma ola lógica tras Fase 1; respetar la limitación de terminales del modelo y verificar cada una antes de la siguiente si no hay concurrencia disponible.
4. Despachar Fase 6 tras la exportación de Usuarios, pues modifica el mismo toolbar que consumirá sus enlaces.
5. Ejecutar Fase 7 solo cuando todas las tareas anteriores estén completadas y cada worker haya reportado su smoke test.

## 6. QA de cierre por ola

Después de cualquier cambio PHP: `docker-compose restart php`, `php -l` sobre cada archivo tocado y smoke manual en `http://localhost`. Si se agregan clases en `app/src/`, ejecutar además `docker-compose exec php composer dump-autoload`. No hay cambios de `composer.json`, por lo que no corresponde recrear el mount por ese motivo.

Checklist funcional final:

- Cada una de las nueve vistas mantiene un único buscador funcional, colocado a la izquierda en escritorio.
- Los botones aparecen en el orden Excel, PDF, acciones existentes y alta; en móvil se envuelven sin solapamiento.
- Excel descarga CSV UTF-8 con BOM; PDF descarga un documento válido con título, fecha, encabezados y datos escapados.
- Contratos conserva `estado`, `sort` y `dir`; Servicios y Facturas conservan su `filter`; los documentos tienen el mismo conjunto de registros que su filtro server-side.
- El texto del buscador cliente-side no pretende limitar la exportación y esa semántica queda visible/consistente en QA.
- Ningún endpoint acepta `format` o filtro fuera de su allowlist; una URL inválida devuelve 400 y un rol no autorizado recibe el bloqueo de permisos.
- Usuarios: botón y endpoint solo para administrador; verificar tanto la UI como acceso directo a `controller/user-export.php`.
- Los PDF individuales de factura, servicio y certificado, la carga masiva de facturas, drawers, tabs, pills, toggle cards/tabla y paginación nativa no presentan regresiones.
- Network sin 404 de assets; consola y `docker-compose logs php` sin errores nuevos; cada exportación deja una entrada `EXPORT` en el log de actividad.

## 7. Fuera de alcance

- Convertir CSV a XLSX real o sumar PhpSpreadsheet.
- Exportar la búsqueda cliente-side o la página puntual visible; requiere filtros server-side/AJAX y no forma parte de esta homologación.
- Reparar/migrar `controller/servicio-pdf.php`; su SQL insegura y su relación incorrecta con baños requieren un plan propio de entidad/reportes.
- Rediseñar las tablas, cards, filtros, date picker o PDF individuales existentes.
- Cambiar credenciales, `config.php`, esquema de base de datos, dependencias o `composer.json`.

