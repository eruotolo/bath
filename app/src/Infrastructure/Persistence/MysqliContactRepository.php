<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contact\Contact;
use App\Domain\Contact\ContactRepositoryInterface;
use mysqli;

final class MysqliContactRepository implements ContactRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function findById(int $id): ?Contact
    {
        $stmt = $this->connection->prepare('SELECT * FROM contactos WHERE id_Contacto = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrate($row) : null;
    }

    public function insert(Contact $contact): int
    {
        $customerId = $contact->customerId;
        $name = $contact->name;
        $lastname = $contact->lastname;
        $rut = $contact->rut;
        $phone = $contact->phone;
        $address = $contact->address;
        $state = $contact->state;

        $stmt = $this->connection->prepare(
            'INSERT INTO contactos (id_Cliente, nombre_Contacto, apellido_Contacto, rut_Contacto, telefono_Contacto, direccion_Contacto, estado_Contacto)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('isssssi', $customerId, $name, $lastname, $rut, $phone, $address, $state);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function update(Contact $contact): void
    {
        $id = $contact->id;
        $customerId = $contact->customerId;
        $name = $contact->name;
        $lastname = $contact->lastname;
        $rut = $contact->rut;
        $phone = $contact->phone;
        $address = $contact->address;
        $observation = $contact->observation;

        $stmt = $this->connection->prepare(
            'UPDATE contactos SET id_Cliente = ?, nombre_Contacto = ?, apellido_Contacto = ?, rut_Contacto = ?,
             telefono_Contacto = ?, direccion_Contacto = ?, observacion_Contacto = ? WHERE id_Contacto = ?'
        );
        $stmt->bind_param('issssssi', $customerId, $name, $lastname, $rut, $phone, $address, $observation, $id);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare('DELETE FROM contactos WHERE id_Contacto = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function listActiveByCustomer(int $customerId): array
    {
        $stmt = $this->connection->prepare('SELECT * FROM contactos WHERE id_Cliente = ? AND estado_Contacto = 1');
        $stmt->bind_param('i', $customerId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    private function hydrate(array $row): Contact
    {
        return new Contact(
            id: (int) $row['id_Contacto'],
            customerId: (int) $row['id_Cliente'],
            name: $row['nombre_Contacto'],
            lastname: $row['apellido_Contacto'],
            rut: $row['rut_Contacto'],
            phone: $row['telefono_Contacto'],
            address: $row['direccion_Contacto'],
            observation: $row['observacion_Contacto'],
            state: (int) $row['estado_Contacto'],
        );
    }
}
