<?php

namespace UchiPro\Leads;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;

class Leads
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
     * @return Lead
     */
    public function createLead()
    {
        return new Lead();
    }

    /**
     * @param Lead $lead
     *
     * @return Lead
     */
    public function save(Lead $lead)
    {
        $params = [
          'number' => $lead->number,
          'contact_person' => $lead->contactPerson,
          'email' => $lead->email,
          'phone' => $lead->phone,
        ];

        if (!empty($lead->contractor->id)) {
            $params['contractor'] = $lead->contractor->id;
        }

        if (!empty($lead->courses)) {
            $params['courses'] = array_map(function (Course $course) {
                return $course->id;
            }, $lead->courses);
        }

        $responseData = $this->apiClient->request('leads/0/edit', $params);

        if (isset($responseData['lead']['uuid'])) {
            $lead->id = $responseData['lead']['uuid'] ?? null;
        } else if (isset($responseData['uuid'])) {
            // @todo Устаревшее -- после обновления убрать.
            $lead->id = $responseData['uuid'] ?? null;
        }

        return $lead;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
