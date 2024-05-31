<?php

declare(strict_types=1);

namespace UchiPro\Leads;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;

final readonly class LeadsApi
{
    private function __construct(private ApiClient $apiClient) {}

    public function newLead(): Lead
    {
        return new Lead();
    }

    public function newComment(string $id = null, string $text = null): Comment
    {
        return Comment::create($id, $text);
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
            $formParams['courses'] = array_map(fn(Course $course) => $course->id, $lead->courses);
        }

        foreach ($additionalParams as $key => $value) {
            $formParams[$key] = $value;
        }

        $responseData = $this->apiClient->request('leads/0/edit', $formParams);

        if (isset($responseData['lead']['uuid'])) {
            $lead->id = $responseData['lead']['uuid'];
        }

        return $lead;
    }

    /**
     * @param Lead $lead
     * @param Comment $comment
     * @param array $additionalParams
     *
     * @return Comment
     */
    public function saveLeadComment(Lead $lead, Comment $comment, array $additionalParams = []): Comment
    {
        $formParams = [
            'comments' => $comment->text,
        ];

        foreach ($additionalParams as $key => $value) {
            $formParams[$key] = $value;
        }

        $responseData = $this->apiClient->request("leads/$lead->id/comments/0", $formParams);

        if (isset($responseData['comment']['uuid'])) {
            $comment->id = $responseData['comment']['uuid'];
        }

        return $comment;
    }

    public static function create(ApiClient $apiClient): self
    {
        return new self($apiClient);
    }
}
