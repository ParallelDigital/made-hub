<?php
// This script will set up test users with bcrypt hashed passwords

// Connect to the SQLite database
$db = new SQLite3('database/database.sqlite');

// Password to use for all test accounts
$password = 'password';

// Hash the password using bcrypt
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

echo "Setting up test users with password: $password\n";

// Update test user
$db->exec("UPDATE users SET password = '" . SQLite3::escapeString($hashed_password) . "' WHERE email = 'test@example.com'");
echo "Updated test@example.com\n";

// Update admin user
$db->exec("UPDATE users SET password = '" . SQLite3::escapeString($hashed_password) . "' WHERE email = 'admin@example.com'");
echo "Updated admin@example.com\n";

// Verify the hashes
$result = $db->query("SELECT email, password FROM users WHERE email IN ('test@example.com', 'admin@example.com')");
echo "\nVerifying password hashes:\n";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $is_valid = password_verify($password, $row['password']);
    echo "{$row['email']}: " . ($is_valid ? 'Password is valid' : 'INVALID PASSWORD') . "\n";
}

echo "\nSetup complete. You can now log in with:\n";
echo "Test User: test@example.com / password\n";
echo "Admin User: admin@example.com / password\n";
