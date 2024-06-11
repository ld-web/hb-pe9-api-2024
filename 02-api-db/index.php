<?php

header('Content-type: application/json; charset=UTF-8');

set_exception_handler(function (Throwable $exception) {
    // Utiliser $exception pour créer un log d'erreur détaillé sur le serveur
    // Si $exception est quelque chose de grave, alors
    // envoyer un email à l'administrateur

    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Une erreur est survenue, réessayez plus tard'
    ]);
    exit;
});

require_once 'vendor/autoload.php';

use App\Db;

$pdo = Db::getConnection();

$stmt = $pdo->query("SELECT * FROM courses");
$courses = $stmt->fetchAll();

echo json_encode($courses);