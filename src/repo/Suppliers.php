<?php
// Proveedores externos (glpi_suppliers) y su vínculo con tickets (glpi_suppliers_tickets type=2).
class Suppliers
{
    public static function all(): array
    {
        return q_all("SELECT id, name, email, phonenumber FROM glpi_suppliers WHERE is_deleted = 0 ORDER BY name");
    }

    public static function get(int $id): ?array
    {
        return q_one("SELECT id, name, email, phonenumber FROM glpi_suppliers WHERE id = :id AND is_deleted = 0", [':id' => $id]);
    }

    // Crea (o reutiliza por nombre) un proveedor. Devuelve su id.
    public static function findOrCreate(string $name, string $email = '', string $phone = ''): int
    {
        $name = trim($name);
        if ($name === '') return 0;
        $existing = q_val("SELECT id FROM glpi_suppliers WHERE name = :n AND is_deleted = 0 LIMIT 1", [':n' => $name]);
        if ($existing) return (int)$existing;
        db()->prepare(
            "INSERT INTO glpi_suppliers (entities_id, is_recursive, name, email, phonenumber, date_creation, date_mod)
             VALUES (0, 1, :n, :e, :p, NOW(), NOW())"
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
