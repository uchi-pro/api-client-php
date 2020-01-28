<?php

/**
 * Пример построения дерева курсов.
 */

use UchiPro\{ApiClient, Courses\Course, Identity};

require __DIR__.'/../vendor/autoload.php';

$courses = fetchCourses();

print buildCoursesTree($courses);

/**
 * @param Course[] $courses
 *
 * @param int $parentId
 * @param int $level
 * @param int $i
 *
 * @return string
 */
function buildCoursesTree($courses, $parentId = null, $level = 0, $i = 0)
{
    $output = '';

    $j = 0;
    foreach ($courses as $course) {
        if ($course->parentId !== $parentId) {
            continue;
        }
        $j++;

        $prefix = str_repeat(' ', $level * 2).($i?"{$i}.":'')."{$j}. ";
        $suffix = '';
        if ($course->lessonsCount === 0 && !$course->parentId) {
            $suffix = ' [направление]';
        }

        $output .= $prefix.$course->title.$suffix.PHP_EOL;

        $output .= buildCoursesTree($courses, $course->id, $level + 1, $j);
    }

    return $output;
}

/**
 * @return ApiClient
 */
function getApiClient()
{
    $url = getenv('UCHIPRO_URL');
    $login = getenv('UCHIPRO_LOGIN');
    $password = getenv('UCHIPRO_PASSWORD');

    $identity = Identity::createByLogin($url, $login, $password);
    return ApiClient::create($identity);
}

/**
 * @return array|Course[]
 */
function fetchCourses()
{
    $apiClient = getApiClient();

    return $apiClient->courses()->findBy();
}
