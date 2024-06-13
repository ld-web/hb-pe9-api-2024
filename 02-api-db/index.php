<?php

use App\Request\RequestUri;

require_once __DIR__ . '/functions/course.php';

header('Content-type: application/json; charset=UTF-8');

set_exception_handler(function (Throwable $exception) {
    // Utiliser $exception pour créer un log d'erreur détaillé sur le serveur
    // Si $exception est quelque chose de grave, alors
    // envoyer un email à l'administrateur
    var_dump($exception);

    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Une erreur est survenue, réessayez plus tard'
    ]);
    exit;
});

require_once 'vendor/autoload.php';

use App\Db;

$pdo = Db::getConnection();

$uri = new RequestUri($_SERVER['REQUEST_URI']);
$httpMethod = $_SERVER['REQUEST_METHOD'];

// --- Liste des cours -------------------------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_COLLECTION && $uri->getResourceName() === 'courses' && $httpMethod === 'GET') {
    $courses = findAllCourses();

    echo json_encode($courses);
    exit;
}

// --- Création d'un cours ---------------------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_COLLECTION && $uri->getResourceName() === 'courses' && $httpMethod === 'POST') {
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
        'uri' => '/courses/' . $id,
        'id_course' => $id,
        'course_name' => $arrayContent['name'],
        'cover_img_url' => $arrayContent['img'],
        'video_url' => $arrayContent['video'],
        'date_online' => $arrayContent['date'],
    ]);
    exit;
}

// --- Récupération d'un cours seul ------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_ITEM && $uri->getResourceName() === 'courses' && $httpMethod === 'GET') {
    $id = $uri->getIdentifier();

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

    $course = [
        'uri' => '/courses/' . $id,
        ...$course
    ];

    echo json_encode($course);
    exit;
}

// --- Modification d'un cours seul ------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_ITEM && $uri->getResourceName() === 'courses' && $httpMethod === 'PUT') {
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

    $stmt = $pdo->prepare("UPDATE courses SET course_name=:name, cover_img_url=:img, video_url=:video, date_online=:date WHERE id_course=:id");

    $result = $stmt->execute([
        'name' => $arrayContent['name'],
        'img' => $arrayContent['img'],
        'video' => $arrayContent['video'],
        'date' => $arrayContent['date'],
        'id' => $uri->getIdentifier()
    ]);

    if ($result === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'error' => "Une erreur est survenue lors de l'enregistrement du nouvel élément"
        ]);
        exit;
    }

    http_response_code(204); // No Content
    exit;
}

// --- Suppression d'un cours ------------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_ITEM && $uri->getResourceName() === 'courses' && $httpMethod === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM courses WHERE id_course=:id');

    $result = $stmt->execute(['id' => $uri->getIdentifier()]);

    if ($result === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'error' => "Une erreur est survenue lors de l'enregistrement du nouvel élément"
        ]);
        exit;
    }

    http_response_code(204); // No Content
    exit;
}
