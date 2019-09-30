<?php

namespace UchiPro\Courses;

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
     * @param array $criteria
     *
     * @return array|Course[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findAll(array $criteria = [])
    {
        $courses = [];

        $url = '/courses';

        if (isset($criteria['vendor']) && ($criteria['vendor'] instanceof Vendor)) {
            $url = "/vendors/{$criteria['vendor']->id}/courses";
        }

        $responseData = $this->apiClient->request($url);

        if (!isset($responseData['courses'])) {
            throw new BadResponseException('Не удалось получить список курсов.');
        }

        if (is_array($responseData['courses'])) {
            foreach ($responseData['courses'] as $item) {
                $course = new Course();
                $course->id = $item['uuid'] ?? null;
                $course->title = $item['title'] ?? null;
                $course->parentId = $item['parent_uuid'] ?? null;
                $course->hours = $item['hours'] ?? null;
                $course->price = $item['price'] ?? null;

                $courses[] = $course;
            }
        }

        return $courses;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
