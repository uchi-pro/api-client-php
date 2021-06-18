<?php

declare(strict_types=1);

namespace UchiPro\Leads;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;

final class Leads
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function createLead(): Lead
    {
        return new Lead();
    }

    public function createComment(string $id = null, string $text = null): Comment
    {
        return Comment::create($id, $text);
    }

    public function save(Lead $lead, Comment $comment = null): Lead
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
        }

        return $lead;
    }

    /**
     * @param Lead $lead
     * @param Comment $comment
     *
     * @return Comment
     */
    public function saveLeadComment(Lead $lead, Comment $comment): Comment
    {
        $params = [
            'comments' => $comment->text,
        ];
        $responseData = $this->apiClient->request("leads/$lead->id/comments/0", $params);

        if (isset($responseData['comment']['uuid'])) {
            $comment->id = $responseData['comment']['uuid'] ?? null;
        }

        return $comment;
    }

    public static function create(ApiClient $apiClient): Leads
    {
        return new self($apiClient);
    }
}
