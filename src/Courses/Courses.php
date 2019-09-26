<?php

namespace UchiPro\Courses;

use UchiPro\ApiClient;
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

    public function findAll(array $criteria = [])
    {
        $courses = [];

        $url = '/courses';

        if (isset($criteria['vendor']) && ($criteria['vendor'] instanceof Vendor)) {
            $url = "/vendors/{$criteria['vendor']->id}/courses";
        }

        $responseData = $this->apiClient->request($url);

        foreach ($responseData['courses'] as $item) {
            $course = new Course();
            $course->id = $item['uuid'] ?? null;
            $course->title = $item['title'] ?? null;
            $course->parentId = $item['parent_uuid'] ?? null;
            $course->hours = $item['hours'] ?? null;
            $course->price = $item['price'] ?? null;

            $courses[] = $course;
        }

        return $courses;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
