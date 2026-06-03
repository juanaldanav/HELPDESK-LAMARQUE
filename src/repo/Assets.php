<?php
// Activos: 8 tablas GenericObject + glpi_computers. Ver schema.md.
class Assets
{
    // Catálogo de tipos. table/class son constantes (no input) → seguras para interpolar.
    const TYPES = [
        ['key' => 'refrigeracion',         'table' => 'glpi_plugin_genericobject_refrigeracions',         'class' => 'PluginGenericobjectRefrigeracion',         'label' => 'Refrigeración',          'code' => 'REF'],
        ['key' => 'maquinariadecafe',      'table' => 'glpi_plugin_genericobject_maquinariadecafes',      'class' => 'PluginGenericobjectMaquinariadecafe',      'label' => 'Maquinaria de Café',     'code' => 'MCF'],
        ['key' => 'mobiliario',            'table' => 'glpi_plugin_genericobject_mobiliarios',            'class' => 'PluginGenericobjectMobiliario',            'label' => 'Mobiliario',             'code' => 'MOB'],
        ['key' => 'electronicayseguridad', 'table' => 'glpi_plugin_genericobject_electronicayseguridads', 'class' => 'PluginGenericobjectElectronicayseguridad', 'label' => 'Electrónica y Seguridad', 'code' => 'ESG'],
        ['key' => 'jardineria',            'table' => 'glpi_plugin_genericobject_jardinerias',            'class' => 'PluginGenericobjectJardineria',            'label' => 'Jardinería',             'code' => 'JRD'],
        ['key' => 'televisione',           'table' => 'glpi_plugin_genericobject_televisiones',           'class' => 'PluginGenericobjectTelevisione',           'label' => 'Televisiones',           'code' => 'TV'],
        ['key' => 'utensilio',             'table' => 'glpi_plugin_genericobject_utensilios',             'class' => 'PluginGenericobjectUtensilio',             'label' => 'Utensilios',             'code' => 'UTL'],
        ['key' => 'herramienta_soporte',   'table' => 'glpi_plugin_genericobject_herramientasoportes',    'class' => 'PluginGenericobjectHerramientasoporte',    'label' => 'Herramientas',           'code' => 'HRS'],
        ['key' => 'computer',              'table' => 'glpi_computers',                                   'class' => 'Computer',                                 'label' => 'Cómputo / POS',          'code' => 'CPT'],
    ];

    const STATES = [1 => 'Activo', 2 => 'En Reparación', 3 => 'Baja', 4 => 'En Bodega', 5 => 'Pendiente'];

    public static function typeByKey(string $key): ?array
    {
        foreach (self::TYPES as $t) if ($t['key'] === $key) return $t;
        return null;
    }
    public static function typeByClass(string $class): ?array
    {
        foreach (self::TYPES as $t) if ($t['class'] === $class) return $t;
        return null;
    }

    // Conteo por categoría para una entidad (desglose). entityId null = todas (Dalia).
    public static function categoryCounts(?int $entityId): array
    {
        $out = [];
        foreach (self::TYPES as $t) {
            $sql = "SELECT COUNT(*) FROM {$t['table']} WHERE is_deleted = 0";
            $p = [];
            if ($entityId !== null) { $sql .= " AND entities_id = :e"; $p[':e'] = $entityId; }
            $c = (int) q_val($sql, $p);
            $out[] = ['key' => $t['key'], 'label' => $t['label'], 'code' => $t['code'], 'count' => $c];
        }
        return $out;
    }

    // Items de un tipo, filtrado por entidad y búsqueda opcional. entityId null = todas (Dalia).
    public static function items(string $typeKey, ?int $entityId, ?string $search = null, int $limit = 500): array
    {
        $t = self::typeByKey($typeKey);
        if (!$t) return [];
        $sql = "SELECT a.id, a.name, a.serial, a.otherserial, a.states_id, a.comment, a.entities_id,
                       e.name AS entity_name
                FROM {$t['table']} a
                LEFT JOIN glpi_entities e ON e.id = a.entities_id
                WHERE a.is_deleted = 0";
        $p = [];
        if ($entityId !== null) { $sql .= " AND a.entities_id = :e"; $p[':e'] = $entityId; }
        if ($search) { $sql .= " AND (a.name LIKE :q OR a.serial LIKE :q)"; $p[':q'] = '%' . $search . '%'; }
        $sql .= " ORDER BY a.serial ASC LIMIT " . (int)$limit;
        $rows = q_all($sql, $p);
        foreach ($rows as &$r) {
            $r['type_key']   = $t['key'];
            $r['type_label'] = $t['label'];
            $r['itemtype']   = $t['class'];
            $r['state_name'] = self::STATES[(int)$r['states_id']] ?? '—';
            $r['drive_url']  = self::driveUrl($r['comment'] ?? '');
        }
        return $rows;
    }

    // Activos de una entidad agrupados por categoría (para <select> con optgroups al levantar ticket).
    public static function forEntityGroupedSelect(?int $entityId): array
    {
        $out = [];
        foreach (self::TYPES as $t) {
            $rows = q_all(
                "SELECT id, name, serial FROM {$t['table']}
                 WHERE is_deleted = 0" . ($entityId !== null ? " AND entities_id = :e" : "") . "
                 ORDER BY name, serial LIMIT 500",
                $entityId !== null ? [':e' => $entityId] : []
            );
            if ($rows) $out[] = ['label' => $t['label'], 'class' => $t['class'], 'items' => $rows];
        }
        return $out;
    }

    // ¿El activo (clase GLPI + id) existe y, si se pasa entityId, pertenece a esa entidad?
    // Usado para impedir que una sucursal vincule activos de otra (aislamiento).
    public static function existsInEntity(string $class, int $id, ?int $entityId): bool
    {
        $t = self::typeByClass($class);
        if (!$t || $id <= 0) return false;
        $sql = "SELECT 1 FROM {$t['table']} WHERE id = :id AND is_deleted = 0";
        $p = [':id' => $id];
        if ($entityId !== null) { $sql .= " AND entities_id = :e"; $p[':e'] = $entityId; }
        return (bool) q_val($sql . " LIMIT 1", $p);
    }

    // Renombra un activo (corrección de datos). typeKey validado contra TYPES (no SQL injection).
    public static function rename(string $typeKey, int $id, string $name): bool
    {
        $t = self::typeByKey($typeKey);
        $name = trim($name);
        if (!$t || $id <= 0 || $name === '') return false;
        return db()->prepare("UPDATE {$t['table']} SET name = :n, date_mod = NOW() WHERE id = :id")
            ->execute([':n' => mb_substr($name, 0, 255), ':id' => $id]);
    }

    // Dar de baja un activo (states_id = 3 = Baja). Solo Dalia/admin. Queda en histórico.
    public static function baja(string $typeKey, int $id): bool
    {
        $t = self::typeByKey($typeKey);
        if (!$t) return false;
        $st = db()->prepare("UPDATE {$t['table']} SET states_id = 3, date_mod = NOW() WHERE id = :id");
        return $st->execute([':id' => $id]);
    }

    // Búsqueda transversal por todas las categorías (para selector en nuevo ticket).
    public static function search(?int $entityId, string $q, int $perType = 8): array
    {
        $out = [];
        foreach (self::TYPES as $t) {
            $sql = "SELECT id, name, serial, entities_id FROM {$t['table']}
                    WHERE is_deleted = 0 AND (name LIKE :q OR serial LIKE :q)";
            $p = [':q' => '%' . $q . '%'];
            if ($entityId !== null) { $sql .= " AND entities_id = :e"; $p[':e'] = $entityId; }
            $sql .= " ORDER BY serial ASC LIMIT " . (int)$perType;
            foreach (q_all($sql, $p) as $row) {
                $out[] = [
                    'itemtype'   => $t['class'],
                    'type_key'   => $t['key'],
                    'type_label' => $t['label'],
                    'id'         => (int)$row['id'],
                    'name'       => $row['name'],
                    'serial'     => $row['serial'],
                ];
            }
        }
        return $out;
    }

    public static function get(string $class, int $id): ?array
    {
        $t = self::typeByClass($class);
        if (!$t) return null;
        $r = q_one(
            "SELECT id, name, serial, otherserial, states_id, comment, entities_id
             FROM {$t['table']} WHERE id = :id AND is_deleted = 0",
            [':id' => $id]
        );
        if (!$r) return null;
        $r['type_label'] = $t['label'];
        $r['itemtype']   = $t['class'];
        $r['state_name'] = self::STATES[(int)$r['states_id']] ?? '—';
        $r['drive_url']  = self::driveUrl($r['comment'] ?? '');
        return $r;
    }

    // Extrae primer URL (link de Drive) del campo comment.
    public static function driveUrl(?string $comment): ?string
    {
        if (!$comment) return null;
        if (preg_match('~https?://\S+~', $comment, $m)) return rtrim($m[0], '.,);');
        return null;
    }
}
