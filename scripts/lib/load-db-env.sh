#!/usr/bin/env bash
# Carga credenciales MySQL desde Laravel cuando el shell de Railway no exporta DB_*.

boutique_load_db_env() {
  if [[ -n "${DB_HOST:-}" && -n "${DB_USERNAME:-}" && -n "${DB_DATABASE:-}" ]]; then
    return 0
  fi

  if [[ -n "${MYSQLHOST:-}" && -n "${MYSQLUSER:-}" && -n "${MYSQLDATABASE:-}" ]]; then
    DB_HOST="${DB_HOST:-$MYSQLHOST}"
    DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
    DB_USERNAME="${DB_USERNAME:-$MYSQLUSER}"
    DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
    DB_DATABASE="${DB_DATABASE:-$MYSQLDATABASE}"
    export DB_HOST DB_PORT DB_USERNAME DB_PASSWORD DB_DATABASE
    return 0
  fi

  if [[ ! -f vendor/autoload.php || ! -f bootstrap/app.php ]]; then
    return 1
  fi

  eval "$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection = config("database.default");
$config = config("database.connections.$connection") ?? [];
$url = $config["url"] ?? env("DATABASE_URL") ?? env("DB_URL");
if ($url) {
    $parts = parse_url($url);
    $host = $parts["host"] ?? "";
    $port = $parts["port"] ?? 3306;
    $user = isset($parts["user"]) ? urldecode($parts["user"]) : "";
    $pass = isset($parts["pass"]) ? urldecode($parts["pass"]) : "";
    $db = isset($parts["path"]) ? ltrim($parts["path"], "/") : "";
} else {
    $host = $config["host"] ?? "";
    $port = $config["port"] ?? 3306;
    $user = $config["username"] ?? "";
    $pass = $config["password"] ?? "";
    $db = $config["database"] ?? "";
}
$prefix = (string) config("vecsa.db_table_prefix", env("DB_TABLE_PREFIX", ""));
$emit = static function (string $key, $value): void {
    echo "export ", $key, "=", escapeshellarg((string) $value), PHP_EOL;
};
$emit("DB_HOST", $host);
$emit("DB_PORT", $port);
$emit("DB_USERNAME", $user);
$emit("DB_PASSWORD", $pass);
$emit("DB_DATABASE", $db);
$emit("DB_TABLE_PREFIX", $prefix);
')"

  return 0
}
