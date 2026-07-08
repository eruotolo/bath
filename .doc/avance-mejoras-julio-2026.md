# Informe de avance — Mejoras Sistema Blanco Servicios

**Cliente:** Blanco Servicios e Inversiones SPA
**Referencia:** `.doc/cotizacion-mejoras-julio-2026.md` (cotización original)
**Última actualización:** 08-07-2026
**Estado general:** Fase 1 y Fase 2 completas — cambios probados y verificados en ambiente de testing, pendiente de despliegue a producción.

---

## Resumen de avance

| Fase | Estado |
|---|---|
| Fase 1 — Ajustes rápidos de listados y validaciones | ✅ Completa (ítems 1 a 6) |
| Fase 2 — Historial y reglas de negocio | ✅ Completa (ítems 7 a 13) |
| Fase 3 — Carga masiva de facturas por Excel | Pendiente |

---

## Fase 1 — Ajustes rápidos de listados y validaciones

### 1. Certificados: orden y botón de impresión
El listado de certificados ahora muestra primero los cargados más recientemente. Se agregó un botón de "Imprimir" directo en cada fila, que abre el comprobante en una pestaña nueva sin perder el listado.

### 2. Facturas: orden por fecha de creación
El listado de facturas ahora ordena mostrando primero las cargadas más recientemente.

### 3. Servicios: orden por fecha de creación
El listado de servicios/seguimientos ahora ordena mostrando primero los cargados más recientemente.

### 4. Nuevo Servicio: cliente en orden alfabético
Al crear un nuevo servicio, el selector de cliente ahora lista los clientes en orden alfabético, facilitando encontrarlos.

### 5. Nuevo Servicio: tipo "Retiro de Baños"
Se agregó "Retiro de Baños" como una opción más dentro de los tipos de servicio, junto a Instalación, Reparación, Limpieza, etc. Disponible al crear, editar e imprimir un servicio.

### 6. Nuevo Baño: código único
El sistema ahora impide cargar dos baños con el mismo código — si se intenta, muestra un aviso claro y no permite guardar el duplicado. Aplica tanto al crear un baño nuevo como al editar uno existente.

### Adicional: etiqueta "Disponible"
En el listado de baños, donde antes decía "No Asignado" (en rojo), ahora dice "Disponible" (en azul) — se mantiene el rojo reservado para estados como baja o cancelación.

---

## Fase 2 — Historial y reglas de negocio

### 7. Contratos: estados y submenú
- Donde antes un contrato figuraba como "Inactivo", ahora dice **"Terminado"** (en azul, no en rojo).
- Se agregó un submenú nuevo en "Obra / Contratos" con dos accesos directos: **"Contratos Activos"** y **"Contratos Terminados"**, cada uno mostrando solo los contratos de ese estado, ordenados por los más recientes primero.

### 8. Módulo nuevo: "Baños & Contratos"
Se agregó una pantalla nueva, con dos pestañas:
- **"Todos los contratos activos"**: muestra qué baño está en qué obra en este momento, con el nombre del cliente y la obra.
- **"Todos los baños disponibles"**: muestra qué baños están libres para asignar a una nueva obra.

Ambas con un contador exacto de cantidad, y ordenadas por código de baño. La pantalla anterior que tenía este mismo nombre ahora se llama **"Histórico"** y sigue disponible sin cambios.

### 9. Contratos: cierre automático sin baños
Cuando a una obra se le quita el último baño asignado, el contrato ahora pasa automáticamente a "Terminado" — ya no queda marcado como "Activo" indefinidamente por error.

**Corrección de datos históricos:** además, se revisaron los contratos ya existentes en el sistema y se corrigieron **62 contratos** que figuraban como "Activos" sin tener ningún baño asignado (trabajos ya completados que nunca se habían cerrado manualmente). Quedaron marcados correctamente como "Terminados".

**Hallazgo pendiente de decisión (no corregido automáticamente):** al revisar esto en detalle se encontraron **41 baños** que figuran asignados a más de una obra "activa" al mismo tiempo (por ejemplo, un mismo baño en 4 obras distintas que ya terminaron pero nunca se cerraron a mano). No se corrigió de forma automática porque cada caso puede tener una explicación distinta y requiere que alguien del equipo revise cuál es la asignación vigente real. Podemos revisar esta lista juntos cuando quieras.

### 10. Baños: filtros rápidos
Se agregaron botones de filtro sobre el listado de baños químicos: por Estado (Activo/Inactivo) y por Asignación (Asignado/Disponible), combinables entre sí, sin necesidad de recargar la página.

### 11. Facturas: botón Editar
Ahora se puede editar una factura ya cargada (número, fecha, cliente, obra y monto) desde un botón nuevo en el listado, sin tener que anularla y crear una nueva.

### 12. Facturas: no perder servicios al anular
Al anular una factura, los servicios que tenía asociados quedan liberados automáticamente y disponibles para facturarse de nuevo — antes quedaban bloqueados para siempre sin poder usarse.

### 13. Facturas: campo Fecha de Pago
Se agregó una columna "Fecha de Pago" editable directamente desde el listado de facturas, con un botón que abre un cuadro simple para fijarla o borrarla.

---

## Pendiente

- **Fase 3:** módulo de carga masiva de facturas por Excel.
- **Despliegue a producción:** todo lo anterior está probado en el ambiente de desarrollo/testing. Para pasar a producción hay un script de base de datos ya preparado con todos los cambios necesarios, listo para aplicarse cuando se coordine la ventana de despliegue.
- **Decisión pendiente:** revisar los 41 casos de baños en más de una obra activa simultánea (ver hallazgo arriba).

---

*Este documento se actualiza a medida que se completa cada ítem. Para el detalle técnico de cada cambio, ver los checklists internos en `.doc/checklist-fase1-ajustes-rapidos.md` y `.doc/checklist-fase2-historial-reglas-negocio.md`.*
