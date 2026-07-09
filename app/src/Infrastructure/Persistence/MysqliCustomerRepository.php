<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;
use mysqli;

final class MysqliCustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function findById(int $id): ?Customer
    {
        $stmt = $this->connection->prepare('SELECT * FROM clientes WHERE id_Cliente = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrate($row) : null;
    }

    public function save(Customer $customer): int
    {
        $rut = $customer->rut;
        $name = $customer->name;
        $phone = $customer->phone;
        $email = $customer->email;
        $address = $customer->address;
        $region = $customer->region;
        $city = $customer->city;
        $commune = $customer->commune;

        if ($customer->id === null) {
            $active = (int) $customer->active;
            $stmt = $this->connection->prepare(
                'INSERT INTO clientes (rut_Cliente, nombre_Cliente, telefono_Cliente, email_Cliente,
                 direccion_Cliente, region_Cliente, ciudad_Cliente, comuna_Cliente, estado_Cliente)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'ssssssssi',
                $rut, $name, $phone, $email, $address, $region, $city, $commune, $active
            );
            $stmt->execute();

            return $stmt->insert_id;
        }

        // Nota: el UPDATE original siempre reactivaba al cliente (estado_Cliente = 1),
        // y es el unico mecanismo existente para reactivar uno inactivo -> se preserva.
        $id = $customer->id;
        $active = (int) $customer->active;
        $stmt = $this->connection->prepare(
            'UPDATE clientes SET rut_Cliente = ?, nombre_Cliente = ?, telefono_Cliente = ?,
             email_Cliente = ?, direccion_Cliente = ?, region_Cliente = ?, ciudad_Cliente = ?,
             comuna_Cliente = ?, estado_Cliente = ? WHERE id_Cliente = ?'
        );
        $stmt->bind_param(
            'ssssssssii',
            $rut, $name, $phone, $email, $address, $region, $city, $commune, $active, $id
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

    public function count(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) AS total FROM clientes');

        return (int) $result->fetch_assoc()['total'];
    }

    public function listActive(): array
    {
        $result = $this->connection->query('SELECT * FROM clientes WHERE estado_Cliente = 1');

        $customers = [];
        foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
            $customers[] = $this->hydrate($row);
        }

        return $customers;
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
