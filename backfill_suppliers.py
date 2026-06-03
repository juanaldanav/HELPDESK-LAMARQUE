# Backfill de proveedores: GLPI_Tickets_Import.csv -> glpi_suppliers + glpi_suppliers_tickets
# Internos (Interno/Internos/Interno - X) NO se vinculan: cuentan como 'Interno' via COALESCE.
# Uso: python soporte-v2/backfill_suppliers.py > /tmp/suppliers.sql   (desde D:\LAMARQUE\GLPI)
import csv, sys

def esc(s): return s.replace("\\", "\\\\").replace("'", "''")

def norm(s):
    s = (s or '').replace('�', 'é').strip()
    if not s: return None
    if s.lower().startswith('intern'): return None  # interno -> sin proveedor
    if s.lower() in ('cafe marino', 'café marino'): return 'Café Marino'
    return s

with open('GLPI_Tickets_Import.csv', encoding='utf-8-sig', newline='') as f:
    rows = list(csv.DictReader(f, delimiter=';'))

pairs = []   # (title, supplier)
sups = set()
for r in rows:
    sup = norm(r.get('suppliers_id'))
    title = (r.get('title') or '').strip()
    if sup and title:
        pairs.append((title, sup)); sups.add(sup)

print(f"-- externos: {len(pairs)} links, {len(sups)} proveedores", file=sys.stderr)
print("-- backfill proveedores desde CSV historico")
for s in sorted(sups):
    e = esc(s)
    print(f"INSERT INTO glpi_suppliers (entities_id, is_recursive, name, date_creation, date_mod)"
          f" SELECT 0, 1, '{e}', NOW(), NOW() FROM DUAL"
          f" WHERE NOT EXISTS (SELECT 1 FROM glpi_suppliers WHERE name = '{e}');")
for title, sup in pairs:
    t, s = esc(title), esc(sup)
    print(f"INSERT INTO glpi_suppliers_tickets (tickets_id, suppliers_id, type, use_notification)"
          f" SELECT t.id, s.id, 2, 0 FROM glpi_tickets t JOIN glpi_suppliers s ON s.name = '{s}'"
          f" WHERE t.name = '{t}' AND NOT EXISTS"
          f" (SELECT 1 FROM glpi_suppliers_tickets x WHERE x.tickets_id = t.id AND x.suppliers_id = s.id);")
