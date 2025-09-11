<?php
// This is a direct implementation of WordPress password hashing
function wp_hash_password($password) {
    global $wp_hasher;

    if (empty($wp_hasher)) {
        require_once(dirname(__FILE__) . '/vendor/roots/wp-password-bcrypt/wp-password-bcrypt.php');
        $wp_hasher = new Roots\WP_Password_BCrypt();
    }

    return $wp_hasher->hash_password($password);
}

// The password we want to hash
$password = 'password';

// Generate a WordPress-compatible password hash
$hashed_password = wp_hash_password($password);

echo "Hashed password for '$password': " . $hashed_password . "\n";

// Now update the database with this hash
$db = new SQLite3('database/database.sqlite');

// Update test user
$db->exec("UPDATE users SET password = '" . SQLite3::escapeString($hashed_password) . "' WHERE email = 'test@example.com'");

// Update admin user
$db->exec("UPDATE users SET password = '" . SQLite3::escapeString($hashed_password) . "' WHERE email = 'admin@example.com'");

echo "Passwords updated in the database.\n";
