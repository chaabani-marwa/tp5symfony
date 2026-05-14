<?php

require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

try {
    // Configuration de la base de données (à adapter selon votre configuration)
    $connectionParams = [
        'dbname' => 'symbook2_2025',
        'user' => 'root',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    ];

    $conn = DriverManager::getConnection($connectionParams);

    // Vérifier si la table commande existe
    $schemaManager = $conn->createSchemaManager();
    $tables = $schemaManager->listTableNames();

    if (!in_array('commande', $tables)) {
        echo "Création de la table commande...\n";

        // Créer la table commande
        $sql = "
            CREATE TABLE commande (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                status VARCHAR(255) NOT NULL,
                prix_total DOUBLE PRECISION NOT NULL,
                date_commande DATETIME NOT NULL,
                livres JSON NOT NULL COMMENT '(DC2Type:json)',
                INDEX IDX_6EEAA67DA76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ";

        $conn->executeStatement($sql);

        // Ajouter la clé étrangère
        $sql = "ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)";
        $conn->executeStatement($sql);

        echo "Table commande créée avec succès!\n";
    } else {
        echo "La table commande existe déjà.\n";
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}