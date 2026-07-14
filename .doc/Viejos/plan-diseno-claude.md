# Plan de diseño — Capa de Fundación (CSS, Tokens, Backend)

**Asignado a:** Claude (IA 1 de 2)
**Par simultáneo:** `plan-diseno-minimax.md` (MiniMax — capa de markup de vistas)
**Origen:** derivado de `.doc/plan-diseno-sistema-visual.md` (consensuado con Edgardo, 2026-07-10), dividido para paralelización sin colisión.
**Estado:** 📋 plan — nada implementado todavía.

---

## 0. Cómo usar este documento

Este documento es **una de dos mitades** del plan de diseño visual del panel. Está pensado para que **Claude lo ejecute de forma autónoma y completa**, sin coordinarse con la otra mitad durante el trabajo. La división garantiza **cero colisión de archivos**: cada IA es propietaria exclusiva de un conjunto de archivos distinto.

Cada fase lista los archivos exactos a tocar, el código exacto (antes/después) y un checklist de aceptación. **No implementar dos fases a la vez** — el proyecto no tiene tests automatizados, cada fase se verifica manualmente en `http://localhost` antes de pasar a la siguiente.

### 0.1 Reglas del proyecto (aplican a todo este documento)

De `AGENTS.md` del proyecto, no se repiten en cada fase:

- Conversación en español; código, variables, funciones y mensajes de commit en inglés.
- `snake_case` para variables/funciones PHP, SQL en MAYÚSCULAS.
- Después de cualquier cambio de código PHP: `docker-compose up -d --force-recreate php` (o `docker-compose restart php` si no se tocó `composer.json`/`src/`) + smoke test manual en `http://localhost`.
- Después de cambios en `app/src/`: `docker-compose exec php composer dump-autoload`.
- No se introduce build tooling (Sass, PostCSS, bundlers) — el CSS se edita directo en los `.css` existentes.
- No tocar `app/public/archive/` ni `app/public/assets/libs/` (librerías vendorizadas).
- No commitear ni pushear sin pedido explícito de Edgardo.

---

## 1. Propiedad de archivos — quién hace qué

### 1.1 Archivos de ESTE documento (Claude — Foundation)

Claude es **propietario exclusivo** de los siguientes archivos. MiniMax no los toca:

| Archivo | Qué se hace |
|---|---|
| `app/public/assets/css/bootstrap.css` | Corregir variables `--bs-primary-*` (violeta residual → teal) |
| `app/public/assets/css/style.css` | Agregar bloque de tokens (`:root`), CSS de badges, botones dropdown, KPI cards, tablas y modales |
| `app/public/index.php` | Reemplazar markup de las 4 KPI cards por `.kpi-card`; agregar panel "Contratos por vencer" |
| `app/src/Domain/Contract/ContractRepositoryInterface.php` | Agregar método de lectura `findExpiringSoon()` (Fase A5) |
| `app/src/Infrastructure/Persistence/MysqliContractRepository.php` | Implementar el método nuevo (Fase A5) |
| `app/src/Application/Contract/ListContractsExpiringSoon.php` | Use Case nuevo (Fase A5) |

### 1.2 Archivos del documento paralelo (MiniMax — no tocar)

Claude **no toca** ninguna vista de listado (`dash-*.php`) ni formulario (`dash-*-add.php`, `dash-*-edit.php`). Eso es trabajo de MiniMax, que aplicará el markup HTML que consume las clases CSS que Claude define aquí. El contrato de interfaz está en la sección 2.

---

## 2. Contrato de interfaz entre Claude y MiniMax

MiniMax aplicará clases CSS en el markup HTML de las vistas. Claude debe **definir exactamente estas clases** (mismos nombres, mismos comportamientos). Esta tabla es la fuente de verdad compartida:

| Clase / Variable CSS | La define | La consume | Fase Claude que la crea |
|---|---|---|---|
| `--color-primary`, `--color-primary-rgb`, `--color-primary-text-emphasis`, `--color-primary-bg-subtle`, `--color-primary-border-subtle` | Claude | (uso interno CSS) | A1, A2 |
| `--color-success`, `--color-success-bg` | Claude | MiniMax (badges) | A1 |
| `--color-danger`, `--color-danger-bg`, `--color-danger-soft` | Claude | MiniMax (badges, dropdown destructivo) | A1 |
| `--color-info`, `--color-info-bg` | Claude | MiniMax (badges) | A1 |
| `--color-warn`, `--color-warn-bg` | Claude | MiniMax (badge "Asignado") | A1 |
| `--fs-page-title`, `--fs-section-title`, `--fs-body`, `--fs-label`, `--fs-caption` | Claude | (uso interno CSS y MiniMax opcional) | A2 |
| `.badge-status`, `.badge-status::before` | Claude | MiniMax (reemplaza `.item-*`) | A3 |
| `.badge-status.is-success`, `.is-danger`, `.is-info`, `.is-warn` | Claude | MiniMax | A3 |
| `.item-activo`, `.item-inactivo`, `.item-disponible` (alias de compat) | Claude | MiniMax las elimina al migrar | A3 |
| `.dropdown-item.text-danger:hover/focus` | Claude | MiniMax (marcado de acción destructiva) | A3 |
| `.kpi-card`, `.kpi-card-label`, `.kpi-card-value` | Claude | Claude (markup en `index.php`) | A3, A4 |
| `.dataTable td/th` (compactación + estilo encabezados) | Claude | (aplica a todas las tablas vía CSS) | A3 |
| `.modal-header`, `.modal-footer`, `.modal-body` (overrides) | Claude | (aplica a todos los modales vía CSS) | A3 |

**Principio clave:** MiniMax no necesita que el CSS esté aplicado para escribir su markup — el HTML con clases es válido sin el CSS. Claude no necesita que el markup exista para escribir su CSS — las reglas son válidas sin elementos que las consuman. **Ambas mitades pueden ejecutarse en paralelo real.**

---

## 3. Tokens de diseño

### 3.1 Paleta — mapeo completo antes → después

El panel tiene **tres sistemas de color superpuestos que nunca se unificaron**: las variables raíz de Bootstrap (tema "Skote" original, violeta), los overrides puntuales en `style.css` (teal, aplicados a mano en un commit viejo), y valores hardcodeados sueltos en vistas y JS. La tabla siguiente es la fuente de verdad única — cualquier color fuera de esta tabla que aparezca en el código es un bug a corregir.

| Rol | Valor final | Dónde vive hoy (y su problema) |
|---|---|---|
| **Primario (teal, marca)** | `#2D5C6C` | `bootstrap.css:32` `--bs-primary` — ✅ ya correcto, no tocar |
| **Primario RGB (para transparencias)** | `45, 92, 108` | `bootstrap.css:40` `--bs-primary-rgb: 81, 86, 190` — ❌ **es el RGB del violeta original, nunca se actualizó cuando se cambió el hex de arriba.** Causa raíz de que el violeta "destelle" en focus rings, hovers y fondos sutiles (`rgba(var(--bs-primary-rgb), ...)` se usa en ~15 selectores de `bootstrap.css`/`app.css`). |
| **Primario texto-énfasis** | `#16232a` | `bootstrap.css:48` `--bs-primary-text-emphasis: #20224c` — violeta oscuro, cambiar |
| **Primario fondo sutil** | `#dde8ea` | `bootstrap.css:56` `--bs-primary-bg-subtle: #b9bbe5` — violeta claro, cambiar |
| **Primario borde sutil** | `#b9d2d7` | `bootstrap.css:64` `--bs-primary-border-subtle: #b9bbe5` — mismo valor que bg-subtle (bug menor del original), separar |
| **Éxito / Activo / Pagado** | `#2ab57d` | Coincide entre `bootstrap.css:34 --bs-success` y `style.css:42 .item-activo` — no tocar |
| **Peligro / acción destructiva** | `#d5453b` | `bootstrap.css:38 --bs-danger: #fd625e` (framework) y `style.css:48 .item-inactivo: #F52D00` (custom) son dos rojos distintos. Unificar a `#d5453b`. |
| **Peligro — borde suave de validación** | `#f46a6a` | `style.css:164` (`.is-invalid .form-control`) — **no tocar**, matiz más claro reservado a bordes de error inline. Intencional, no residuo. |
| **Info / Disponible** | `#3B82F6` | `bootstrap.css:35 --bs-info: #4ba6ef` (framework) vs `style.css:53 .item-disponible: #3B82F6` (custom) — unificar a `#3B82F6`. |
| **Atención / Pendiente** | `#b8790a` | No existe hoy como rol propio — se introduce para "Asignado" (que hoy reutiliza el verde de "Activo"). Bootstrap `--bs-warning: #ffbf53` queda intacto. |

### 3.2 Tipografía

Se mantiene **Open Sans** (`style.css:1`, vía `@import` de Google Fonts, pesos 300–800). No hay razón de negocio para migrar de fuente — el problema no es la tipografía sino que no tiene escala. Hoy casi todo corre en `font-size: 14px !important`.

Escala a introducir como variables CSS:

| Token | Tamaño | Peso | Uso |
|---|---|---|---|
| `--fs-page-title` | 24px | 700 | `<h4>` de cada vista (hoy 18px) |
| `--fs-section-title` | 16px | 700 | `card-title` |
| `--fs-body` | 14px | 400 | texto de tabla, párrafos — sin cambios |
| `--fs-label` | 13px | 600 | labels de formulario |
| `--fs-caption` | 12px | 600, uppercase, letter-spacing 0.04em | encabezados de columna, badges |

### 3.3 Spacing, radio, sombra

No se introduce escala nueva — Bootstrap 5 ya trae `--bs-border-radius: 0.375rem` (6px) sin override, y se mantiene por consistencia. Los componentes nuevos usan el mismo `--bs-border-radius`.

---

## 4. Fases de implementación

### Fase A1 — Corregir variables Bootstrap (violeta → teal)

**Por qué va primero:** es la causa raíz del violeta residual en focus rings, hovers y fondos sutiles de toda la app. Cambio quirúrgico de 4 valores en un archivo, cero riesgo.

**Archivos a tocar:** `app/public/assets/css/bootstrap.css` (líneas 40, 48, 56, 64). Las versiones dark-mode en líneas 145/153/161 son opcionales/baja prioridad (no hay toggle de dark mode activo) — dejarlas salvo que Edgardo pida lo contrario.

**Cambios exactos:**

```diff
- --bs-primary-rgb: 81, 86, 190;
+ --bs-primary-rgb: 45, 92, 108;
```
```diff
- --bs-primary-text-emphasis: #20224c;
+ --bs-primary-text-emphasis: #16232a;
```
```diff
- --bs-primary-bg-subtle: #b9bbe5;
+ --bs-primary-bg-subtle: #dde8ea;
```
```diff
- --bs-primary-border-subtle: #b9bbe5;
+ --bs-primary-border-subtle: #b9d2d7;
```

**Checklist de aceptación:**
- [ ] Ningún elemento destella violeta al hacer focus/hover (revisar con foco de teclado Tab sobre inputs de un formulario, checkboxes, botones, sidebar)
- [ ] Los 4 KPI del Tablero, el botón "Ingresar" del login y el sidebar siguen viéndose teal
- [ ] `grep -rn "81, 86, 190" app/public/assets/css/bootstrap.css` devuelve 0 resultados fuera de comentarios

---

### Fase A2 — Bloque de tokens en `style.css`

**Archivos a tocar:** `app/public/assets/css/style.css` — insertar el bloque al principio del archivo, después del `@import` de la fuente (línea 1) y antes de la regla `body` (línea 3).

**Bloque a insertar:**

```css
:root {
    /* Paleta semántica unificada — ver .doc/plan-diseno-claude.md sección 3.1 */
    --color-primary: #2D5C6C;
    --color-primary-rgb: 45, 92, 108;
    --color-primary-text-emphasis: #16232a;
    --color-primary-bg-subtle: #dde8ea;
    --color-primary-border-subtle: #b9d2d7;

    --color-success: #2ab57d;
    --color-success-bg: rgba(42, 181, 125, .18);

    --color-danger: #d5453b;
    --color-danger-bg: rgba(213, 69, 59, .18);
    --color-danger-soft: #f46a6a; /* solo bordes de validación inline, no tocar su uso actual */

    --color-info: #3B82F6;
    --color-info-bg: rgba(59, 130, 246, .18);

    --color-warn: #b8790a;
    --color-warn-bg: rgba(184, 121, 10, .18);

    /* Tipografía */
    --fs-page-title: 24px;
    --fs-section-title: 16px;
    --fs-body: 14px;
    --fs-label: 13px;
    --fs-caption: 12px;
}
```

**Checklist de aceptación:**
- [ ] El bloque está entre el `@import` (línea 1) y el `body` (línea 3, ahora corrida)
- [ ] `docker-compose restart php` + recargar `http://localhost` con DevTools → la calculadora de CSS resuelve `var(--color-primary)` a `#2D5C6C`
- [ ] No hay regresión visual en elementos que ya usaban `#2D5C6C` hardcodeado (`.logo-txt`, `.menu-title`)

---

### Fase A3 — CSS de componentes

**Archivos a tocar:** `app/public/assets/css/style.css` — agregar al final del archivo (después de las reglas de GLightbox existentes). Esta fase define **todas** las clases que MiniMax consumirá; se hace en un solo paso para que el contrato de interfaz (sección 2) quede completo antes o independientemente del markup.

**A3.1 — Badges de estado** (reemplaza `.item-*` y agrega `.badge-status`):

```css
/* ===== Badges de estado ===== */
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: var(--fs-caption);
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 100px;
}
.badge-status::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.badge-status.is-success { color: var(--color-success); background-color: var(--color-success-bg); }
.badge-status.is-success::before { background: var(--color-success); }
.badge-status.is-danger { color: var(--color-danger); background-color: var(--color-danger-bg); }
.badge-status.is-danger::before { background: var(--color-danger); }
.badge-status.is-info { color: var(--color-info); background-color: var(--color-info-bg); }
.badge-status.is-info::before { background: var(--color-info); }
.badge-status.is-warn { color: var(--color-warn); background-color: var(--color-warn-bg); }
.badge-status.is-warn::before { background: var(--color-warn); }

/* Compat: alias de las clases viejas mientras MiniMax migra las vistas.
   Borrar cuando no queden referencias a .item-*. */
.item-activo { color: var(--color-success) !important; background-color: var(--color-success-bg) !important; font-size: var(--fs-caption) !important; }
.item-inactivo { color: var(--color-danger) !important; background-color: var(--color-danger-bg) !important; font-size: var(--fs-caption) !important; }
.item-disponible { color: var(--color-info) !important; background-color: var(--color-info-bg) !important; font-size: var(--fs-caption) !important; }
```

**A3.2 — Acción destructiva en dropdown:**

```css
/* ===== Dropdown: acción destructiva ===== */
.dropdown-item.text-danger:hover,
.dropdown-item.text-danger:focus {
    background-color: var(--color-danger-bg);
    color: var(--color-danger) !important;
}
```

**A3.3 — KPI cards:**

```css
/* ===== KPI cards ===== */
.kpi-card {
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius);
    background: #fff;
    padding: 16px 18px;
}
.kpi-card-label {
    font-size: var(--fs-caption);
    color: var(--bs-secondary-color);
    display: flex;
    align-items: center;
    gap: 6px;
}
.kpi-card-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--bs-body-color);
    margin-top: 6px;
    font-variant-numeric: tabular-nums;
}
```

**A3.4 — Tablas (Datatables):**

```css
/* ===== Datatables: compactación y encabezados ===== */
.dataTable td, .dataTable th {
    padding-top: 10px;
    padding-bottom: 10px;
    vertical-align: middle;
}
.dataTable th {
    font-size: var(--fs-caption);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
    color: var(--bs-secondary-color);
}
.dataTable td.text-center, .dataTable th.text-center {
    text-align: center;
}
```

**A3.5 — Modales:**

```css
/* ===== Modales ===== */
.modal-header {
    border-bottom: 1px solid var(--bs-border-color);
    padding: 16px 24px;
}
.modal-header .modal-title {
    font-size: var(--fs-section-title);
    font-weight: 700;
}
.modal-footer {
    border-top: 1px solid var(--bs-border-color);
    padding: 14px 24px;
}
.modal-body {
    padding: 20px 24px;
}
```

**Checklist de aceptación:**
- [ ] Después de `docker-compose restart php`, en DevTools → Styles, todas las clases nuevas aparecen definidas (no "unknown property")
- [ ] En `dash-bathrooms.php` (que aún usa `.item-activo`), los badges siguen viéndose correctamente vía los alias de compat — el verde no cambió de tono
- [ ] Un `.badge-status.is-warn` de prueba (agregar temporalmente a cualquier vista) se ve ámbar, no verde — confirma que el rol nuevo funciona
- [ ] El modal de edición de cliente (`dash-customers-item.php`) respeta el padding/spacing nuevo sin romperse

---

### Fase A4 — KPI cards markup en `index.php`

**Archivos a tocar:** `app/public/index.php` — las 4 tarjetas de KPI del tablero.

**Markup nuevo** (reemplaza las 4 tarjetas actuales, que no tienen tendencia ni contexto):

```html
<div class="col-md-3">
    <div class="kpi-card">
        <div class="kpi-card-label">
            <i class="fas fa-toilet"></i> Baños
        </div>
        <div class="kpi-card-value"><?php echo $totalBaths; ?></div>
    </div>
</div>
```

Repetir el patrón para las 4 métricas del tablero (Baños, Contratos, Clientes, Servicios — verificar los nombres exactos de las variables `$total*` ya existentes en `index.php` antes de reemplazar; **no inventar variables nuevas**, usar las que ya calcula la vista).

**Checklist de aceptación:**
- [ ] Las 4 KPI cards se ven con borde sutil, label en caption gris y valor grande en tabular-nums
- [ ] Los valores numéricos son reales (mismos que antes del cambio)
- [ ] Responsive: en mobile se apilan en 1 columna (`col-md-3` lo maneja automático)

---

### Fase A5 — Backend "Contratos por vencer"

**Alcance mayor que las anteriores — requiere código PHP namespaced (DDD), no es solo CSS/HTML.** Sigue el patrón ya establecido en la migración DDD de Contract (ver `app/src/Domain/Contract/`, `app/src/Application/Contract/`, `app/src/Infrastructure/Persistence/MysqliContractRepository.php` como referencia).

**Objetivo:** proveer datos reales para un panel "Contratos por vencer" (contratos con `fechaFin_Contrato` dentro de los próximos 7 días) que MiniMax podría consumir, o que el mismo `index.php` pinta en Fase A4 extendida.

**A5.1 — Repository Interface** (`app/src/Domain/Contract/ContractRepositoryInterface.php`):

Agregar un método de lectura:
```php
/**
 * @return Contract[] Contracts whose fechaFin_Contrato falls within the next 7 days.
 */
public function findExpiringSoon(int $days = 7): array;
```

**A5.2 — Implementación** (`app/src/Infrastructure/Persistence/MysqliContractRepository.php`):

Implementar `findExpiringSoon()` con prepared statement (`$this->db->prepare()` + `bind_param('s', $threshold)`), calculando el threshold como `date('Y-m-d', strtotime("+{$days} days"))`. Seguir el patrón existente en los otros métodos del mismo archivo.

**A5.3 — Use Case** (`app/src/Application/Contract/ListContractsExpiringSoon.php`):

```php
<?php
namespace App\Application\Contract;

use App\Domain\Contract\ContractRepositoryInterface;

final class ListContractsExpiringSoon
{
    private ContractRepositoryInterface $repository;

    public function __construct(ContractRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array<\App\Domain\Contract\Contract>
     */
    public function execute(int $days = 7): array
    {
        return $this->repository->findExpiringSoon($days);
    }
}
```

**A5.4 — Autoload:**
```bash
docker-compose exec php composer dump-autoload
```

**A5.5 — Panel en `index.php`** (opcional, si Edgardo confirma incluirlo):

Invocar el Use Case y pintar el panel. Dejar el markup del panel para coordinar con MiniMax si decide estilarlo, o usar el CSS de `.kpi-card` existente como base. **No agregar SQL directo en la vista** — siempre vía el Use Case.

**Checklist de aceptación:**
- [ ] `docker-compose exec php composer dump-autoload` no reporta errores
- [ ] `docker-compose exec php php -l app/src/Application/Contract/ListContractsExpiringSoon.php` → `No syntax errors`
- [ ] Un contrato con `fechaFin_Contrato` en 5 días aparece en el resultado; uno en 30 días no
- [ ] La consulta no genera N+1 (un solo `SELECT`, no un query por contrato)
- [ ] `docker-compose up -d --force-recreate php` + `http://localhost` carga sin errores 500

---

## 5. Verificación de integración final (cuando MiniMax también terminó)

Cuando ambas mitades del plan están implementadas, verificar que el resultado combinado es coherente:

1. **Badges:** en cada vista de listado, los `.badge-status` se ven con el color correcto del rol (verde=Activo/Pagado, rojo=Inactivo/Anulado, azul=Disponible, ámbar=Asignado). "Asignado" y "Activo" en `dash-bathrooms.php` ya no comparten color.
2. **Botones agrupados:** cada fila de las 7 vistas muestra máximo 2 elementos de acción visibles; la destructiva es roja dentro del dropdown.
3. **Confirmaciones:** las 5 vistas que antes no tenían aviso ahora muestran `Swal.fire` antes de ejecutar la acción destructiva.
4. **Formularios:** los 12 formularios se ven en 2 columnas en desktop, 1 en mobile, y los errores de PristineJS siguen pintándose bajo cada campo.
5. **Sin violeta residual:** `grep -rn "5156be\|81, 86, 190\|#5b73e8" app/public/assets/css/ app/public/dash-*.php` devuelve 0 resultados fuera de comentarios.
6. **Tablero:** los 4 KPI renderizan con datos reales y (si se hizo Fase A5) el panel de contratos por vencer muestra datos filtrados por fecha.

---

## 6. Fuera de alcance de este documento

- Cualquier cambio al markup HTML de `dash-*.php` (vistas de listado o formularios) — eso es MiniMax.
- Migración de tipografía a Inter/Geist — descartada, Open Sans se mantiene.
- Unificación de las 4 librerías de íconos a una sola — alto esfuerzo, bajo beneficio.
- Dark mode — no hay toggle activo, las variables `[data-bs-theme=dark]` no se tocan.
- Cambios a `app/public/archive/` o `app/public/assets/libs/`.
- Rotación de la password `Guns026772` (hallazgo de seguridad, no relacionado a diseño) — señalado aparte a Edgardo.
