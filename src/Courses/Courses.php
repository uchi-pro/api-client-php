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

    /**
     * @param array $list
     *
     * @return array|Course[]
     */
    private function parseCourses(array $list)
    {
        $courses = [];

        foreach ($list as $item) {
            $courses[] = $this->parseCourse($item);

            if (!empty($item['children'])) {
                foreach ($this->parseCourses($item['children']) as $childCourse) {
                    $courses[] = $childCourse;
                }
            }
        }

        return $courses;
    }

    /**
     * @param array $data
     *
     * @return Course
     */
    private function parseCourse($data)
    {
        $course = new Course();
        $course->id = $this->apiClient->parseId($data, 'uuid');
        $course->gid = $this->apiClient->parseId($data, 'guid');
        $course->createdAt = $this->apiClient->parseDate($data['created_at']);
        $course->title = $data['title'] ?? null;
        $course->description = $data['description'] ?? null;
        $course->parentId = $this->apiClient->parseId($data, 'parent_uuid');
        $course->type = $this->parseCourseType($data);
        $course->hours = $data['hours'] ?? null;
        $course->price = $data['price'] ?? null;
        $course->depth = isset($data['depth']) ? (int)$data['depth'] : 0;
        $course->childrenCount = isset($data['children_count']) ? (int)$data['children_count'] : 0;
        $course->lessonsCount = isset($data['lessons_count']) ? (int)$data['lessons_count'] : 0;
        $course->academicPlan = $this->parseAcademicPlan($data);
        return $course;
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

    /**
     * @param Course $course
     *
     * @return CourseFeatures
     */
    public function getCourseFeatures(Course $course)
    {
        $courseFeatures = new CourseFeatures();

        $lessonsResponseData = $this->apiClient->request("/courses/{$course->id}/lessons");
        foreach ($lessonsResponseData['lessons'] as $lesson) {
            if (!$courseFeatures->interactive) {
                if ($this->checkForInteractiveContent($lesson['description'])) {
                    $courseFeatures->interactive = true;
                }
            }

            if (!empty($lesson['resources'])) {
                foreach ($lesson['resources'] as $resource) {
                    if ($resource['slides_count']) {
                        $courseFeatures->slides = true;
                    }
                    if ($resource['videos_count']) {
                        $courseFeatures->video = true;
                    }

                    if (!$courseFeatures->interactive) {
                        $resourcesResponseData = $this->apiClient->request("/resources/{$resource['id']}/contents");
                        if (!empty($resourcesResponseData['contents'])) {
                            foreach ($resourcesResponseData['contents'] as $content) {
                                $contentResponseData = $this->apiClient->request(
                                  "/resources/{$resource['id']}/contents/{$content['id']}"
                                );
                                if ($this->checkForInteractiveContent($contentResponseData['content']['body'])) {
                                    $courseFeatures->interactive = true;
                                }
                            }
                        }
                    }
                }
            }
            if ($lesson['type'] === 'quiz') {
                $courseFeatures->testing = true;
            }
            if ($lesson['type'] === 'essay') {
                $courseFeatures->practice = true;
            }
        }

        return $courseFeatures;
    }

    /**
     * @param $content
     *
     * @return bool
     */
    private function checkForInteractiveContent($content)
    {
        if (strpos($content, 'h5p/embed/') > 0) {
            return true;
        }
        return false;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
