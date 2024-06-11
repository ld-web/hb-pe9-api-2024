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


if (!isset($_GET['id'])) {
    http_response_code(400); // Bad request
    echo json_encode([
        'error' => "L'id est obligatoire"
    ]);
    exit;
}

$id = intval($_GET['id']);

if ($id === 0) {
    http_response_code(400); // Bad request
    echo json_encode([
        'error' => "L'id transmis est incorrect"
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id_course = :id");
$stmt->execute(['id' => $id]);

$course = $stmt->fetch();

if ($course === false) {
    http_response_code(404); // Not Found
    echo json_encode([
        'error' => "Cours non trouvé"
    ]);
    exit;
}

echo json_encode($course);