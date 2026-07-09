# Cotización — Mejoras al Sistema Blanco Servicios

**Cliente:** Blanco Servicios e Inversiones SPA
**Fecha:** 07-07-2026
**Preparado por:** Edgardo Ruotolo
**Tarifa acordada:** $10.000 CLP / hora (valores no incluyen IVA — a confirmar tratamiento tributario)

---

## Resumen ejecutivo

Este documento cotiza el conjunto de mejoras solicitadas sobre los módulos de Certificados, Facturas, Contratos/Obras, Servicios y Baños Químicos, más un módulo nuevo de **carga masiva de facturas vía Excel**.

El trabajo se organiza en **3 fases** para permitir entregas parciales y facturación progresiva. El total estimado es de **62,5 horas ≈ $625.000 CLP**.

Los tiempos son estimaciones basadas en el código actual del sistema. Cambios de alcance durante el desarrollo (nuevos requisitos no listados aquí) se cotizan aparte.

---

## Fase 1 — Ajustes rápidos de listados y validaciones

| # | Ítem | Descripción | Horas | Valor CLP |
|---|---|---|---|---|
| 1 | Certificados: orden y botón de impresión | Listado de certificados ordenado por fecha de creación (más reciente primero) + botón "Imprimir Certificado" directo en la grilla | 2 | $20.000 |
| 2 | Facturas: orden por fecha de creación | Listado de facturas ordenado por fecha de creación descendente | 1,5 | $15.000 |
| 3 | Servicios: orden por fecha de creación | Listado de seguimientos/servicios ordenado por fecha de creación descendente | 1,5 | $15.000 |
| 4 | Nuevo Servicio: cliente en orden alfabético | El selector de cliente en el formulario de nuevo servicio se lista alfabéticamente | 1 | $10.000 |
| 5 | Nuevo Servicio: tipo "Retiro de Baños" | Se agrega la opción "Retiro de Baños" al listado de tipos de servicio, incluyendo su reflejo en edición e impresión | 3 | $30.000 |
| 6 | Nuevo Baño: código único | El campo "Código del Baño" no permite duplicados — se valida antes de guardar y se informa el error al usuario | 2,5 | $25.000 |

**Subtotal Fase 1: 11,5 horas — $115.000 CLP**

---

## Fase 2 — Historial y reglas de negocio (Contratos, Baños, Facturas)

| # | Ítem | Descripción | Horas | Valor CLP |
|---|---|---|---|---|
| 7 | Contratos: submenú Activos / Inactivos | Nuevo submenú en el menú lateral con "Contratos Activos" y "Contratos Inactivos", ambos ordenados por fecha de creación descendente | 3 | $30.000 |
| 8 | Contratos: histórico de baños asignados | En el detalle de la obra se muestran dos secciones separadas: Baños Asignados actualmente y Baños No Asignados (histórico) — hoy esa relación se borra al desasignar, por eso se pierde el historial | 5 | $50.000 |
| 9 | Contratos: cierre automático sin baños | Si a una obra se le quitan todos los baños asignados, el contrato pasa automáticamente a estado Inactivo | 2 | $20.000 |
| 10 | Baños: filtros tipo pestañas | Botones de filtro rápido sobre el listado: Estado (Activo/Inactivo) y Asignación (Asignado/No Asignado) | 3 | $30.000 |
| 11 | Facturas: botón Editar | Nuevo botón de acción para editar los datos de una factura ya cargada | 4 | $40.000 |
| 12 | Facturas: no perder servicios al anular | Al anular una factura, los servicios asociados quedan disponibles nuevamente para ser facturados — hoy quedan bloqueados sin poder liberarse | 2 | $20.000 |
| 13 | Facturas: campo "Fecha de Pago" | Nuevo campo editable directamente desde la grilla de facturas | 4 | $40.000 |

**Subtotal Fase 2: 23 horas — $230.000 CLP**

---

## Fase 3 — Módulo nuevo: Carga masiva de facturas por Excel

| # | Ítem | Descripción | Horas | Valor CLP |
|---|---|---|---|---|
| 14 | Módulo de carga de facturas | Nuevo ítem "Cargar Facturas" en el menú, con: pantalla de subida de archivo + plantilla modelo descargable, previsualización en grilla antes de confirmar, columna "Obra" editable (select con las obras del RUT detectado en cada fila), validación de que toda fila tenga obra asignada, botones Cancelar/Confirmar, control de facturas duplicadas (se rechaza la duplicada, el resto continúa cargando) y reporte final de resultado | 22 | $220.000 |

**Subtotal Fase 3: 22 horas — $220.000 CLP**

> **Nota técnica importante:** el proyecto actualmente no usa Composer ni librerías de terceros para leer Excel. Para no romper esa convención, el archivo modelo se define como **planilla Excel simple (una hoja, columnas fijas)** y se procesa con un lector liviano construido a medida (sin dependencias externas). Si el cliente necesita soportar archivos Excel más complejos (múltiples hojas, fórmulas, formatos variables), se recomienda evaluar incorporar una librería especializada, lo que amplía el alcance y el tiempo — a conversar si aplica.

---

## Control de calidad y despliegue

| Ítem | Descripción | Horas | Valor CLP |
|---|---|---|---|
| Pruebas integrales y despliegue | Verificación funcional de los 14 ítems en ambiente de pruebas, corrección de detalles menores y despliegue a producción | 6 | $60.000 |

---

## Total general

| Fase | Horas | Valor CLP |
|---|---|---|
| Fase 1 — Ajustes rápidos | 11,5 | $115.000 |
| Fase 2 — Historial y reglas de negocio | 23 | $230.000 |
| Fase 3 — Carga masiva de facturas | 22 | $220.000 |
| QA y despliegue | 6 | $60.000 |
| **TOTAL** | **62,5 horas** | **$625.000 CLP** |

---

## Supuestos y condiciones

- Valores en CLP, **no incluyen IVA** (a confirmar según forma de facturación con el cliente).
- Se requerirá agregar un campo de fecha de creación a varias tablas que hoy no lo tienen (Certificados, Facturas, Contratos, Servicios), ya que el sistema no registra ese dato actualmente. Esto no afecta datos existentes.
- El alcance no incluye corrección de vulnerabilidades de seguridad conocidas del sistema (inyección SQL, falta de escape de salida) salvo en las líneas de código puntuales que se modifiquen para implementar estas mejoras.
- Las 3 fases pueden facturarse de forma independiente al finalizar cada una, o como pago único al finalizar el total.
- Cualquier requerimiento adicional no listado en este documento se cotiza por separado.
- Tiempos estimados sobre la base del código y datos actuales del sistema en el ambiente de desarrollo local.

---

**Validez de esta cotización:** 30 días desde la fecha de emisión.
