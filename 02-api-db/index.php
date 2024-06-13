<?php

use App\Exception\RequiredFieldsException;
use App\Request\RequestUri;

require_once __DIR__ . '/functions/course.php';

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

$uri = new RequestUri($_SERVER['REQUEST_URI']);
$httpMethod = $_SERVER['REQUEST_METHOD'];

// --- Liste des cours -------------------------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_COLLECTION &&
    $uri->getResourceName() === 'courses' &&
    $httpMethod === 'GET'
) {
    $courses = findAllCourses();

    echo json_encode($courses);
    exit;
}

// --- Création d'un cours ---------------------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_COLLECTION &&
    $uri->getResourceName() === 'courses' &&
    $httpMethod === 'POST'
) {
    $requestRawContent = file_get_contents('php://input');
    $arrayContent = json_decode($requestRawContent, true);

    try {
        $newCourse = addCourse($arrayContent);
        http_response_code(201); // Created
        echo json_encode($newCourse);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => "Erreur lors de l'enregistrement en base de données"
        ]);
    } catch (RequiredFieldsException $e) {
        http_response_code(422);
        echo json_encode($e->getErrors());
    } finally {
        exit;
    }
}

// --- Récupération d'un cours seul ------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_ITEM &&
    $uri->getResourceName() === 'courses' &&
    $httpMethod === 'GET'
) {
    $course = findCourse($uri->getIdentifier());

    if ($course === null) {
        http_response_code(404); // Not Found
        echo json_encode([
            'error' => "Cours non trouvé"
        ]);
        exit;
    }

    echo json_encode(value: $course);
    exit;
}

// --- Modification d'un cours seul ------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_ITEM &&
    $uri->getResourceName() === 'courses' &&
    $httpMethod === 'PUT'
) {
    $requestRawContent = file_get_contents('php://input');
    $arrayContent = json_decode($requestRawContent, true);

    try {
        updateCourse($uri->getIdentifier(), $arrayContent);
        http_response_code(204); // No Content
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => "Erreur lors de l'enregistrement en base de données"
        ]);
    } catch (RequiredFieldsException $e) {
        http_response_code(422);
        echo json_encode($e->getErrors());
    } finally {
        exit;
    }
}

// --- Suppression d'un cours ------------------------------------------
if ($uri->getOperationType() === RequestUri::OPERATION_ITEM &&
    $uri->getResourceName() === 'courses' &&
    $httpMethod === 'DELETE'
) {
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
