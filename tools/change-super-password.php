<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this tool from the command line only.\n");
}

$configFile = dirname(__DIR__) . '/config/super_admin.php';
if (!is_file($configFile)) {
    exit("ERROR: config/super_admin.php was not found.\n");
}

function prompt_line(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    fflush(STDOUT);

    $line = fgets(STDIN);
    if ($line === false) {
        exit("\nERROR: Unable to read input.\n");
    }

    return trim($line);
}

fwrite(STDOUT, "Smart Parish - Change Super Admin Password\n");
fwrite(STDOUT, "------------------------------------------\n");
fwrite(STDOUT, "Password requirements: 10+ characters, uppercase, lowercase, number, special character.\n\n");

$password = prompt_line('Enter new super admin password: ');
if ($password === '') {
    exit("ERROR: Password cannot be empty.\n");
}

$confirm = prompt_line('Confirm new super admin password: ');

if (!hash_equals($password, $confirm)) {
    exit("ERROR: Passwords do not match. Nothing was changed.\n");
}

if (
    strlen($password) < 10 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/\d/', $password) ||
    !preg_match('/[^A-Za-z0-9]/', $password)
) {
    exit("ERROR: Use at least 10 characters with uppercase, lowercase, number, and special character.\n");
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($hash === false) {
    exit("ERROR: Could not generate the password hash.\n");
}

$content = file_get_contents($configFile);
if ($content === false) {
    exit("ERROR: Unable to read config/super_admin.php.\n");
}

// Use a callback so bcrypt/argon hashes containing "$" are inserted literally.
// preg_replace() replacement strings interpret $1, $2, etc. as capture groups,
// which can corrupt hashes such as "$2y$..." into "y$...".
$updated = preg_replace_callback(
    "/'password_hash'\s*=>\s*'[^']*'/",
    static function () use ($hash): string {
        return "'password_hash' => '" . str_replace("'", "\\'", $hash) . "'";
    },
    $content,
    1,
    $count
);

if ($updated === null || $count !== 1) {
    exit("ERROR: Could not locate password_hash in config/super_admin.php. Nothing was changed.\n");
}

if (file_put_contents($configFile, $updated, LOCK_EX) === false) {
    exit("ERROR: Unable to write config/super_admin.php. Check file permissions.\n");
}

fwrite(STDOUT, "\nSUCCESS: Super admin password changed successfully.\n");
fwrite(STDOUT, "You can now use the new password on the Administrator authorization screen.\n");
