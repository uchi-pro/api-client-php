<?php

namespace UchiPro\Sessions;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Orders\Order;
use UchiPro\Users\User;

class Sessions
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
     * @return Session
     */
    public function createSession()
    {
        return new Session();
    }

    /**
     * @return Criteria
     */
    public function createCriteria()
    {
        return new Criteria();
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return array|Session[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Criteria $criteria = null)
    {
        $sessions = [];

        $uri = $this->buildUri($criteria);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('sessions', $responseData)) {
            throw new BadResponseException('Не удалось получить список сессий.');
        }

        if (is_array($responseData['sessions'])) {
            $sessions = $this->parseSessions($responseData['sessions']);

            if ($criteria->order) {
                foreach ($sessions as $session) {
                    $session->order = $criteria->order;
                }
            }
        }

        return $sessions;
    }

    /**
     * @param Order $order
     *
     * @return array|Session[]
     */
    public function findActiveByOrder(Order $order)
    {
        $criteria = $this->createCriteria();
        $criteria->order = $order;
        return array_filter($this->findBy($criteria), function (Session $session) {
            return $session->isActive();
        });
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return string
     */
    private function buildUri(Criteria $criteria = null)
    {
        $uri = '/training/sessions';

        $uriQuery = ['vendor' => 0, 'active_orders_only' => 0];
        if ($criteria) {
            if (!empty($criteria->order)) {
                $uriQuery['order'] = $criteria->order->id;
            }

            if (!empty($criteria->status)) {
                $uriQuery['status'] = is_array($criteria->status)
                  ? array_values($criteria->status)
                  : $criteria->status;
            }
        }

        if (!empty($uriQuery)) {
            $uri .= '?'.$this->apiClient::httpBuildQuery($uriQuery);
        }

        return $uri;
    }

    /**
     * @param array $list
     *
     * @return array|Session[]
     */
    private function parseSessions(array $list)
    {
        $sessions = [];

        foreach ($list as $item) {
            $listener = new User();
            $listener->id = $item['listener_uuid'] ?? null;
            $listener->name = $item['listener_title'] ?? null;

            $course = new Course();
            $course->id = $item['course_uuid'] ?? null;
            $course->title = $item['course_title'] ?? null;

            $order = new Order();
            $order->id = $item['order_uuid'] ?? null;
            $order->course = $course;

            $session = new Session();
            $session->id = $item['uuid'] ?? null;
            $session->createdAt = $this->apiClient->parseDate($item['created_at']);
            $session->deletedAt = !empty($item['is_deleted']) ? $this->apiClient->parseDate($item['deleted_at']) : null;
            $session->startedAt = !empty($item['is_started']) ? $this->apiClient->parseDate($item['started_at']) : null;
            $session->skippedAt = !empty($item['is_skipped']) ? $this->apiClient->parseDate($item['skipped_at']) : null;
            $session->acceptedAt = !empty($item['is_accepted']) ? $this->apiClient->parseDate($item['accepted_at']) : null;
            $session->completedAt = !empty($item['is_completed']) ? $this->apiClient->parseDate($item['completed_at']) : null;
            $session->status = $item['status']['code'] ?? null;
            $session->listener = $listener;
            $session->order = $order;
            $sessions[] = $session;
        }

        return $sessions;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
