<?php

use App\Db;
use App\Exception\RequiredFieldsException;

/**
 * Finds all courses in DB
 *
 * @return array Courses, associative array
 */
function findAllCourses(): array
{
    $pdo = Db::getConnection();
    $stmt = $pdo->query("SELECT * FROM courses");
    return $stmt->fetchAll();
}

function findCourse(int $id): ?array
{
    $pdo = Db::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id_course = :id");
    $stmt->execute(['id' => $id]);

    $course = $stmt->fetch();

    if ($course === false) {
        return null;
    }

    return [
        'uri' => '/courses/' . $id,
        ...$course
    ];
}

function addCourse(array $data): array
{
    $errors = checkRequiredFields($data);

    if (!empty($errors)) {
        throw new RequiredFieldsException($errors);
    }

    $pdo = Db::getConnection();

    $stmt = $pdo->prepare("INSERT INTO courses (course_name, cover_img_url, video_url, date_online) VALUES (:name, :img, :video, :date)");

    $stmt->execute([
        'name' => $data['name'],
        'img' => $data['img'],
        'video' => $data['video'],
        'date' => $data['date'],
    ]);

    $id = $pdo->lastInsertId();

    return [
        'uri' => '/courses/' . $id,
        'id_course' => $id,
        'course_name' => $data['name'],
        'cover_img_url' => $data['img'],
        'video_url' => $data['video'],
        'date_online' => $data['date'],
    ];
}

function updateCourse(int $id, array $data): void
{
    $errors = checkRequiredFields($data);

    if (!empty($errors)) {
        throw new RequiredFieldsException($errors);
    }

    $pdo = Db::getConnection();

    $stmt = $pdo->prepare("UPDATE courses SET course_name=:name, cover_img_url=:img, video_url=:video, date_online=:date WHERE id_course=:id");

    $stmt->execute([
        'name' => $data['name'],
        'img' => $data['img'],
        'video' => $data['video'],
        'date' => $data['date'],
        'id' => $id
    ]);
}

function checkRequiredFields(array $data): array
{
    $requiredFields = ['name', 'img', 'video', 'date'];

    $errors = [];
    foreach ($requiredFields as $requiredField) {
        if  (!isset($data[$requiredField])) {
            $errors[$requiredField] = "Le champ '$requiredField' est requis";
        }
    }

    return $errors;
}
