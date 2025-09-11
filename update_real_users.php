<?php
// Update database with real user credentials

// Connect to the SQLite database
$db = new SQLite3('database/database.sqlite');

// Clear existing users
$db->exec("DELETE FROM users");

// Hash the passwords using bcrypt
$password1 = password_hash('password', PASSWORD_BCRYPT);
$password2 = password_hash('Winner0nly!', PASSWORD_BCRYPT);

echo "Setting up real user accounts...\n";

// Insert Herman's account
$db->exec("INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at, role) VALUES (
    'Herman', 
    'hermen@made.com', 
    '" . SQLite3::escapeString($password1) . "', 
    '2025-09-10 18:00:00', 
    '2025-09-10 18:00:00', 
    '2025-09-10 18:00:00', 
    'admin'
)");
echo "Created hermen@made.com with password: password\n";

// Insert Parallel Digital account
$db->exec("INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at, role) VALUES (
    'Parallel Digital', 
    'info@parallel-digital.com', 
    '" . SQLite3::escapeString($password2) . "', 
    '2025-09-10 18:00:00', 
    '2025-09-10 18:00:00', 
    '2025-09-10 18:00:00', 
    'admin'
)");
echo "Created info@parallel-digital.com with password: Winner0nly!\n";

// Verify the accounts
$result = $db->query("SELECT email, name FROM users");
echo "\nUser accounts created:\n";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "- {$row['name']} ({$row['email']})\n";
}

echo "\nSetup complete!\n";
