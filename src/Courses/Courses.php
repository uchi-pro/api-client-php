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
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @param null $id
     * @param null $title
     *
     * @return Course
     */
    public function createCourse($id = null, $title = null)
    {
        return Course::create($id, $title);
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

    /**
     * @param Criteria|null $criteria
     *
     * @return string
     */
    private function buildUri(Criteria $criteria = null)
    {
        $uri = '/courses?_tree=1';

        if (!empty($criteria)) {
            if ($criteria->vendor instanceof Vendor) {
                $uri = "/vendors/{$criteria->vendor->id}/courses?_tree=1";
            }

            if ($criteria->parent instanceof Course) {
                $uri = "&parent={$criteria->parent->id}";
            }

            if (!empty($criteria->gid)) {
                $uri = "&guid={$criteria->gid}";
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
            if ($course->id === $this->apiClient::EMPTY_UUID_VALUE) {
                $course->id = null;
            }
            $course->gid = $item['guid'] ?? null;
            if ($course->gid === $this->apiClient::EMPTY_UUID_VALUE) {
                $course->gid = null;
            }
            $course->createdAt = $this->apiClient->parseDate($item['created_at']);
            $course->title = $item['title'] ?? null;
            $course->parentId = $item['parent_uuid'] ?? null;
            if ($course->parentId === $this->apiClient::EMPTY_UUID_VALUE) {
                $course->parentId = null;
            }
            $course->type = $this->parseCourseType($item);
            $course->hours = $item['hours'] ?? null;
            $course->price = $item['price'] ?? null;
            $course->depth = isset($item['depth']) ? (int)$item['depth'] : 0;
            $course->childrenCount = isset($item['children_count']) ? (int)$item['children_count'] : 0;
            $course->lessonsCount = isset($item['lessons_count']) ? (int)$item['lessons_count'] : 0;
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
        if ($courseType->id === $this->apiClient::EMPTY_UUID_VALUE) {
            $courseType->id = null;
        }
        $courseType->title = $item['type']['title'] ?? null;

        return $courseType;
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
