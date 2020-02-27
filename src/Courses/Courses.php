<?php

namespace UchiPro\Courses;

use Exception;
use UchiPro\ApiClient;
use UchiPro\Courses\AcademicPlan\Item;
use UchiPro\Courses\AcademicPlan\ItemType;
use UchiPro\Courses\AcademicPlan\Plan;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Vendors\Vendor;

class Courses
{
    const NULL_VALUE = '00000000-0000-0000-0000-000000000000';

    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @return Criteria
     */
    public function createCriteria()
    {
        return new Criteria();
    }

    /**
     * @param Criteria|null $query
     *
     * @return array|Course[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Criteria $query = null)
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

    private function buildUri(Criteria $query = null)
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

    private function parseCourses(array $list)
    {
        $courses = [];

        foreach ($list as $item) {
            $course = new Course();
            $course->id = $item['uuid'] ?? null;
            if ($course->id === self::NULL_VALUE) {
                $course->id = null;
            }
            $course->createdAt = $this->apiClient->parseDate($item['created_at']);
            $course->title = $item['title'] ?? null;
            $course->parentId = $item['parent_uuid'] ?? null;
            if ($course->parentId === self::NULL_VALUE) {
                $course->parentId = null;
            }
            $course->type = $this->parseCourseType($item);
            $course->hours = $item['hours'] ?? null;
            $course->price = $item['price'] ?? null;
            $course->depth = isset($item['depth']) ? (int)$item['depth'] : 0;
            $course->childrenCount = isset($item['children_count']) ? (int)$item['children_count'] : 0;
            $course->lessonsCount = isset($item['lessons_count']) ? (int)$item['lessons_count'] : 0;
            $course->lessons = $this->parseLessons($item);
            $course->academicPlan = $this->parseAcademicPlan($item);

            $courses[] = $course;

            if (!empty($item['children'])) {
                foreach ($this->parseCourses($item['children']) as $childCourse) {
                    $courses[] = $childCourse;
                }
            }
        }

        return $courses;
    }

    /**
     * @param array $item
     *
     * @return CourseType
     */
    private function parseCourseType(array $item)
    {
        $courseType = new CourseType();
        $courseType->id = $item['type']['uuid'] ?? null;
        if ($courseType->id === self::NULL_VALUE) {
            $courseType->id = null;
        }
        $courseType->title = $item['type']['title'] ?? null;

        return $courseType;
    }

    /**
     * @param array $item
     *
     * @return array|Lesson[]
     */
    private function parseLessons(array $item)
    {
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

        return $lessons;
    }

    /**
     * @param array $item
     *
     * @return Plan|null
     */
    private function parseAcademicPlan(array $item)
    {
        $planItems = [];

        if (!empty($item['settings']['academic_plan'])) {
            try {
                $json = json_decode($item['settings']['academic_plan'], true);
                if (is_array($json)) {
                    foreach ($json as $jsonItem) {
                        $itemType = new ItemType();
                        $itemType->id = $jsonItem['type'] ?? '';
                        $itemType->title = $jsonItem['type_title'] ?? '';

                        $planTtem = new Item();
                        $planTtem->title = $jsonItem['title'] ?? '';
                        $planTtem->type = $itemType;
                        $planTtem->hours = $jsonItem['hours'] ?? null;

                        $planItems[] = $planTtem;
                    }
                }
            } catch (Exception $e) {}
        }

        if (empty($planItems)) {
            return null;
        }

        $academicPlan = new Plan();
        $academicPlan->items = $planItems;

        return $academicPlan;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
