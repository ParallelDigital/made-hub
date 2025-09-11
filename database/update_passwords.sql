-- Update test user password to WordPress format
UPDATE users 
SET password = '$P$B5XKj6XhZ6z6Z6Z6Z6Z6Z6Z6Z6Z6Z6Z1' 
WHERE email = 'test@example.com';

-- Update admin user password to WordPress format
UPDATE users 
SET password = '$P$B5XKj6XhZ6z6Z6Z6Z6Z6Z6Z6Z6Z6Z6Z1' 
WHERE email = 'admin@example.com';
