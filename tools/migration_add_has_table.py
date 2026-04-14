#!/usr/bin/env python3
"""
Añade guardas Schema::hasTable a migraciones con un solo Schema::create(env(...). 'table', ...).
Archivos con varios Schema::create se excluyen y se editan a mano.
"""
from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MIG = ROOT / "vecsa-backend" / "database" / "migrations"

SKIP_MULTI = {
    "0001_01_01_000000_create_users_table.php",
    "0001_01_01_000001_create_cache_table.php",
    "0001_01_01_000002_create_jobs_table.php",
    "2024_06_26_132225_create_permission_tables.php",
}


def split_up_down(content: str) -> tuple[str, str]:
    m = re.search(r"\n(\s*public function down\s*\([^)]*\)\s*:\s*(?:void|\w+)\s*\{)", content)
    if not m:
        return content, ""
    return content[: m.start()], content[m.start() :]


def count_creates(up: str) -> int:
    return len(re.findall(r"Schema::create\s*\(", up))


def up_already_has_has_table(up: str) -> bool:
    return "Schema::hasTable" in up


def transform_one_prefixed_create(up: str, env_key: str) -> tuple[str, bool]:
    pat = re.compile(
        rf"^(\s*)Schema::create\(\s*env\(\s*['\"]{re.escape(env_key)}['\"]\s*,\s*['\"]['\"]\s*\)\s*\.\s*['\"]([^'\"]+)['\"]\s*,\s*(function\s*\(Blueprint\s+\$table\)\s*(?:use\s*\([^)]*\)\s*)?\{{)",
        re.MULTILINE,
    )
    m = pat.search(up)
    if not m:
        return up, False
    ind, tbl, fn = m.group(1), m.group(2), m.group(3)
    repl = (
        f"{ind}$tableName = env('{env_key}', '') . '{tbl}';\n\n"
        f"{ind}if (Schema::hasTable($tableName)) {{\n"
        f"{ind}    return;\n"
        f"{ind}}}\n\n"
        f"{ind}Schema::create($tableName, {fn}"
    )
    return pat.sub(repl, up, count=1), True


def process_file(path: Path) -> bool:
    if path.name in SKIP_MULTI:
        return False
    raw = path.read_text(encoding="utf-8")
    if "Schema::create" not in raw:
        return False
    up, down = split_up_down(raw)
    if count_creates(up) != 1:
        return False
    if up_already_has_has_table(up):
        return False

    new_up = up
    changed = False
    for key in ("DB_TABLE_PREFIX", "DB_TABLE_PREFIX_STREGA"):
        new_up, c = transform_one_prefixed_create(new_up, key)
        if c:
            changed = True
            break

    if not changed:
        return False

    path.write_text(new_up + down, encoding="utf-8")
    return True


def main() -> int:
    n = 0
    for p in sorted(MIG.glob("*.php")):
        if process_file(p):
            print(p.name)
            n += 1
    print(f"Modified {n} files (single env-prefixed Schema::create only).")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
