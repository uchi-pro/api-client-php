<?php

declare(strict_types=1);

namespace UchiPro\Courses;

use Exception;
use UchiPro\ApiClient;
use UchiPro\Courses\AcademicPlan\Item;
use UchiPro\Courses\AcademicPlan\ItemType;
use UchiPro\Courses\AcademicPlan\Plan;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Users\User;
use UchiPro\Vendors\Vendor;

final class Courses
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function createCourse(?string $id = null, ?string $title = null): Course
    {
        return Course::create($id, $title);
    }

    public function createCriteria(): Criteria
    {
        return new Criteria();
    }

    public function findById(string $id): ?Course
    {
        $responseData = $this->apiClient->request("/courses/$id");

        if (empty($responseData['course']['uuid'])) {
            return null;
        }

        return $this->parseCourse($responseData['course']);
    }

    /**
     * @param Criteria|null $query
     *
     * @return array|Course[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(?Criteria $query = null): array
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

    private function buildUri(?Criteria $criteria): string
    {
        $uri = '/courses?_tree=1';

        if (!empty($criteria)) {
            if ($criteria->vendor instanceof Vendor) {
                $uri = "/vendors/{$criteria->vendor->id}/courses?_tree=1";
            }

            if ($criteria->parent instanceof Course) {
                $uri .= "&parent={$criteria->parent->id}";
            }

            if (!empty($criteria->gid)) {
                $uri .= "&guid=$criteria->gid";
            }

            if ($criteria->withInactive) {
                $uri .= "&show_inactive=1";
            }
        }

        return $uri;
    }

    /**
     * @param array $list
     *
     * @return array|Course[]
     */
    private function parseCourses(array $list): array
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

    private function parseCourse(array $data): Course
    {
        $course = new Course();
        $course->id = $this->apiClient->parseId($data, 'uuid');
        $course->gid = $this->apiClient->parseId($data, 'guid');
        $course->createdAt = $this->apiClient->parseDate($data['created_at']);
        $course->updatedAt = $this->apiClient->parseDate($data['updated_at']);
        $course->deletedAt = $this->apiClient->parseDate($data['deleted_at']);
        $course->isActive = isset($data['is_active']) ? (bool)$data['is_active'] : null;
        $course->author = $this->parseCourseAuthor($data);
        $course->title = $data['title'] ?? null;
        $course->description = $data['description'] ?? null;
        $course->comments = $data['comments'] ?? null;
        $course->parentId = $this->apiClient->parseId($data, 'parent_uuid');
        $course->type = $this->parseCourseType($data);
        $course->hours = $data['hours'] ?? null;
        $course->price = $data['price'] ?? null;
        $course->depth = isset($data['depth']) ? (int)$data['depth'] : 0;
        $course->childrenCount = isset($data['children_count']) ? (int)$data['children_count'] : 0;
        $course->lessonsCount = isset($data['lessons_count']) ? (int)$data['lessons_count'] : 0;
        $course->academicPlan = $this->parseAcademicPlan($data);
        $course->vendor = $this->parseVendor($data);
        return $course;
    }

    private function parseCourseType(array $data): CourseType
    {
        $courseType = new CourseType();
        if (!empty($data['type'])) {
            $courseType->id = $this->parseCourseTypeId($data['type']);
            $courseType->title = $data['type']['title'] ?? null;
        }
        return $courseType;
    }

    private function parseCourseTypeId(array $data): ?string
    {
        $id = $data['code'] ?? null;
        if (!empty($data['uuid'])) { // @todo устаревший вариант - убрать со временем
            $id = $data['uuid'] ?? null;
        }
        if ($id === $this->apiClient::EMPTY_UUID_VALUE) {
            $id = null;
        }
        return $id;
    }

    private function parseCourseAuthor(array $data): User
    {
        $user = null;

        if (!empty($data['author_uuid'])) {
            $user = new User();
            $user->id = $this->apiClient->parseId($data, 'author_uuid');
            $user->name = $data['author_title'] ?? null;
        }

        return $user;
    }

    /**
     * @param array $data
     *
     * @return Plan|null
     */
    private function parseAcademicPlan(array $data): ?Plan
    {
        $planItems = [];

        if (!empty($data['settings']['academic_plan'])) {
            try {
                $json = json_decode($data['settings']['academic_plan'], true);
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
            } catch (Exception $e) {
                // Ничего не делать.
            }
        }

        if (empty($planItems)) {
            return null;
        }

        $academicPlan = new Plan();
        $academicPlan->items = $planItems;

        return $academicPlan;
    }

    public function parseVendor(array $data): ?Vendor
    {
        if (empty($data['vendor_uuid'])) {
            return null;
        }

        $vendor = new Vendor();
        $vendor->id = $data['vendor_uuid'];
        $vendor->title = $data['vendor_title'];
        return $vendor;
    }

    public function getCourseFeatures(Course $course): CourseFeatures
    {
        $courseFeatures = new CourseFeatures();

        $lessonsResponseData = $this->apiClient->request("/courses/$course->id/lessons");
        if (empty($lessonsResponseData['lessons']) || !is_array($lessonsResponseData['lessons'])) {
            return $courseFeatures;
        }

        foreach ($lessonsResponseData['lessons'] as $lesson) {
            $this->extractLessonFeatures($lesson, $courseFeatures);
        }

        return $courseFeatures;
    }

    private function extractLessonFeatures(array $lesson, CourseFeatures $courseFeatures)
    {
        if (!$courseFeatures->interactive && $this->checkForInteractiveContent($lesson['description'])) {
            $courseFeatures->interactive = true;
        }

        if ($lesson['type'] === 'quiz') {
            $courseFeatures->testing = true;
        }
        if ($lesson['type'] === 'essay') {
            $courseFeatures->practice = true;
        }

        if (!empty($lesson['resources']) && is_array($lesson['resources'])) {
            foreach ($lesson['resources'] as $resource) {
                $this->extractResourceFeatures($resource, $courseFeatures);
            }
        }
    }

    private function extractResourceFeatures(array $resource, CourseFeatures $courseFeatures)
    {
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

    private function checkForInteractiveContent(string $content): bool
    {
        if (strpos($content, 'h5p/embed/') > 0) {
            return true;
        }
        return false;
    }

    public static function create(ApiClient $apiClient): Courses
    {
        return new self($apiClient);
    }
}
