# Plan de migración: DDD (liviano) + DRY Code

**Proyecto:** php-bathroom
**Fecha del análisis:** 2026-07-06
**Relacionado:** [`plan-migracion-php-8.5.md`](./plan-migracion-php-8.5.md)

---

## 1. Resumen ejecutivo

El proyecto hoy es 100% procedural: 94 vistas (`dash-*.php`) + 38 controllers de acción (`controller/*.php`) + `layouts/` compartido, sin una sola clase PHP, sin autoload, sin capa de dominio. Confirmé en 4 archivos representativos (`bath-new.php`, `customer-new.php`, `customer-update.php`, `customer-get.php`) que **las 7 entidades del sistema repiten exactamente el mismo esqueleto** de acceso a datos, y que ese esqueleto arma el SQL por concatenación directa de `$_POST` — es decir, DRY roto y SQL Injection sistemática son **la misma causa raíz**: no existe una capa que centralice el acceso a datos.

Esto es una buena noticia práctica: **el mismo refactor que resuelve DDD/DRY también elimina la inyección SQL de raíz**, porque ambos requieren mover el acceso a datos a un solo lugar por entidad, con prepared statements.

**Importante — qué DDD vamos a aplicar y qué no:** este proyecto es un panel administrativo con 7 entidades y reglas de negocio simples (altas, bajas, cambios de estado, asociaciones). Eso es un dominio de complejidad baja/media. Aplicar DDD "de libro completo" (Aggregate Roots complejos, Domain Events, CQRS, Bounded Contexts separados) sería sobre-ingeniería para este caso — va contra los mismos principios de KISS/YAGNI que ya seguís. Lo que sí vale la pena, y es exactamente lo que preguntaste, es la parte **táctica** de DDD que da la mayor ganancia con el menor costo:

- **Entities** (objetos con identidad, en vez de arrays sueltos de `mysqli_fetch_array`)
- **Repositories** (las consultas/API que mencionás, separadas y organizadas)
- **Application Services / Use Cases** (reemplazan los `controller/*.php` actuales como orquestadores)
- Sin Aggregates multi-entidad, sin Domain Events, sin CQRS — no hacen falta acá.

---

## 2. Respuesta directa a tu pregunta: "¿las consultas que son la API podrían estar separadas?"

Sí, y de hecho ya existe el embrión de eso sin que lo hayan planeado: revisé `controller/customer-get.php`, `controller/bath-get.php`, `controller/obtener_contratos.php` — son controllers que **ya devuelven `json_encode(...)`**, es decir, funcionalmente ya son endpoints de API, solo que:

1. Están mezclados en la misma carpeta que los controllers que hacen `redirect` (HTML), sin distinción.
2. Cada uno arma su propio SQL a mano, repetido entidad por entidad.
3. No hay una capa intermedia — la vista, la query y el HTTP están en el mismo archivo.

La solución (sección 4) es exactamente separar esa lógica de acceso a datos en clases **Repository**, una por entidad, en una carpeta de dominio propia — y que tanto los controllers HTML como los que devuelven JSON llamen al mismo Repository. Cero SQL duplicado entre "la vista de la tabla" y "el endpoint que devuelve JSON para un modal", que hoy son dos consultas idénticas escritas dos veces.

---

## 3. Arquitectura objetivo

```
php-bathroom/
├── app/
│   ├── src/                                    ← NUEVO: código orientado a objetos, con namespaces
│   │   ├── Domain/
│   │   │   ├── Customer/
│   │   │   │   ├── Customer.php                (Entity)
│   │   │   │   └── CustomerRepositoryInterface.php
│   │   │   ├── Bathroom/
│   │   │   ├── Contract/
│   │   │   ├── Invoice/
│   │   │   ├── Service/
│   │   │   ├── Certificate/
│   │   │   └── User/
│   │   │
│   │   ├── Application/
│   │   │   ├── Customer/
│   │   │   │   ├── CreateCustomer.php          (Use Case)
│   │   │   │   ├── UpdateCustomer.php
│   │   │   │   ├── FindCustomer.php
│   │   │   │   └── ListCustomers.php
│   │   │   └── ... (una carpeta por entidad, mismo patrón)
│   │   │
│   │   ├── Infrastructure/
│   │   │   ├── Database/
│   │   │   │   └── MysqliConnection.php        (reemplaza el $link global de config.php)
│   │   │   └── Persistence/
│   │   │       ├── MysqliCustomerRepository.php (implementa la interface, con prepared statements)
│   │   │       └── ... (una por entidad)
│   │   │
│   │   └── Shared/
│   │       ├── Rut.php                          (Value Object, valida/formatea RUT chileno una sola vez)
│   │       └── Session.php                       (reemplaza layouts/session.php)
│   │
│   └── public/
│       ├── controller/                          ← se ADELGAZAN, no desaparecen
│       │   └── customer-new.php                 (ahora: 8 líneas, delega a Application\Customer\CreateCustomer)
│       └── dash-*.php                            (vistas, sin cambios en esta fase)
│
├── composer.json                                 ← NUEVO: autoload PSR-4 + prepared statements
```

**Por qué convive `controller/` viejo con `src/` nuevo:** la migración es incremental (sección 6, patrón *strangler fig*). Cada controller existente pasa, uno por uno, de "tener la lógica adentro" a "llamar un Use Case de `src/`". Las vistas (`dash-*.php`) no se tocan en esta fase — siguen recibiendo los mismos datos que antes, solo que ahora vienen de un Repository en vez de un `mysqli_query` inline.

---

## 4. Ejemplo completo: migrando la entidad `Customer`

Esta es la plantilla que se repite para las otras 6 entidades (bathrooms, contracts, invoices, services, certificates, users).

### 4.1 — Prerrequisito: `composer.json` con autoload PSR-4

Hoy no existe `composer.json` a nivel de proyecto (TCPDF y PHPMailer están vendorizados a mano). Hace falta uno para poder usar namespaces y autoload:

```json
{
    "name": "php-bathroom/app",
    "autoload": {
        "psr-4": {
            "App\\": "app/src/"
        }
    }
}
```

Luego `composer dump-autoload`, y en `layouts/config.php` (o en un nuevo `bootstrap.php`) agregar `require __DIR__ . '/../../vendor/autoload.php';`.

### 4.2 — Domain: la Entity

```php
<?php
// app/src/Domain/Customer/Customer.php
namespace App\Domain\Customer;

final class Customer
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $rut,
        public readonly string $name,
        public readonly string $phone,
        public readonly string $email,
        public readonly string $address,
        public readonly string $region,
        public readonly string $city,
        public readonly string $commune,
        public readonly bool $active = true,
    ) {}
}
```

### 4.3 — Domain: la interface del Repository (el contrato)

```php
<?php
// app/src/Domain/Customer/CustomerRepositoryInterface.php
namespace App\Domain\Customer;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;
    public function save(Customer $customer): int;   // insert o update, devuelve el id
    public function setInactive(int $id): void;
}
```

### 4.4 — Infrastructure: la implementación real (acá muere el SQL Injection)

```php
<?php
// app/src/Infrastructure/Persistence/MysqliCustomerRepository.php
namespace App\Infrastructure\Persistence;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;
use mysqli;

final class MysqliCustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function findById(int $id): ?Customer
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM clientes WHERE id_Cliente = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrate($row) : null;
    }

    public function save(Customer $customer): int
    {
        if ($customer->id === null) {
            $stmt = $this->connection->prepare(
                'INSERT INTO clientes (rut_Cliente, nombre_Cliente, telefono_Cliente, email_Cliente,
                 direccion_Cliente, region_Cliente, ciudad_Cliente, comuna_Cliente, estado_Cliente)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'ssssssssi',
                $customer->rut, $customer->name, $customer->phone, $customer->email,
                $customer->address, $customer->region, $customer->city, $customer->commune,
                $customer->active
            );
            $stmt->execute();
            return $stmt->insert_id;
        }

        $stmt = $this->connection->prepare(
            'UPDATE clientes SET rut_Cliente = ?, nombre_Cliente = ?, telefono_Cliente = ?,
             email_Cliente = ?, direccion_Cliente = ?, region_Cliente = ?, ciudad_Cliente = ?,
             comuna_Cliente = ? WHERE id_Cliente = ?'
        );
        $stmt->bind_param(
            'ssssssssi',
            $customer->rut, $customer->name, $customer->phone, $customer->email,
            $customer->address, $customer->region, $customer->city, $customer->commune,
            $customer->id
        );
        $stmt->execute();
        return $customer->id;
    }

    public function setInactive(int $id): void
    {
        $stmt = $this->connection->prepare('UPDATE clientes SET estado_Cliente = 0 WHERE id_Cliente = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    private function hydrate(array $row): Customer
    {
        return new Customer(
            id: (int) $row['id_Cliente'],
            rut: $row['rut_Cliente'],
            name: $row['nombre_Cliente'],
            phone: $row['telefono_Cliente'],
            email: $row['email_Cliente'],
            address: $row['direccion_Cliente'],
            region: $row['region_Cliente'],
            city: $row['ciudad_Cliente'],
            commune: $row['comuna_Cliente'],
            active: (bool) $row['estado_Cliente'],
        );
    }
}
```

Notá que **este es el único archivo de todo el proyecto que sabe que la tabla se llama `clientes`** o que las columnas están en español con sufijo `_Cliente`. Si mañana cambia el motor de base de datos o el nombre de una columna, se toca un solo archivo.

### 4.5 — Application: el Use Case (reemplaza la lógica que hoy está en el controller)

```php
<?php
// app/src/Application/Customer/CreateCustomer.php
namespace App\Application\Customer;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;

final class CreateCustomer
{
    public function __construct(private readonly CustomerRepositoryInterface $repository) {}

    public function handle(array $input): int
    {
        $customer = new Customer(
            id: null,
            rut: $input['rut_Cliente'],
            name: $input['nombre_Cliente'],
            phone: $input['telefono_Cliente'],
            email: $input['email_Cliente'],
            address: $input['direccion_Cliente'],
            region: $input['region_Cliente'],
            city: $input['ciudad_Cliente'],
            commune: $input['comuna_Cliente'],
        );

        return $this->repository->save($customer);
    }
}
```

### 4.6 — Presentation: el controller queda delgado (esto es lo que se ve en `public/`)

```php
<?php
// app/public/controller/customer-new.php — ANTES: 35 líneas con SQL inline. AHORA:
require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Customer\CreateCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

session_start();
$link = require __DIR__ . '/../layouts/bootstrap-db.php'; // devuelve el mysqli connection

if (!isset($_POST['crear'])) {
    echo '<script>alert("No se pudo crear el cliente")</script>';
    exit;
}

$useCase = new CreateCustomer(new MysqliCustomerRepository($link));
$useCase->handle($_POST);

header('Location: ../dash-customers.php');
```

Y el endpoint JSON (`customer-get.php`, hoy con SQL Injection en el `WHERE`) queda:

```php
<?php
// app/public/controller/customer-get.php — usa el MISMO Repository que customer-new.php
require __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Persistence\MysqliCustomerRepository;

session_start();
$link = require __DIR__ . '/../layouts/bootstrap-db.php';

$repository = new MysqliCustomerRepository($link);
$customer = $repository->findById((int) $_POST['id_Cliente']);

header('Content-Type: application/json');
echo $customer ? json_encode(get_object_vars($customer)) : json_encode(['error' => 'No encontrado']);
```

Cero SQL en el controller. Cero duplicación entre el endpoint HTML y el JSON — ambos llaman al mismo `MysqliCustomerRepository`.

---

## 5. Cómo esto resuelve cada problema detectado

| Problema detectado | Cómo lo resuelve esta arquitectura |
|---|---|
| SQL Injection en `bath-new.php`, `customer-new.php`, `customer-update.php`, `customer-get.php` (y previsiblemente el resto) | Prepared statements centralizados en `Infrastructure/Persistence/*Repository.php` — imposible construir SQL con concatenación una vez migrado |
| DRY roto: mismo esqueleto repetido en 38 controllers | El esqueleto pasa a vivir una sola vez por entidad, en el Repository + Use Case; el controller queda en ~10 líneas siempre iguales |
| XSS en `dash-customers.php` (`echo $row['nombre_Cliente']` sin escapar) | No lo resuelve directamente (es capa de vista, no de dominio) — queda como tarea aparte: envolver los `echo` de vistas con `htmlspecialchars()`. Se puede hacer entidad por entidad junto con esta migración |
| Credenciales hardcodeadas en `config.php` | `Infrastructure/Database/MysqliConnection.php` es el lugar natural para leer credenciales desde variables de entorno en vez de constantes hardcodeadas |
| "Las consultas que son la API deberían estar separadas" (tu pregunta) | Exactamente lo que hace `Infrastructure/Persistence/` — una clase Repository por entidad, sin importar si el consumidor final es una vista HTML o un endpoint JSON |

---

## 6. Plan de migración incremental (strangler fig, entidad por entidad)

**No se migra todo de una vez.** Se hace un "molde" con la entidad más simple, se valida en producción, y se repite el patrón para las demás.

### Orden sugerido (de menor a mayor riesgo/complejidad)

1. **Certificates** (3 páginas, sin relaciones complejas con otras entidades) → piloto
2. **Bathrooms** (5 archivos, relación simple con contratos)
3. **Users** (4 archivos, cuidado extra por ser autenticación)
4. **Customers** (ya ejemplificado arriba)
5. **Contracts** (relaciona customers + bathrooms — requiere que esas dos ya estén migradas)
6. **Services** (relaciona contracts)
7. **Invoices** (el más complejo, depende de services — se migra al final)

### Pasos por cada entidad

1. Crear `Domain/{Entity}/` (Entity + Repository interface).
2. Crear `Infrastructure/Persistence/Mysqli{Entity}Repository.php` con prepared statements — **este paso ya elimina el SQL Injection de esa entidad, aunque el resto del refactor no esté terminado**.
3. Crear `Application/{Entity}/` con un Use Case por acción existente hoy (new, update, get, inactive, delete...).
4. Reemplazar el contenido de cada `controller/{entity}-*.php` para que delegue al Use Case correspondiente (el archivo sigue existiendo, en la misma ruta — las vistas que lo llaman no se tocan).
5. QA manual de esa entidad (alta, edición, baja, listado).
6. Commit atómico por entidad migrada.

### Qué NO tocar en esta fase
- Las vistas `dash-*.php` (HTML) no cambian de estructura, solo dejan de tener SQL inline si lo tuvieran.
- No se introduce un framework (Laravel/Symfony) — se mantiene PHP plano + Composer solo para autoload, según lo conversado.
- No se arma Aggregate Roots ni Domain Events — no hay casos de uso en este dominio que los justifiquen.

---

## 7. Checklist de "Definition of Done" por entidad migrada

- [ ] Existe `Domain/{Entity}/{Entity}.php` (Entity) y `{Entity}RepositoryInterface.php`
- [ ] Existe `Infrastructure/Persistence/Mysqli{Entity}Repository.php` — **100% prepared statements, cero concatenación de SQL**
- [ ] Existe un Use Case en `Application/{Entity}/` por cada acción (new/update/get/inactive/delete)
- [ ] Los `controller/{entity}-*.php` correspondientes ya no contienen SQL, solo delegan
- [ ] QA manual: alta, edición, baja y listado de la entidad funcionan igual que antes
- [ ] El endpoint JSON de esa entidad (si existe) usa el mismo Repository que la vista HTML

---

## 8. Alcance explícito — qué queda fuera de este plan

- **CQRS, Event Sourcing, Domain Events, Bounded Contexts múltiples**: no aportan valor a un panel administrativo de 7 entidades con reglas simples. Agregarlos sería la sobre-ingeniería que tu propia filosofía de trabajo (KISS/YAGNI) descarta.
- **Escapar output en vistas (XSS)**: se puede hacer en paralelo, entidad por entidad, pero es un cambio de capa de presentación, no de arquitectura — no depende de este plan para poder ejecutarse antes.
- **Migración de PHP 8.1 → 8.5**: documentado por separado en [`plan-migracion-php-8.5.md`](./plan-migracion-php-8.5.md). Recomendación de orden: conviene resolver primero la entidad piloto de este plan (para validar que Composer/autoload/prepared statements funcionan bien en el entorno actual) antes de empezar a subir de versión de PHP, ya que ambos cambios tocan `config.php`/la conexión a base de datos.
