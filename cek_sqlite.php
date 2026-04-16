<?php
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $stmt = $pdo->query('SELECT COUNT(*) FROM products');
    echo 'Barang di SQLite: ' . $stmt->fetchColumn() . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
