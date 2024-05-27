<?php

declare(strict_types=1);

namespace UchiPro\Leads;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;

final class LeadsApi
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function newLead(): Lead
    {
        return new Lead();
    }

    /** @deprecated */
    public function createLead(): Lead
    {
        return self::newLead();
    }

    public function newComment(string $id = null, string $text = null): Comment
    {
        return Comment::create($id, $text);
    }

    /** @deprecated */
    public function createComment(string $id = null, string $text = null): Comment
    {
        return self::newComment($id, $text);
    }

    public function save(Lead $lead, Comment $comment = null, array $additionalParams = []): Lead
    {
        $formParams = [
          'number' => $lead->number,
          'contact_person' => $lead->contactPerson,
          'email' => $lead->email,
          'phone' => $lead->phone,
        ];

        if (!empty($comment->text)) {
            $formParams['comments'] = $comment->text;
        }

        if (!empty($lead->contractor->id)) {
            $formParams['contractor'] = $lead->contractor->id;
        }

        if (!empty($lead->courses)) {
            $formParams['courses'] = array_map(function (Course $course) {
                return $course->id;
            }, $lead->courses);
        }

        foreach ($additionalParams as $key => $value) {
            $formParams[$key] = $value;
        }

        $responseData = $this->apiClient->request('leads/0/edit', $formParams);

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
    public function saveLeadComment(Lead $lead, Comment $comment, array $additionalParams = []): Comment
    {
        $params = [
            'comments' => $comment->text,
        ];

        foreach ($additionalParams as $key => $value) {
            $formParams[$key] = $value;
        }

        $responseData = $this->apiClient->request("leads/$lead->id/comments/0", $params);

        if (isset($responseData['comment']['uuid'])) {
            $comment->id = $responseData['comment']['uuid'] ?? null;
        }

        return $comment;
    }

    public static function create(ApiClient $apiClient): LeadsApi
    {
        return new self($apiClient);
    }
}
