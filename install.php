<?php
/**
 * PCALS Installer (web + CLI) — "improve install" step.
 *
 *   Web: open /install.php in a browser.
 *   CLI: php install.php [db_host] [db_name] [db_user] [db_pass] [admin_user] [admin_pass]
 *
 * Flow:
 *   1. Prereq checks (PHP extensions, writable .env / files/).
 *   2. Connect to MySQL, create the database if missing.
 *   3. Import sql/database.sql.
 *   4. Run idempotent migrations (add any missing columns, e.g. loans.purpose).
 *   5. Seed the first admin account (bcrypt-hashed password).
 *   6. Write .env LAST. The installer self-locks once .env exists.
 */
declare(strict_types=1);

$ROOT       = dirname(__FILE__);
$ENV_FILE   = $ROOT . '/.env';
$SQL_FILE   = $ROOT . '/sql/database.sql';

/* ------------------------------------------------------------------ */
/* Helpers                                                            */
/* ------------------------------------------------------------------ */
function isCli(): bool { return php_sapi_name() === 'cli'; }

function out(string $s): void {
    if (isCli()) { fwrite(STDOUT, $s . "\n"); } else { echo $s; }
}

function err(string $s): void {
    if (isCli()) { fwrite(STDERR, $s . "\n"); } else { echo '<div class="error">' . htmlspecialchars($s) . '</div>'; }
}

function writeEnv(string $host, string $name, string $user, string $pass): bool {
    $content = "APP_ENV=local\n";
    $content .= "DB_HOST={$host}\n";
    $content .= "DB_NAME={$name}\n";
    $content .= "DB_USER={$user}\n";
    $content .= "DB_PASS={$pass}\n";
    return file_put_contents($GLOBALS['ENV_FILE'], $content) !== false;
}

function envGet(string $key, string $default = ''): string {
    // env may already be in process env (CLI args / server) or in .env
    $v = getenv($key);
    if ($v === false) {
        $v = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }
    if ($v === false || $v === null) {
        $v = $default;
    }
    $v = is_array($v) ? '' : (string)$v;
    return $v;
}

/* ------------------------------------------------------------------ */
/* Already installed? -> lock (unless --force via CLI)                */
/* ------------------------------------------------------------------ */
if (is_file($GLOBALS['ENV_FILE'])) {
    $force = isCli() && in_array('--force', $GLOBALS['argv'] ?? [], true);
    if (!$force) {
        if (isCli()) { fwrite(STDERR, "PCALS is already configured. Delete .env to reinstall (or use --force).\n"); exit(1); }
        http_response_code(404);
        exit('Installer is locked.');
    }
}

/* ------------------------------------------------------------------ */
/* Prereq checks                                                      */
/* ------------------------------------------------------------------ */
$errors = [];
if (version_compare(PHP_VERSION, '7.4', '<')) { $errors[] = 'PHP 7.4+ required (found ' . PHP_VERSION . ').'; }
if (!extension_loaded('pdo'))           { $errors[] = 'pdo extension required.'; }
if (!extension_loaded('pdo_mysql'))     { $errors[] = 'pdo_mysql extension required.'; }
if (!extension_loaded('openssl'))       { $errors[] = 'openssl extension required (for password hashing).'; }
if (!is_writable($ROOT))                 { $errors[] = 'Project root must be writable (to create .env).'; }
if (!is_writable($ROOT . '/sql'))        { $errors[] = 'sql/ directory must be readable.'; }

/* ------------------------------------------------------------------ */
/* Collect credentials (CLI argv, then POST, then defaults)           */
/* ------------------------------------------------------------------ */
$defaults = [
    'DB_HOST'       => 'localhost',
    'DB_NAME'       => 'ca_loan_system',
    'DB_USER'       => 'root',
    'DB_PASS'       => '',
    'ADMIN_USER'    => 'admin',
    'ADMIN_PASS'    => 'changeme123',
];

if (isCli()) {
    $a = array_values(array_slice(array_merge($GLOBALS['argv'], array_fill(0, 6, '')), 0, 6));
    $host = $a[0] !== '' ? $a[0] : envGet('DB_HOST', $defaults['DB_HOST']);
    $name = $a[1] !== '' ? $a[1] : envGet('DB_NAME', $defaults['DB_NAME']);
    $user = $a[2] !== '' ? $a[2] : envGet('DB_USER', $defaults['DB_USER']);
    $pass = $a[3] !== '' ? $a[3] : envGet('DB_PASS', $defaults['DB_PASS']);
    $auser = $a[4] !== '' ? $a[4] : $defaults['ADMIN_USER'];
    $apass = $a[5] !== '' ? $a[5] : $defaults['ADMIN_PASS'];
} else {
    $host   = $_POST['db_host']   ?? $defaults['DB_HOST'];
    $name   = $_POST['db_name']   ?? $defaults['DB_NAME'];
    $user   = $_POST['db_user']   ?? $defaults['DB_USER'];
    $pass   = $_POST['db_pass']   ?? $defaults['DB_PASS'];
    $auser  = $_POST['admin_user'] ?? $defaults['ADMIN_USER'];
    $apass  = $_POST['admin_pass'] ?? $defaults['ADMIN_PASS'];
}

/* ------------------------------------------------------------------ */
/* Handle web GET -> render form                                      */
/* ------------------------------------------------------------------ */
if (!isCli() && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(installer_form());
    exit;
}

/* ------------------------------------------------------------------ */
/* Run installation                                                   */
/* ------------------------------------------------------------------ */
if (!empty($errors)) {
    foreach ($errors as $e) { err('✗ ' . $e); }
    if (isCli()) { exit(1); }
    out('<p><a href="install.php">Back</a></p>');
    exit;
}

$ok = true;
try {
    // 1. Connect to server (no db) and create database.
    $dsnServer = "mysql:host={$host};charset=utf8mb4";
    $pdoServer = new PDO($dsnServer, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    out("Database `{$name}` ready.");

    // 2. Connect to the database and import schema.
    $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $sql = file_get_contents($SQL_FILE);
    if ($sql === false) { throw new RuntimeException('Could not read sql/database.sql'); }
    // Strip "CREATE DATABASE / USE" lines; we create the DB above.
    $sql = preg_replace('/^\s*CREATE\s+DATABASE.*$/im', '', $sql);
    $sql = preg_replace('/^\s*USE\s+.*;$/im', '', $sql);
    $stmts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($stmts as $stmt) {
        if ($stmt !== '') { $pdo->exec($stmt); }
    }
    out("Schema imported.");

    // 3. Idempotent migrations: ensure loans.purpose exists.
    $hasPurpose = (bool) $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'loans' AND COLUMN_NAME = 'purpose'")->fetchColumn();
    if (!$hasPurpose) {
        $pdo->exec("ALTER TABLE loans ADD COLUMN purpose VARCHAR(255) NOT NULL DEFAULT ''");
        out("Migrated: added loans.purpose.");
    } else {
        out("loans.purpose already present.");
    }

    // 4. Seed first admin (avoid duplicating an existing username).
    $existing = $pdo->query("SELECT COUNT(*) FROM admins WHERE username = '" . $pdo->quote($auser) . "'")->fetchColumn();
    if ((int)$existing > 0) {
        out("Admin `{$auser}` already exists; skipping seed.");
    } else {
        $hash = password_hash($apass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->execute([$auser, $hash]);
        out("Admin `{$auser}` seeded (bcrypt-hashed).");
    }

    // 5. Write .env LAST (locks the installer).
    if (!writeEnv($host, $name, $user, $pass)) {
        err('✗ Could not write .env — check permissions.');
        $ok = false;
    }

} catch (Throwable $e) {
    err('✗ ' . $e->getMessage());
    $ok = false;
}

if ($ok) {
    out('Install complete. .env written.');
    if (isCli()) {
        fwrite(STDOUT, "Login at: http://localhost/public/index.php\n");
        fwrite(STDOUT, "Admin user: {$auser}\n");
        fwrite(STDOUT, "Admin pass: {$apass}\n");
        exit(0);
    }
    // Web: redirect to login
    header('Location: public/index.php');
    exit;
} else {
    err('Installation finished with errors. .env was NOT written; you may re-run.');
    if (isCli()) { exit(1); }
    out('<p><a href="install.php">Retry</a></p>');
    exit;
}

/* ------------------------------------------------------------------ */
/* Web installer form                                                 */
/* ------------------------------------------------------------------ */
function installer_form(): string {
    $d = $GLOBALS['defaults'];
    return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PCALS Installer</title>
<style>
body{font-family:system-ui,Arial,sans-serif;background:#f3f4f6;color:#1f2937}
.card{max-width:520px;margin:3rem auto;background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.1);padding:2rem}
h1{color:#0d9488;font-size:1.6rem;margin-top:0}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.grid > div{grid-column:1/-1}
label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.25rem;color:#374151}
input{width:100%;padding:.6rem;border:1px solid #d1d5db;border-radius:8px;font:inherit}
btn{display:inline-block;background:#0d9488;color:#fff;padding:.6rem 1.2rem;border:none;border-radius:8px;font-weight:600;cursor:pointer}
btn:hover{background:#0b7f75}
note{font-size:.8rem;color:#6b7280;margin-top:1rem;display:block}
</style>
</head>
<body>
<div class="card">
<h1>PCALS Installer</h1>
<form method="POST" action="install.php">
  <div class="grid">
    <div>
      <label>Database Host</label>
      <input name="db_host" value="localhost" required>
    </div>
    <div>
      <label>Database Name</label>
      <input name="db_name" value="ca_loan_system" required>
    </div>
    <div>
      <label>Database User</label>
      <input name="db_user" required>
    </div>
    <div>
      <label>Database Password</label>
      <input name="db_pass" type="password">
    </div>
    <div>
      <label>Admin Username</label>
      <input name="admin_user" value="admin" required>
    </div>
    <div>
      <label>Admin Password</label>
      <input name="admin_pass" type="password" required>
    </div>
  </div>
  <p style="margin-top:1.2rem"><button type="submit" class="btn">Install</button></p>
  <note>Installer locks itself after success (deletes/renames approach: just remove .env to reinstall).</note>
</form>
</div>
</body>
</html>
HTML;
}
