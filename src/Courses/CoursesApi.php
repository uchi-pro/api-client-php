<?php

declare(strict_types=1);

namespace UchiPro\Courses;

use Exception;
use UchiPro\ApiClient;
use UchiPro\Collection;
use UchiPro\Courses\AcademicPlan\Item;
use UchiPro\Courses\AcademicPlan\ItemType;
use UchiPro\Courses\AcademicPlan\Plan;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Users\User;
use UchiPro\Vendors\Vendor;

final class CoursesApi
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function newCourse(?string $id = null, ?string $title = null): Course
    {
        return Course::create($id, $title);
    }

    /** @deprecated */
    public function createCourse(?string $id = null, ?string $title = null): Course
    {
        return self::newCourse($id, $title);
    }

    public function newLesson(): Lesson
    {
        return new Lesson();
    }

    public function newCriteria(): Criteria
    {
        return new Criteria();
    }

    /** @deprecated */
    public function createCriteria(): Criteria
    {
        return self::newCriteria();
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
     * @param ?Criteria $criteria
     *
     * @return Course[]|Collection
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(?Criteria $criteria = null): iterable
    {
        $courses = new Collection();

        if (is_null($criteria)) {
            $criteria = $this->newCriteria();
        }

        $uri = $this->buildUri($criteria);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('courses', $responseData)) {
            throw new BadResponseException('Не удалось получить список курсов.');
        }

        if (is_array($responseData['courses'])) {
            $courses = $this->parseCourses($responseData['courses']);
        }

        if (isset($responseData['pager'])) {
            $courses->setPager($responseData['pager']);
        }

        return $courses;
    }

    private function buildUri(?Criteria $criteria): string
    {
        $uri = '/courses?_tree=1';

        if (!empty($criteria)) {
            if ($criteria->vendor instanceof Vendor) {
                if ($criteria->vendor->id === $this->apiClient::EMPTY_UUID_VALUE) {
                    $uri .= "&vendor={$criteria->vendor->id}";
                } else {
                    $uri = "/vendors/{$criteria->vendor->id}/courses?_tree=1";
                }
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

            if ($criteria->withDeleted) {
                $uri .= "&show_deleted=1";
            }
        }

        return $uri;
    }

    /**
     * @param array $list
     *
     * @return Course[]|Collection
     */
    private function parseCourses(array $list): Collection
    {
        $courses = new Collection();

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
        $course->tags = $this->parseTags($data);

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
            $id = $data['uuid'];
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

    private function parseAcademicPlan(array $courseData): ?Plan
    {
        $planItems = [];

        if (!empty($courseData['settings']['academic_plan'])) {
            try {
                $json = json_decode($courseData['settings']['academic_plan'], true);
                if (is_array($json)) {
                    foreach ($json as $jsonItem) {
                        $itemType = new ItemType();
                        if (isset($jsonItem['type_title'])) {
                            $itemType->id = $jsonItem['type'] ?? '';
                            $itemType->title = $jsonItem['type_title'] ?? '';
                        } else {
                            $itemType->title = $jsonItem['type'] ?? '';
                        }

                        $planItem = new Item();
                        $planItem->title = $jsonItem['title'] ?? '';
                        $planItem->type = $itemType;
                        $planItem->hours = $jsonItem['hours'] ?? null;

                        $planItems[] = $planItem;
                    }
                }
            } catch (Exception $e) {
                // Ничего не делать.
            }
        }

        if (!empty($courseData['academic_plan']) && is_array($courseData['academic_plan'])) {
            foreach ($courseData['academic_plan'] as $item) {
                $itemType = new ItemType();
                if (isset($item['type_title'])) {
                    $itemType->id = $item['type'] ?? '';
                    $itemType->title = $item['type_title'] ?? '';
                } else {
                    $itemType->title = $item['type'] ?? '';
                }

                $planItem = new Item();
                $planItem->title = $item['title'] ?? '';
                $planItem->type = $itemType;
                $planItem->hours = $item['hours'] ?? null;

                $planItems[] = $planItem;
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
        $vendor->title = $data['vendor_title'] ?? '';
        return $vendor;
    }

    /**
     * @param array $courseData
     *
     * @return iterable|Tag[]|null
     */
    public function parseTags(array $courseData): ?iterable
    {
        if (!isset($courseData['tags'])) {
            return null;
        }

        $tags = [];
        foreach ($courseData['tags'] as $tagData) {
            $tag = $this->parseTag($tagData);
            if (!empty($tag)) {
                $tags[] = $tag;
            }
        }
        return $tags;
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

    /**
     * @return Tag[]
     */
    public function getTagsTree(): iterable
    {
        $responseData = $this->apiClient->request("/tags?_tree=1");

        $tags = [];
        foreach ($responseData['tags'] as $tagData) {
            $tag = $this->parseTag($tagData);
            if (!empty($tag)) {
                $tags[] = $tag;
            }
        }
        return $tags;
    }

    /**
     * @deprecated
     * @see getTagsTree
     */
    public function fetchTagsTree(): iterable
    {
        return $this->getTagsTree();
    }

    public function parseTag(array $data): ?Tag
    {
        if (empty($data['uuid'])) {
            return null;
        }

        $tag = new Tag();
        $tag->id = $data['uuid'];
        $tag->isActive = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        $tag->parentId = $data['parent_uuid'];
        $tag->title = $data['title'];
        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $childTagData) {
                $childTag = $this->parseTag($childTagData);
                if (!empty($childTag)) {
                    $tag->children[] = $childTag;
                }
            }
        }
        return $tag;
    }

    private function extractLessonFeatures(array $lesson, CourseFeatures $courseFeatures)
    {
        if (isset($lesson['type'])) {
            // Старый вариант.
            if ($lesson['type'] === 'quiz') {
                $courseFeatures->testing = true;
            }
            if ($lesson['type'] === 'essay') {
                $courseFeatures->practice = true;
            }
        } elseif ($lesson['type_info']) {
            if ($lesson['type_info']['code'] === 'quiz') {
                $courseFeatures->testing = true;
            }
            if ($lesson['type_info']['code'] === 'essay') {
                $courseFeatures->practice = true;
            }
            if ($lesson['type_info']['code'] === 'scorm') {
                $courseFeatures->interactive = true;
            }
        }

        if (!$courseFeatures->interactive && $this->checkForInteractiveContent($lesson['description'])) {
            $courseFeatures->interactive = true;
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

    public function saveCourse(Course $course): Course
    {
        $formParams = [
            'course' => $course->id,
        ];

        if (!empty($course->title)) {
            $formParams['title'] = $course->title;
            $formParams['display_title'] = $course->title;
        }

        $courseId = !empty($course->id) ? $course->id : 0;
        $uri = "/courses/$courseId";
        $responseData = $this->apiClient->request($uri, $formParams);

        return $this->parseCourse($responseData['course']);
    }

    public function saveLesson(Course $course, Lesson $lesson): Lesson
    {
        $formParams = [
            'lesson' => $lesson->id ?? 0,
            'course' => $course->id,
            'type' => $lesson->type->id,
            'title' => $lesson->title,
        ];

        $uri = "/courses/$course->id/lessons/0";
        $responseData = $this->apiClient->request($uri, $formParams);
        return $this->parseLesson($responseData['lesson']);
    }

    private function parseLesson(array $data): Lesson
    {
        $lesson = new Lesson();
        $lesson->id = $this->apiClient->parseId($data, 'uuid');
        $lesson->title = $data['title'] ?? null;
        $lesson->type = LessonType::create($data['type_info']['code'], $data['type_info']['title']);
        return $lesson;
    }

    public static function create(ApiClient $apiClient): CoursesApi
    {
        return new self($apiClient);
    }
}
