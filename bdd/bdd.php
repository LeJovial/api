<?php
try {
    $dbh = new PDO('mysql:host=localhost;dbname=api_test', 'root', '');

    $dbh->exec('
        CREATE TABLE IF NOT EXISTS memory (
            id INTEGER PRIMARY KEY AUTO_INCREMENT,
            page VARCHAR(255) NOT NULL,
            page_id VARCHAR(255),
            title VARCHAR(255)
        );
    ');
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage();
    die();
}