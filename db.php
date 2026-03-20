<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'ccs_portal');
define('DB_USER', 'root');
define('DB_PASS', '');          

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
    } catch (PDOException $e) {
        die('<div style="font-family:sans-serif;padding:30px;color:#c0392b;">
            <h2>Database Connection Failed</h2>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <p>Make sure MySQL is running in XAMPP and the database <strong>ccs_portal</strong> exists.</p>
        </div>');
    }

    return $pdo;
}