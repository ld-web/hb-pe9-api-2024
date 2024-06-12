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

$requestRawContent = file_get_contents('php://input');
$arrayContent = json_decode($requestRawContent, true);

$requiredFields = ['name', 'img', 'video', 'date'];
$errors = [];
foreach ($requiredFields as $requiredField) {
    if  (!isset($arrayContent[$requiredField])) {
        $errors[$requiredField] = "Le champ '$requiredField' est requis";
    }
}

if (count($errors) > 0) {
    http_response_code(422); // Unprocessable content
    echo json_encode($errors);
    exit;
}


$stmt = $pdo->prepare("INSERT INTO courses (course_name, cover_img_url, video_url, date_online) VALUES (:name, :img, :video, :date)");

$result = $stmt->execute([
    'name' => $arrayContent['name'],
    'img' => $arrayContent['img'],
    'video' => $arrayContent['video'],
    'date' => $arrayContent['date'],
]);

if ($result === false) {
    http_response_code(500); // Unprocessable content
    echo json_encode([
        'error' => "Une erreur est survenue lors de l'enregistrement du nouvel élément"
    ]);
    exit;
}

http_response_code(201); // Created

$id = $pdo->lastInsertId();

echo json_encode([
    'uri' => 'course.php?id=' . $id,
    'id_course' => $id,
    'course_name' => $arrayContent['name'],
    'cover_img_url' => $arrayContent['img'],
    'video_url' => $arrayContent['video'],
    'date_online' => $arrayContent['date'],
]);
