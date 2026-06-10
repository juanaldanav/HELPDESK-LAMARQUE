<?php
// Proveedores externos (glpi_suppliers) y su vínculo con tickets (glpi_suppliers_tickets type=2).
class Suppliers
{
    // Proveedores para selección en tickets: solo autorizados (is_active=1), no borrados.
    public static function all(): array
    {
        return q_all("SELECT id, name, email, phonenumber FROM glpi_suppliers WHERE is_deleted = 0 AND is_active = 1 ORDER BY name");
    }

    // Catálogo completo para el CRUD de Dalia (incluye no autorizados) + conteo de usos.
    public static function manage(): array
    {
        return q_all(
            "SELECT s.id, s.name, s.email, s.phonenumber, s.is_active,
                    (SELECT COUNT(*) FROM glpi_suppliers_tickets st WHERE st.suppliers_id = s.id) AS usos
             FROM glpi_suppliers s
             WHERE s.is_deleted = 0
             ORDER BY s.is_active DESC, s.name"
        );
    }

    public static function get(int $id): ?array
    {
        return q_one("SELECT id, name, email, phonenumber, is_active FROM glpi_suppliers WHERE id = :id AND is_deleted = 0", [':id' => $id]);
    }

    // Crea un proveedor desde el CRUD (con autorización explícita). Devuelve [ok, id|error].
    public static function createOne(string $name, string $email, string $phone, bool $active): array
    {
        $name = trim($name);
        if ($name === '') return ['ok' => false, 'error' => 'El nombre es obligatorio.'];
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'error' => 'Correo inválido.'];
        if (q_val("SELECT id FROM glpi_suppliers WHERE name = :n AND is_deleted = 0", [':n' => $name])) {
            return ['ok' => false, 'error' => 'Ya existe un proveedor con ese nombre.'];
        }
        db()->prepare(
            "INSERT INTO glpi_suppliers (entities_id, is_recursive, name, email, phonenumber, is_active, date_creation, date_mod)
             VALUES (0, 1, :n, :e, :p, :a, NOW(), NOW())"
        )->execute([':n' => $name, ':e' => trim($email), ':p' => trim($phone), ':a' => $active ? 1 : 0]);
        return ['ok' => true, 'id' => (int) db()->lastInsertId()];
    }

    // Edita datos del proveedor. Devuelve [ok, error?].
    public static function update(int $id, string $name, string $email, string $phone, bool $active): array
    {
        $name = trim($name);
        if ($name === '') return ['ok' => false, 'error' => 'El nombre es obligatorio.'];
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'error' => 'Correo inválido.'];
        $dup = q_val("SELECT id FROM glpi_suppliers WHERE name = :n AND id <> :id AND is_deleted = 0", [':n' => $name, ':id' => $id]);
        if ($dup) return ['ok' => false, 'error' => 'Otro proveedor ya usa ese nombre (usa Fusionar).'];
        db()->prepare(
            "UPDATE glpi_suppliers SET name = :n, email = :e, phonenumber = :p, is_active = :a, date_mod = NOW() WHERE id = :id"
        )->execute([':n' => $name, ':e' => trim($email), ':p' => trim($phone), ':a' => $active ? 1 : 0, ':id' => $id]);
        return ['ok' => true];
    }

    // Borrado lógico (conserva histórico de tickets ligados).
    public static function softDelete(int $id): bool
    {
        if ($id <= 0) return false;
        db()->prepare("UPDATE glpi_suppliers SET is_deleted = 1, date_mod = NOW() WHERE id = :id")->execute([':id' => $id]);
        return true;
    }

    // Fusiona el proveedor $fromId dentro de $intoId: repunta los tickets y elimina el origen.
    public static function merge(int $fromId, int $intoId): array
    {
        if ($fromId <= 0 || $intoId <= 0 || $fromId === $intoId) return ['ok' => false, 'error' => 'Selección inválida.'];
        if (!self::get($intoId)) return ['ok' => false, 'error' => 'El proveedor destino no existe.'];
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // Evita duplicar el vínculo si ambos ya están en el mismo ticket.
            $pdo->prepare(
                "DELETE st FROM glpi_suppliers_tickets st
                 JOIN glpi_suppliers_tickets keep
                   ON keep.tickets_id = st.tickets_id AND keep.type = st.type AND keep.suppliers_id = :into
                 WHERE st.suppliers_id = :from AND st.type = 2"
            )->execute([':into' => $intoId, ':from' => $fromId]);
            $pdo->prepare("UPDATE glpi_suppliers_tickets SET suppliers_id = :into WHERE suppliers_id = :from")
                ->execute([':into' => $intoId, ':from' => $fromId]);
            $pdo->prepare("UPDATE glpi_suppliers SET is_deleted = 1, date_mod = NOW() WHERE id = :from")
                ->execute([':from' => $fromId]);
            $pdo->commit();
            return ['ok' => true];
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    // Crea (o reutiliza por nombre) un proveedor. Devuelve su id.
    public static function findOrCreate(string $name, string $email = '', string $phone = ''): int
    {
        $name = trim($name);
        if ($name === '') return 0;
        $existing = q_val("SELECT id FROM glpi_suppliers WHERE name = :n AND is_deleted = 0 LIMIT 1", [':n' => $name]);
        if ($existing) return (int)$existing;
        // Creado al vuelo desde un ticket → autorizado por defecto (is_active=1) para que aparezca en el selector.
        db()->prepare(
            "INSERT INTO glpi_suppliers (entities_id, is_recursive, name, email, phonenumber, is_active, date_creation, date_mod)
             VALUES (0, 1, :n, :e, :p, 1, NOW(), NOW())"
        )->execute([':n' => $name, ':e' => trim($email), ':p' => trim($phone)]);
        return (int) db()->lastInsertId();
    }

    // Proveedor asignado a un ticket (el primero type=2), o null.
    public static function ofTicket(int $ticketId): ?array
    {
        return q_one(
            "SELECT s.id, s.name, s.email, s.phonenumber
             FROM glpi_suppliers_tickets st JOIN glpi_suppliers s ON s.id = st.suppliers_id
             WHERE st.tickets_id = :t AND st.type = 2 ORDER BY st.id LIMIT 1",
            [':t' => $ticketId]
        );
    }

    // Vincula proveedor al ticket (reemplaza el anterior).
    public static function assign(int $ticketId, int $supplierId): void
    {
        db()->prepare("DELETE FROM glpi_suppliers_tickets WHERE tickets_id = :t AND type = 2")->execute([':t' => $ticketId]);
        if ($supplierId > 0) {
            db()->prepare("INSERT INTO glpi_suppliers_tickets (tickets_id, suppliers_id, type, use_notification) VALUES (:t, :s, 2, 1)")
                ->execute([':t' => $ticketId, ':s' => $supplierId]);
        }
    }
}
