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
     * @param string|null $id
     * @param string|null $text
     *
     * @return Comment
     */
    public function createComment($id = null, $text = null)
    {
        return Comment::create($id, $text);
    }

    /**
     * @param Lead $lead
     * @param Comment $comment
     *
     * @return Lead
     */
    public function save(Lead $lead, Comment $comment = null)
    {
        $params = [
          'number' => $lead->number,
          'contact_person' => $lead->contactPerson,
          'email' => $lead->email,
          'phone' => $lead->phone,
        ];

        if (!empty($comment->text)) {
            $params['comments'] = $comment->text;
        }

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

    /**
     * @param Lead $lead
     * @param Comment $comment
     *
     * @return Comment
     */
    public function saveLeadComment(Lead $lead, Comment $comment)
    {
        $params = [
            'comments' => $comment->text,
        ];
        $responseData = $this->apiClient->request("leads/{$lead->id}/comments/0", $params);

        if (isset($responseData['comment']['uuid'])) {
            $comment->id = $responseData['comment']['uuid'] ?? null;
        }

        return $comment;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
