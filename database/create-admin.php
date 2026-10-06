<?php
require __DIR__.'/../config/db.php';
$email='admin@smartparish.local'; $password='Admin123!';
$hash=password_hash($password,PASSWORD_DEFAULT);
$s=$pdo->prepare("INSERT INTO users(full_name,email,contact_number,address,birthday,gender,password,role,status) VALUES('Parish Administrator',?,'N/A','Parish Office','2000-01-01','Other',?,'admin','active') ON DUPLICATE KEY UPDATE password=VALUES(password), role='admin'");
$s->execute([$email,$hash]);
echo "Admin ready: {$email} / {$password}\nDelete this file after use.\n";
