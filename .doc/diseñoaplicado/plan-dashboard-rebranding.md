# Plan — Dashboard rebranding (Tablero)

**Origen:** prototipo `rebranding/src/components/TableroView.tsx` (React + Tailwind).
**Objetivo:** reemplazar el `index.php` actual (4 KPI cards con COUNT sueltos) por un dashboard visualmente idéntico al prototipo, **conectado a datos reales** de la base de datos.
**Depende de:** `plan-sidebar-rebranding.md` (comparte paleta indigo, fuentes, Lucide).
**Estado:** ✅ implementado y cerrado (2026-07-13) — D1-D6 completas. Bug de `estado_Bath` corregido (§7). Footer Skote eliminado. `clientes.created_at` agregado para sparkline.

---

## 0. Reglas y convenciones

Mismas reglas que `plan-sidebar-rebranding.md` sección 0. Destacadas para este plan:

- **Sin build tooling, sin librería de charts.** Los sparklines, donut y bar chart se generan como **SVG inline en PHP** (mismo approach del prototipo).
- **Queries raw en `index.php`.** El dashboard es una vista de solo lectura con agregaciones (COUNT, SUM, GROUP BY) que no encajan en los repositorios DDD basados en entidades. Se usa `$link->query()` con SQL en MAYÚSCULAS, igual que el `index.php` actual. Sin input del usuario → sin prepared statements necesarios.
- **Paleta indigo** consistente con el sidebar (no la teal del resto de la app).

---

## 1. Estado actual vs objetivo

### 1.1 `index.php` actual (151 líneas)

- 4 KPI cards (`.kpi-card`) con `COUNT(*)` sueltos de `bathrooms`, `clientes`, `contratos`, `servicios`.
- Sin gráficos, sin métricas financieras, sin actividades recientes.
- Carga `apexcharts.min.js`, `dashboard.init.js`, vectormap — **todo sin usar**.

### 1.2 Prototipo TableroView (434 líneas)

| Sección | Contenido | Datos del prototipo |
|---|---|---|
| Banner rebranding | Anuncio del propio rediseño | **Eliminar** — no aporta en producción |
| 4 KPI cards | Baños, Clientes, Contratos, Servicios | Hardcodeados (`'100'`, `'90'`, `'195'`, `'1,711'`) |
| Donut chart | Distribución baños: asignados/disponibles/mantención | Cálculo real del estado |
| Bar chart | Servicios por día de la semana | Hardcodeado |
| Actividades recientes | Timeline de últimas operaciones | Mock data |
| Resumen financiero | Facturado, pagado, pendiente, m³ | Cálculo real de facturas/certificados |

### 1.3 Mapeo prototipo → datos reales

| Widget | Query real |
|---|---|
| KPI Baños (total) | `SELECT COUNT(*) FROM bathrooms` |
| KPI Baños (asignados/disponibles) | `SELECT COUNT(*) FROM bathrooms WHERE asignado_Bath = 1` / `= 0 AND estado_Bath = 2` |
| KPI Clientes (total) | `SELECT COUNT(*) FROM clientes WHERE estado_Cliente = 1` |
| KPI Contratos (total / activos / terminados) | `SELECT COUNT(*) FROM contratos` / `WHERE estado_Contrato = 2` / `= 1` |
| KPI Servicios (total) | `SELECT COUNT(*) FROM servicios` |
| Donut: asignados / disponibles / mantención | `SELECT asignado_Bath, estado_Bath, COUNT(*) FROM bathrooms GROUP BY asignado_Bath, estado_Bath` |
| Bar chart: servicios por día esta semana | `SELECT DAYOFWEEK(fecha_Servicio) AS d, COUNT(*) AS c FROM servicios WHERE WEEK(fecha_Servicio, 1) = WEEK(CURDATE(), 1) GROUP BY d` |
| Actividades recientes | Últimos 5 servicios + últimos 3 certificados + últimos 2 facturas pagadas |
| Total facturado | `SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura IN (1, 2)` |
| Monto pagado | `SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura = 2` |
| Monto pendiente | `SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura = 1` |
| m³ certificados | `SELECT COALESCE(SUM(mts_Certificado), 0) FROM certificados` |
| Sparklines (7 puntos) | Conteos mensuales de los últimos 7 meses por entidad |

**Estado codes de referencia (verificados contra DB el 2026-07-13):**
- `estado_Bath`: `1` = Activo, `0` = Inactivo ⚠️ (distinto de lo que decía el plan original — `2` no existe)
- `asignado_Bath`: `1` = Asignado, `0` = Disponible
- `estado_Contrato`: `2` = Activo, `1` = Terminado, `0` = (otro, 12 registros sin clasificar)
- `estado_Factura`: `1` = Pendiente, `2` = Pagado, `3` = Anulado
- `estado_Cliente`: `1` = Activo, `0` = Inactivo

---

## 2. Anatomía del dashboard

```
<main class="db-dashboard">
  <!-- 4 KPI cards -->
  <div class="db-kpi-grid">
    x4: <a class="db-kpi-card" href="...">
      <div class="db-kpi-header"> label + icon </div>
      <div class="db-kpi-value"> number + badge </div>
      <svg class="db-sparkline"> ... </svg>
      <div class="db-kpi-footer"> subtitle + pLabel </div>
    </a>
  </div>

  <!-- Analytics hub: 3 columnas -->
  <div class="db-hub-grid">
    <div class="db-card"> Donut chart baños </div>
    <div class="db-card"> Bar chart semanal </div>
    <div class="db-card"> Actividades recientes </div>
  </div>

  <!-- Resumen financiero -->
  <div class="db-card db-finance">
    <div class="db-finance-header"> Total + link </div>
    <div class="db-finance-grid">
      <div class="db-finance-box paid"> Pagado </div>
      <div class="db-finance-box pending"> Pendiente </div>
      <div class="db-finance-box eco"> m³ certificados </div>
    </div>
  </div>
</main>
```

---

## 3. Fases de implementación

### Fase D1 — Capa de datos (queries en `index.php`)

**Archivos a tocar:** `app/public/index.php` — reemplazar las queries sueltas del bloque `lines 50-114` por un bloque de datos estructurado al inicio del archivo.

**D1.1 — Bloque de queries (antes del markup HTML)**

Reemplazar todo entre `include('layouts/config.php')` y `<head>` por:

```php
<?php
// ===== Dashboard metrics — raw SQL (read-only view, sin input del usuario) =====

function db_count(mysqli $link, string $sql): int {
    $row = mysqli_fetch_array($link->query($sql));
    return $row ? (int) $row[0] : 0;
}

function db_sum(mysqli $link, string $sql): float {
    $row = mysqli_fetch_array($link->query($sql));
    return $row ? (float) $row[0] : 0.0;
}

function db_rows(mysqli $link, string $sql): array {
    $result = $link->query($sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

// --- KPI basics ---
$total_banos       = db_count($link, 'SELECT COUNT(*) FROM bathrooms');
$total_clientes    = db_count($link, 'SELECT COUNT(*) FROM clientes WHERE estado_Cliente = 1');
$total_contratos   = db_count($link, 'SELECT COUNT(*) FROM contratos');
$contratos_activos = db_count($link, 'SELECT COUNT(*) FROM contratos WHERE estado_Contrato = 2');
$contratos_terminados = db_count($link, 'SELECT COUNT(*) FROM contratos WHERE estado_Contrato = 1');
$total_servicios   = db_count($link, 'SELECT COUNT(*) FROM servicios');

// --- Bathroom distribution (donut) ---
$banos_asignados   = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE asignado_Bath = 1 AND estado_Bath = 2');
$banos_disponibles = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE asignado_Bath = 0 AND estado_Bath = 2');
$banos_mantencion  = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE estado_Bath = 1');

// --- Financial ---
$total_facturado = db_sum($link, 'SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura IN (1, 2)');
$monto_pagado    = db_sum($link, 'SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura = 2');
$monto_pendiente = db_sum($link, 'SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura = 1');
$facturas_pendientes_count = db_count($link, 'SELECT COUNT(*) FROM facturas WHERE estado_Factura = 1');

// --- Eco metric ---
$m3_certificados = db_sum($link, 'SELECT COALESCE(SUM(mts_Certificado), 0) FROM certificados');

// --- Weekly services (bar chart) ---
$weekly_services = array_fill(1, 7, 0); // 1=Dom..7=Sab
$weekly_rows = db_rows($link, 'SELECT DAYOFWEEK(fecha_Servicio) AS d, COUNT(*) AS c FROM servicios WHERE WEEK(fecha_Servicio, 1) = WEEK(CURDATE(), 1) GROUP BY d');
foreach ($weekly_rows as $wr) {
    $weekly_services[(int) $wr['d']] = (int) $wr['c'];
}
$weekly_max = max($weekly_services) ?: 1;
$weekly_total = array_sum($weekly_services);

// --- Sparklines: últimos 7 meses ---
$spark_banos = array_fill(0, 7, 0);
$rows = db_rows($link, "SELECT DATE_FORMAT(fechaCompra_Bath, '%Y-%m') AS m, COUNT(*) AS c FROM bathrooms WHERE fechaCompra_Bath >= DATE_SUB(CURDATE(), INTERVAL 7 MONTH) GROUP BY m ORDER BY m");
foreach ($rows as $i => $r) { $spark_banos[$i] = (int) $r['c']; }

$spark_clientes = array_fill(0, 7, 0);
$rows = db_rows($link, "SELECT DATE_FORMAT(createdAt, '%Y-%m') AS m, COUNT(*) AS c FROM clientes WHERE createdAt >= DATE_SUB(CURDATE(), INTERVAL 7 MONTH) GROUP BY m ORDER BY m");
foreach ($rows as $i => $r) { $spark_clientes[$i] = (int) $r['c']; }

$spark_contratos = array_fill(0, 7, 0);
$rows = db_rows($link, "SELECT DATE_FORMAT(fechaInicio_Contrato, '%Y-%m') AS m, COUNT(*) AS c FROM contratos WHERE fechaInicio_Contrato >= DATE_SUB(CURDATE(), INTERVAL 7 MONTH) GROUP BY m ORDER BY m");
foreach ($rows as $i => $r) { $spark_contratos[$i] = (int) $r['c']; }

$spark_servicios = array_fill(0, 7, 0);
$rows = db_rows($link, "SELECT DATE_FORMAT(fecha_Servicio, '%Y-%m') AS m, COUNT(*) AS c FROM servicios WHERE fecha_Servicio >= DATE_SUB(CURDATE(), INTERVAL 7 MONTH) GROUP BY m ORDER BY m");
foreach ($rows as $i => $r) { $spark_servicios[$i] = (int) $r['c']; }

// --- Recent activities (timeline) ---
$recent_services = db_rows($link, 'SELECT SR.id_Servicio, SR.fecha_Servicio, SR.observaciones_Servicio, CT.obra_Contrato, CL.nombre_Cliente FROM servicios SR LEFT JOIN contratos CT ON CT.id_Contrato = SR.id_Contrato LEFT JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente ORDER BY SR.id_Servicio DESC LIMIT 4');

$recent_certificados = db_rows($link, 'SELECT CR.nro_Certificado, CR.mts_Certificado, CR.fecha_Servicio, CT.obra_Contrato, CL.nombre_Cliente FROM certificados CR LEFT JOIN contratos CT ON CT.id_Contrato = CR.id_Contrato LEFT JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente ORDER BY CR.id_Certificado DESC LIMIT 3');

// --- Contracts expiring soon ---
$expiring_soon = db_rows($link, 'SELECT CT.id_Contrato, CT.obra_Contrato, CT.fechaFin_Contrato, CL.nombre_Cliente FROM contratos CT LEFT JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente WHERE CT.estado_Contrato = 2 AND CT.fechaFin_Contrato BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY CT.fechaFin_Contrato ASC');

// --- Helpers ---
function format_clp(float $val): string {
    return '$' . number_format($val, 0, ',', '.');
}
?>
```

**Notas:**
- Si la columna `createdAt` no existe en `clientes`, verificar el nombre real (`rg "createdAt\|fecha.*cliente\|created" app/src/Infrastructure/Persistence/MysqliCustomerRepository.php`). Si no existe un campo de alta, usar un sparkline plano de ceros o omitir el sparkline de clientes.
- `WEEK(fecha_Servicio, 1)` usa modo ISO (lunes = día 1), consistente con la semana laboral.

**Checklist de aceptación:**
- [ ] `docker-compose restart php` + `http://localhost` carga sin error 500
- [ ] `var_dump($total_banos)` devuelve el mismo número que el COUNT del index.php viejo
- [ ] `$monto_pagado + $monto_pendiente` es coherente con `$total_facturado`
- [ ] `$weekly_total` es razonable (no es 0 si hubo servicios esta semana, no es negativo)
- [ ] `$m3_certificados` es un número decimal positivo
- [ ] No hay warnings PHP en `docker-compose logs php`

---

### Fase D2 — CSS del dashboard (`dashboard.css`)

**Archivos a crear:** `app/public/assets/css/dashboard.css`.
**Archivos a tocar:** `app/public/layouts/head-style.php` (agregar `<link>`).

Mismo patrón de aislamiento que `sidebar.css`. Paleta indigo.

**D2.1 — Contenido de `dashboard.css`**

```css
/* ===== Dashboard rebranding — vanilla CSS traducido de TableroView.tsx ===== */

.db-dashboard {
    --db-primary: #4f46e5;
    --db-primary-light: #eef2ff;
    --db-primary-mid: #6366f1;
    --db-primary-dark: #4338ca;
    --db-ink: #0f172a;
    --db-bg: #ffffff;
    --db-surface: #f8fafc;
    --db-border: #e2e8f0;
    --db-border-soft: #f1f5f9;
    --db-text: #0f172a;
    --db-text-muted: #64748b;
    --db-text-faint: #94a3b8;
    --db-success: #10b981;
    --db-success-bg: rgba(16, 185, 129, 0.1);
    --db-warn: #f59e0b;
    --db-warn-bg: rgba(245, 158, 11, 0.1);
    --db-danger: #f43f5e;

    font-family: 'Inter', sans-serif;
    padding: 4px;
}

.db-dashboard > * + * { margin-top: 24px; }

/* KPI grid */
.db-kpi-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 24px;
}
@media (min-width: 640px) { .db-kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .db-kpi-grid { grid-template-columns: repeat(4, 1fr); } }

.db-kpi-card {
    display: block;
    text-decoration: none;
    color: inherit;
    padding: 24px;
    background: var(--db-bg);
    border: 1px solid var(--db-border-soft);
    border-radius: 24px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: box-shadow 0.3s, transform 0.2s;
    position: relative;
    overflow: hidden;
}
.db-kpi-card:hover { box-shadow: 0 10px 30px -5px rgba(226,232,240,0.7); transform: translateY(-2px); }
.db-kpi-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 128px; height: 128px;
    background: linear-gradient(to bottom left, rgba(99,102,241,0.05), transparent);
    border-bottom-left-radius: 100%;
}

.db-kpi-header { display: flex; align-items: center; justify-content: space-between; }
.db-kpi-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--db-text-faint);
    text-transform: uppercase;
    letter-spacing: 0.025em;
}
.db-kpi-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    flex-shrink: 0;
}
.db-kpi-icon svg { width: 16px; height: 16px; }
.db-kpi-icon.bg-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 4px 10px rgba(99,102,241,0.2); }
.db-kpi-icon.bg-dark { background: linear-gradient(135deg, #334155, #0f172a); }

.db-kpi-body { margin-top: 16px; display: flex; align-items: baseline; gap: 8px; }
.db-kpi-value { font-size: 30px; font-weight: 800; color: var(--db-text); letter-spacing: -0.025em; line-height: 1; font-variant-numeric: tabular-nums; }
.db-kpi-badge {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    font-weight: 700;
    color: var(--db-primary);
    background: var(--db-primary-light);
    padding: 2px 6px;
    border-radius: 6px;
}

.db-sparkline { width: 100%; height: 32px; margin-top: 12px; }

.db-kpi-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--db-border-soft);
    font-size: 11px;
}
.db-kpi-subtitle { color: var(--db-text-muted); font-weight: 500; }
.db-kpi-plabel { font-family: 'JetBrains Mono', monospace; font-size: 9px; font-weight: 600; color: var(--db-text-faint); text-transform: uppercase; flex-shrink: 0; }

/* Hub grid (3 columns) */
.db-hub-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}
@media (min-width: 1024px) { .db-hub-grid { grid-template-columns: repeat(3, 1fr); } }

.db-card {
    background: var(--db-bg);
    border: 1px solid var(--db-border-soft);
    border-radius: 24px;
    padding: 24px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.db-card-title { font-size: 16px; font-weight: 700; color: var(--db-text); }
.db-card-subtitle { font-size: 12px; color: var(--db-text-muted); margin-top: 2px; }

/* Donut chart */
.db-donut-wrap { display: flex; flex-direction: column; align-items: center; padding: 16px 0; position: relative; }
.db-donut { width: 160px; height: 160px; }
.db-donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
.db-donut-percent { font-size: 30px; font-weight: 800; color: var(--db-text); line-height: 1; }
.db-donut-label { font-family: 'JetBrains Mono', monospace; font-size: 9px; font-weight: 700; color: var(--db-text-muted); text-transform: uppercase; margin-top: 4px; }

.db-legend { margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--db-border-soft); }
.db-legend-row { display: flex; align-items: center; justify-content: space-between; font-size: 12px; padding: 6px 0; }
.db-legend-dot { width: 10px; height: 10px; border-radius: 3px; display: inline-block; margin-right: 8px; }
.db-legend-label { color: var(--db-text-muted); font-weight: 500; display: flex; align-items: center; }
.db-legend-value { font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--db-text); }

/* Bar chart */
.db-bars { height: 176px; display: flex; align-items: flex-end; justify-content: space-between; gap: 8px; padding: 0 8px; margin-top: 24px; }
.db-bar-col { display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; }
.db-bar { width: 100%; max-width: 32px; border-radius: 6px 6px 0 0; background: linear-gradient(to top, #6366f1, #a5b4fc); transition: background 0.2s; min-height: 2px; }
.db-bar:hover { background: linear-gradient(to top, #4f46e5, #818cf8); }
.db-bar-day { font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; color: var(--db-text-faint); text-transform: uppercase; }
.db-bars-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--db-border-soft); font-size: 12px; color: var(--db-text-muted); }
.db-bars-total { font-family: 'JetBrains Mono', monospace; font-weight: 800; color: var(--db-text); }

/* Activities timeline */
.db-activities { margin-top: 16px; max-height: 220px; overflow-y: auto; }
.db-activity { display: flex; gap: 12px; padding: 8px 0; font-size: 12px; line-height: 1.4; }
.db-activity-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
.db-activity.is-success .db-activity-dot { background: var(--db-success); box-shadow: 0 0 0 4px var(--db-success-bg); }
.db-activity.is-info .db-activity-dot { background: var(--db-primary); box-shadow: 0 0 0 4px var(--db-primary-light); }
.db-activity.is-warn .db-activity-dot { background: var(--db-warn); box-shadow: 0 0 0 4px var(--db-warn-bg); }
.db-activity-title { font-weight: 600; color: #1e293b; }
.db-activity-meta { color: var(--db-text-muted); font-size: 11px; }
.db-activity-detail { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--db-text-faint); }

/* Finance overview */
.db-finance-header { display: flex; flex-direction: column; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid var(--db-border-soft); }
@media (min-width: 640px) { .db-finance-header { flex-direction: row; align-items: center; justify-content: space-between; } }
.db-finance-total-label { font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; color: var(--db-text-faint); text-transform: uppercase; }
.db-finance-total-value { font-size: 18px; font-weight: 800; color: var(--db-text); }

.db-finance-grid { display: grid; grid-template-columns: 1fr; gap: 24px; margin-top: 20px; }
@media (min-width: 768px) { .db-finance-grid { grid-template-columns: repeat(3, 1fr); } }

.db-finance-box { padding: 16px; border-radius: 16px; display: flex; align-items: center; justify-content: space-between; }
.db-finance-box.paid { background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.1); }
.db-finance-box.pending { background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.1); }
.db-finance-box.eco { background: var(--db-bg); border: 1px solid var(--db-border-soft); }
.db-finance-box-label { font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.db-finance-box.paid .db-finance-box-label { color: var(--db-primary); }
.db-finance-box.pending .db-finance-box-label { color: var(--db-warn); }
.db-finance-box.eco .db-finance-box-label { color: var(--db-text-faint); }
.db-finance-box-value { font-size: 20px; font-weight: 800; color: var(--db-text); margin-top: 4px; }
.db-finance-box-note { font-size: 10px; margin-top: 4px; }
.db-finance-box.paid .db-finance-box-note { color: var(--db-primary); }
.db-finance-box.pending .db-finance-box-note { color: var(--db-warn); }
.db-finance-box.eco .db-finance-box-note { color: var(--db-text-muted); font-size: 12px; }
.db-finance-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
.db-finance-icon svg { width: 20px; height: 20px; }
.db-finance-icon.paid { background: var(--db-primary); }
.db-finance-icon.pending { background: var(--db-warn); }
```

**D2.2 — Cargar `dashboard.css` en `head-style.php`**

Agregar después del `<link>` de `sidebar.css`:

```html
<!-- dashboard rebranding -->
<link href="assets/css/dashboard.css" rel="stylesheet" type="text/css">
```

**Checklist de aceptación:**
- [ ] `assets/css/dashboard.css` existe y carga con 200
- [ ] No hay regresiones visuales (clases nuevas no matchean nada todavía)

---

### Fase D3 — KPI cards markup

**Archivos a tocar:** `app/public/index.php` — reemplazar el bloque de las 4 `.kpi-card` (líneas 50-114) por el nuevo markup.

**D3.1 — Generador de sparkline SVG (función helper PHP)**

Agregar al final del bloque de datos de Fase D1:

```php
<?php
function db_sparkline_svg(array $points, string $color = '#6366f1'): string {
    $max = max($points) ?: 1;
    $count = count($points);
    $step = 100 / ($count - 1);
    $coords = '';
    foreach ($points as $i => $val) {
        $x = round($i * $step, 1);
        $y = round(30 - ($val / $max) * 25, 1);
        $coords .= ($i === 0 ? 'M' : 'L') . " {$x},{$y} ";
    }
    return '<svg class="db-sparkline" viewBox="0 0 100 30" preserveAspectRatio="none">'
        . '<defs><linearGradient id="sg' . spl_object_hash((object)$points) . '" x1="0" y1="0" x2="0" y2="1">'
        . '<stop offset="0%" stop-color="' . $color . '" stop-opacity="0.3"/>'
        . '<stop offset="100%" stop-color="' . $color . '" stop-opacity="0"/>'
        . '</linearGradient></defs>'
        . '<path d="' . $coords . ' L 100,30 L 0,30 Z" fill="url(#sg' . spl_object_hash((object)$points) . ')"/>'
        . '<path d="' . $coords . '" fill="none" stroke="' . $color . '" stroke-width="1.5" stroke-linecap="round"/>'
        . '</svg>';
}
?>
```

**D3.2 — Markup de las 4 KPI cards**

Reemplazar el `<div class="row">...<!-- end row-->` actual por:

```php
<div class="db-dashboard">
    <div class="db-kpi-grid">
        <!-- Baños -->
        <a href="dash-bathrooms.php" class="db-kpi-card">
            <div class="db-kpi-header">
                <span class="db-kpi-title">Flota de Baños</span>
                <span class="db-kpi-icon bg-indigo"><i data-lucide="bath"></i></span>
            </div>
            <div class="db-kpi-body">
                <span class="db-kpi-value"><?php echo $total_banos; ?></span>
                <span class="db-kpi-badge"><?php echo $total_banos > 0 ? round($banos_asignados / $total_banos * 100) : 0; ?>%</span>
            </div>
            <?php echo db_sparkline_svg($spark_banos); ?>
            <div class="db-kpi-footer">
                <span class="db-kpi-subtitle"><?php echo $banos_asignados; ?> asignados, <?php echo $banos_disponibles; ?> disponibles</span>
                <span class="db-kpi-plabel">Ocupación</span>
            </div>
        </a>

        <!-- Clientes -->
        <a href="dash-customers.php" class="db-kpi-card">
            <div class="db-kpi-header">
                <span class="db-kpi-title">Clientes Activos</span>
                <span class="db-kpi-icon bg-indigo"><i data-lucide="users"></i></span>
            </div>
            <div class="db-kpi-body">
                <span class="db-kpi-value"><?php echo $total_clientes; ?></span>
            </div>
            <?php echo db_sparkline_svg($spark_clientes); ?>
            <div class="db-kpi-footer">
                <span class="db-kpi-subtitle">Empresas registradas</span>
                <span class="db-kpi-plabel">Activos</span>
            </div>
        </a>

        <!-- Contratos -->
        <a href="dash-contracts.php" class="db-kpi-card">
            <div class="db-kpi-header">
                <span class="db-kpi-title">Contratos</span>
                <span class="db-kpi-icon bg-indigo"><i data-lucide="file-text"></i></span>
            </div>
            <div class="db-kpi-body">
                <span class="db-kpi-value"><?php echo $total_contratos; ?></span>
                <span class="db-kpi-badge"><?php echo $total_contratos > 0 ? round($contratos_activos / $total_contratos * 100) : 0; ?>%</span>
            </div>
            <?php echo db_sparkline_svg($spark_contratos); ?>
            <div class="db-kpi-footer">
                <span class="db-kpi-subtitle"><?php echo $contratos_activos; ?> activos, <?php echo $contratos_terminados; ?> finalizados</span>
                <span class="db-kpi-plabel">Retención</span>
            </div>
        </a>

        <!-- Servicios -->
        <a href="dash-services.php" class="db-kpi-card">
            <div class="db-kpi-header">
                <span class="db-kpi-title">Servicios Realizados</span>
                <span class="db-kpi-icon bg-dark"><i data-lucide="wrench"></i></span>
            </div>
            <div class="db-kpi-body">
                <span class="db-kpi-value"><?php echo $total_servicios; ?></span>
            </div>
            <?php echo db_sparkline_svg($spark_servicios, '#334155'); ?>
            <div class="db-kpi-footer">
                <span class="db-kpi-subtitle">Limpiezas, desinfecciones y reparaciones</span>
                <span class="db-kpi-plabel">Histórico</span>
            </div>
        </a>
    </div>
```

**Checklist de aceptación:**
- [ ] Los 4 números mostrados coinciden con los del `index.php` viejo (mismas entidades, mismo COUNT)
- [ ] Las cards son clickeables y navegan a la vista correcta
- [ ] Los sparklines renderizan como SVG con gradiente
- [ ] Responsive: 1 columna mobile, 2 tablet, 4 desktop
- [ ] Los porcentajes (ocupación, retención) son matemáticamente correctos

---

### Fase D4 — Analytics hub (donut + bar chart + actividades)

**D4.1 — Donut chart (distribución de baños)**

```php
    <div class="db-hub-grid">
        <!-- Donut: distribución baños -->
        <div class="db-card">
            <h3 class="db-card-title">Distribución de Baños</h3>
            <p class="db-card-subtitle">Estado operativo de la flota</p>

            <div class="db-donut-wrap">
                <?php
                $pct_asignados = $total_banos > 0 ? round($banos_asignados / $total_banos * 100) : 0;
                $circumference = 251.2; // 2 * pi * 40
                $dash_asignados = $circumference * ($banos_asignados / max($total_banos, 1));
                ?>
                <svg class="db-donut" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f1f5f9" stroke-width="12" />
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#4f46e5" stroke-width="12"
                            stroke-dasharray="<?php echo $dash_asignados; ?> <?php echo $circumference; ?>"
                            stroke-linecap="round" transform="rotate(-90 50 50)" />
                </svg>
                <div class="db-donut-center">
                    <span class="db-donut-percent"><?php echo $pct_asignados; ?>%</span>
                    <span class="db-donut-label">Asignados</span>
                </div>
            </div>

            <div class="db-legend">
                <div class="db-legend-row">
                    <span class="db-legend-label"><span class="db-legend-dot" style="background:#4f46e5"></span> Asignados a Obra</span>
                    <span class="db-legend-value"><?php echo $banos_asignados; ?> (<?php echo $pct_asignados; ?>%)</span>
                </div>
                <div class="db-legend-row">
                    <span class="db-legend-label"><span class="db-legend-dot" style="background:#e2e8f0"></span> Disponibles</span>
                    <span class="db-legend-value"><?php echo $banos_disponibles; ?> (<?php echo $total_banos > 0 ? round($banos_disponibles / $total_banos * 100) : 0; ?>%)</span>
                </div>
                <div class="db-legend-row">
                    <span class="db-legend-label"><span class="db-legend-dot" style="background:#fecaca"></span> En Mantención</span>
                    <span class="db-legend-value"><?php echo $banos_mantencion; ?></span>
                </div>
            </div>
        </div>
```

**D4.2 — Bar chart (servicios semanales)**

```php
        <!-- Bar chart: servicios esta semana -->
        <div class="db-card">
            <h3 class="db-card-title">Servicios de la Semana</h3>
            <p class="db-card-subtitle">Visitas de limpieza y desinfección</p>

            <div class="db-bars">
                <?php
                $days = [2 => 'Lun', 3 => 'Mar', 4 => 'Mié', 5 => 'Jue', 6 => 'Vie', 7 => 'Sáb', 1 => 'Dom'];
                foreach ($days as $dow => $label):
                    $count = $weekly_services[$dow];
                    $height_px = $weekly_max > 0 ? max(2, round($count / $weekly_max * 140)) : 2;
                ?>
                    <div class="db-bar-col">
                        <div class="db-bar" style="height: <?php echo $height_px; ?>px" title="<?php echo $count; ?> servicios"></div>
                        <span class="db-bar-day"><?php echo $label; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="db-bars-footer">
                <span>Total servicios esta semana</span>
                <span class="db-bars-total"><?php echo $weekly_total; ?> visitas</span>
            </div>
        </div>
```

**D4.3 — Actividades recientes (timeline real)**

```php
        <!-- Actividades recientes -->
        <div class="db-card">
            <h3 class="db-card-title">Actividades Recientes</h3>
            <p class="db-card-subtitle">Últimas operaciones registradas</p>

            <div class="db-activities">
                <?php foreach ($recent_services as $srv): ?>
                    <div class="db-activity is-info">
                        <span class="db-activity-dot"></span>
                        <div>
                            <div class="db-activity-title">Servicio completado</div>
                            <div class="db-activity-meta"><?php echo htmlspecialchars($srv['nombre_Cliente'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($srv['obra_Contrato'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="db-activity-detail"><?php echo htmlspecialchars($srv['fecha_Servicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?> · #<?php echo htmlspecialchars($srv['id_Servicio'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($recent_certificados as $cert): ?>
                    <div class="db-activity is-success">
                        <span class="db-activity-dot"></span>
                        <div>
                            <div class="db-activity-title">Certificado emitido (<?php echo htmlspecialchars(number_format((float) $cert['mts_Certificado'], 1), ENT_QUOTES, 'UTF-8'); ?> m³)</div>
                            <div class="db-activity-meta"><?php echo htmlspecialchars($cert['nombre_Cliente'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="db-activity-detail"><?php echo htmlspecialchars($cert['nro_Certificado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="dash-services.php" class="db-bars-footer" style="text-decoration:none; display:flex; justify-content:center; padding-top:16px; border-top:1px solid var(--db-border-soft); margin-top:16px;">
                <span style="font-size:12px; font-weight:600; color:var(--db-primary);">Ver bitácora completa →</span>
            </a>
        </div>
    </div>
```

**Checklist de aceptación:**
- [ ] El donut muestra el porcentaje real de baños asignados
- [ ] Las barras del bar chart tienen altura proporcional a los servicios reales del día
- [ ] Si no hubo servicios un día, la barra mide 2px (mínimo)
- [ ] Las actividades muestran datos reales (últimos servicios + certificados)
- [ ] No hay warnings PHP si un LEFT JOIN trae NULLs (cliente/obra vacíos)

---

### Fase D5 — Resumen financiero

```php
    <!-- Resumen financiero -->
    <div class="db-card">
        <div class="db-finance-header">
            <div>
                <h3 class="db-card-title">Resumen de Facturación</h3>
                <p class="db-card-subtitle">Monitoreo de compromisos de pago</p>
            </div>
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="text-align:right;">
                    <span class="db-finance-total-label">Total Facturado</span>
                    <span class="db-finance-total-value"><?php echo format_clp($total_facturado); ?></span>
                </div>
                <a href="dash-invoices-list.php" style="padding:10px 16px; background:var(--db-primary-light); color:var(--db-primary-dark); border-radius:12px; font-size:12px; font-weight:600; text-decoration:none;">Ir a Facturas</a>
            </div>
        </div>

        <div class="db-finance-grid">
            <!-- Pagado -->
            <div class="db-finance-box paid">
                <div>
                    <span class="db-finance-box-label">Monto Percibido</span>
                    <div class="db-finance-box-value"><?php echo format_clp($monto_pagado); ?></div>
                    <span class="db-finance-box-note">Operaciones al día</span>
                </div>
                <div class="db-finance-icon paid"><i data-lucide="check-circle"></i></div>
            </div>

            <!-- Pendiente -->
            <div class="db-finance-box pending">
                <div>
                    <span class="db-finance-box-label">Monto por Cobrar</span>
                    <div class="db-finance-box-value"><?php echo format_clp($monto_pendiente); ?></div>
                    <span class="db-finance-box-note"><?php echo $facturas_pendientes_count; ?> facturas pendientes</span>
                </div>
                <div class="db-finance-icon pending"><i data-lucide="alert-circle"></i></div>
            </div>

            <!-- Eco m³ -->
            <div class="db-finance-box eco">
                <div>
                    <span class="db-finance-box-label">Certificación Sanitaria</span>
                    <div class="db-finance-box-value"><?php echo number_format($m3_certificados, 1); ?> m³</div>
                    <span class="db-finance-box-note">Residuos retirados y tratados</span>
                </div>
                <div class="db-finance-icon" style="background:var(--db-ink);"><i data-lucide="droplet"></i></div>
            </div>
        </div>
    </div>
</div>
```

**Checklist de aceptación:**
- [ ] `$monto_pagado + $monto_pendiente == $total_facturado` (coherencia)
- [ ] Los montos se formatean en CLP con separador de miles
- [ ] El contador de facturas pendientes es correcto
- [ ] Los m³ tienen un decimal
- [ ] Los iconos Lucide renderizan

---

### Fase D6 — Limpieza de assets viejos

**Archivos a tocar:** `app/public/index.php` (footer del archivo).

Eliminar del final de `index.php`:

```html
<!-- ELIMINAR: apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<!-- ELIMINAR: vectormap -->
<script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
<!-- ELIMINAR: dashboard.init.js (Skote template, ya sin uso) -->
<script src="assets/js/pages/dashboard.init.js"></script>
<!-- ELIMINAR: app.js (Skote template) -->
<script src="assets/js/app.js"></script>
```

Conservar: `<?php include 'layouts/vendor-scripts.php'; ?>` (que ahora incluye `sidebar.js` que inicializa Lucide).

**No eliminar los archivos físicos** de `assets/libs/` — pueden estar referenciados desde `archive/`. Solo quitar los `<script>` del `index.php`.

**Checklist de aceptación:**
- [ ] DevTools → Network: no carga `apexcharts.min.js`, ni `vectormap`, ni `dashboard.init.js`
- [ ] Los gráficos SVG inline del dashboard siguen renderizando
- [ ] No hay errores de consola por JS faltante

---

## 4. Verificación de integración final

1. **Datos reales:** comparar los 4 números de los KPIs con `SELECT COUNT(*)` directo en la base — deben coincidir.
2. **Coherencia financiera:** `$monto_pagado + $monto_pendiente == $total_facturado`.
3. **Donut correcto:** el porcentaje del donut + los números de la leyenda coinciden con la realidad de la tabla `bathrooms`.
4. **Bar chart:** el día con más servicios tiene la barra más alta; los días sin servicios tienen barra mínima.
5. **Sparklines:** al menos uno de los 4 muestra variación si hubo actividad en los últimos 7 meses.
6. **Sin mock data:** ningún número del dashboard es hardcodeado — todos provienen de queries.
7. **Responsive:** 1 columna en mobile, expansión progresiva en tablet/desktop.
8. **Sin regresión:** sidebar y topbar siguen funcionando; la navegación desde las cards llega a la vista correcta.

---

## 5. Fuera de alcance de este plan

- **Contratos por vencer (panel lateral):** los datos se calculan (`$expiring_soon`) pero no se incluye un panel visual en esta fase. Se puede agregar como extensión de Fase D5 si Edgardo lo pide.
- **Filtros de rango de fechas:** el dashboard muestra "esta semana" y "últimos 7 meses" fijos. Agregar selectores de período es feature futura.
- **Cache de queries:** cada carga del dashboard ejecuta ~12 queries. Para un panel interno con poca concurrencia es aceptable. Si se necesita cache, usar MySQL query cache o un mecanismo de aplicación.
- **Gráficos interactivos (hover tooltips, drill-down):** los SVG son estáticos. Interactividad requiere una librería de charts (ApexCharts, Chart.js — ya vendorizadas) — se evalúa si Edgardo los quiere.
- **Rebranding banner del prototipo:** se omite (era meta-publicidad del propio rediseño).

---

## 7. Implementación real (2026-07-13)

**Estado al retomar el plan:** las 6 fases ya estaban implementadas en `app/public/index.php` (368 líneas), `app/public/assets/css/dashboard.css` (183 líneas) y `app/public/layouts/head-style.php` (cargando `dashboard.css`). El plan decía "nada implementado todavía" — desactualizado.

**Hallazgo crítico al retomar:** bug en las queries de baños. El plan asumía `estado_Bath = 2 = Activo` pero en la DB:
- `estado_Bath = 1` → 86 baños (Activos)
- `estado_Bath = 0` → 14 baños (Inactivos)

Resultado del bug antes del fix: `$banos_asignados = 0`, `$banos_disponibles = 0`, `$banos_mantencion = 86`. El donut mostraba 0% de ocupación y la leyenda "Mantención 86%" en realidad contaba todos los activos.

**Fix aplicado** (`app/public/index.php` líneas ~37-39):
```php
$banos_asignados   = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE asignado_Bath = 1 AND estado_Bath = 1');
$banos_disponibles = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE asignado_Bath = 0 AND estado_Bath = 1');
$banos_mantencion  = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE estado_Bath = 0');
```

**Distribución post-fix (verificada con smoke test):**
- Total: 100 baños
- Asignados: 57 (57%) — donut muestra 57%
- Disponibles: 29 (29%)
- Mantención: 14 (14%)
- Suma: 100 ✅ cierra

**Otros códigos de estado verificados OK** (no requirieron fix):
- `estado_Cliente = 1` → 87 activos ✅
- `estado_Contrato`: 43 activos (=2), 140 terminados (=1) ✅
- `estado_Factura`: 95 pendientes, 261 pagados, 28 anulados. `pagado + pendiente = $163.469.174 = total_facturado` ✅
- `m³_certificados` = 1.039,0 ✅

**Deuda técnica menor (no bloqueante):**
- **`$expiring_soon` query:** no incluida en el `index.php` final (acordado en §5 como fuera de alcance).

**Resuelto 2026-07-13 — `spark_clientes` real:** se agregó la columna `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP` a `clientes` (las otras 4 entidades — contratos, facturas, certificados, servicios — ya la tenían). Query del sparkline ahora devuelve datos reales (`index.php:64-67`). Además, `MysqliCustomerRepository::listActive()` ahora ordena `ORDER BY created_at DESC`. **Limitación:** los 90 registros preexistentes comparten el mismo timestamp (cuando se aplicó el ALTER TABLE) → el ORDER BY es indistinguible entre ellos hasta que se actualicen manualmente o se creen clientes nuevos con timestamps distintos. Documentado como decisión en `~/SitesDoc/decisiones/2026-07-13-clientes-created-at.md`.

**Decisión 2026-07-13 — Footer Skote eliminado:** el `<?php include 'layouts/footer.php'; ?>` se removió de las 19 vistas no-archive (`index.php` + 17 `dash-*.php` + `template/template.php`). El footer era del template viejo (Skote, "Design & Develop by Crow Advance") y rompía la estética minimalista del rebranding. Solo queda el sidebar footer (`sb-footer`, "© 2026 Blanco Servicios / Plataforma Eco-Sostenible v2.0") como footer global. Archivo `layouts/footer.php` eliminado del repo. Documentado en `~/SitesDoc/decisiones/2026-07-13-footer-skote-eliminado.md`.

**Cierre del plan:** ✅ completo. Pendiente solo validar visualmente Edgardo.

---

## 6. Referencias

- **Prototipo fuente:** `rebranding/src/components/TableroView.tsx` (434 líneas, React + Tailwind).
- **Dashboard actual a reemplazar:** `app/public/index.php` (151 líneas, 4 KPI cards con COUNT sueltos).
- **Planes hermanos:** `plan-sidebar-rebranding.md`, `plan-header-rebranding.md` — comparten paleta indigo, fuentes, Lucide.
