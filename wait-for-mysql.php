<?php
// wait-for-mysql.php

$maxAttempts = 30;
$attempt = 0;

echo "Waiting for MySQL connection...\n";
echo "Host: " . getenv('DB_HOST') . "\n";
echo "Port: " . getenv('DB_PORT') . "\n";
echo "Database: " . getenv('DB_DATABASE') . "\n";
echo "Username: " . getenv('DB_USERNAME') . "\n";

while ($attempt < $maxAttempts) {
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s',
            getenv('DB_HOST'),
            getenv('DB_PORT') ?: 3306,
            getenv('DB_DATABASE')
        );
        
        new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        
        echo "✅ MySQL is ready!\n";
        exit(0);
    } catch (PDOException $e) {
        $attempt++;
        echo "❌ Attempt $attempt/$maxAttempts failed: " . $e->getMessage() . "\n";
        sleep(3);
    }
}

echo "💥 Could not connect to MySQL after $maxAttempts attempts\n";
exit(1);