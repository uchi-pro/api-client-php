<?php

namespace UchiPro\Courses;

use DateTime;
use DateTimeImmutable;
use UchiPro\ApiClient;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Vendors\Vendor;

class Courses
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @param Query|null $query
     *
     * @return array|Course[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Query $query = null)
    {
        $courses = [];

        $uri = $this->buildUri($query);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('courses', $responseData)) {
            throw new BadResponseException('Не удалось получить список курсов.');
        }

        if (is_array($responseData['courses'])) {
            $courses = $this->parseCourses($responseData['courses']);
        }

        return $courses;
    }

    protected function buildUri(Query $query = null)
    {
        $uri = '/courses?_tree=1';

        if ($query) {
            if ($query->vendor && ($query->vendor instanceof Vendor)) {
                $uri = "/vendors/{$query->vendor->id}/courses?_tree=1";
            }

            if ($query->withLessons) {
                $uri .= '&with_lessons=1';
            }

            if ($query->parent && ($query->parent instanceof Course)) {
                $uri = "?parent={$query->parent->id}";
            }
        }

        return $uri;
    }

    protected function parseCourses(array $list)
    {
        $courses = [];

        foreach ($list as $item) {
            $courseType = new CourseType();
            $courseType->title = $item['type']['title'] ?? null;

            $lessons = [];
            if (isset($item['lessons']) && is_array($item['lessons'])) {
                foreach ($item['lessons'] as $lessonItem) {
                    $lessonType = new LessonType();
                    $lessonType->id = $lessonItem['type'] ?? null;
                    $lessonType->title = $lessonItem['type_title'] ?? null;

                    $lesson = new Lesson();
                    $lesson->id = $lessonItem['uuid'] ?? null;
                    $lesson->title = $lessonItem['title'] ?? null;
                    $lesson->type = $lessonType;

                    $lessons[] = $lesson;
                }
            }

            $course = new Course();
            $course->id = $item['uuid'] ?? null;
            $course->createdAt = DateTimeImmutable::createFromFormat(DateTime::RFC3339, $item['created_at']);
            $course->title = $item['title'] ?? null;
            $course->parentId = $item['parent_uuid'] ?? null;
            $course->type = $courseType;
            $course->hours = $item['hours'] ?? null;
            $course->price = $item['price'] ?? null;
            $course->depth = isset($item['depth']) ? (int)$item['depth'] : 0;
            $course->childrenCount = isset($item['children_count']) ? (int)$item['children_count'] : 0;
            $course->lessonsCount = isset($item['lessons_count']) ? (int)$item['lessons_count'] : 0;
            $course->lessons = $lessons;

            $courses[] = $course;

            if (!empty($item['children'])) {
                foreach ($this->parseCourses($item['children']) as $childCourse) {
                    $courses[] = $childCourse;
                }
            }
        }

        return $courses;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
