<?php

use App\Db;

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

function addCourse(): array
{
    return []; // TODO: Implémenter
}
