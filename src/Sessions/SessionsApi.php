<?php

declare(strict_types=1);

namespace UchiPro\Sessions;

use UchiPro\ApiClient;
use UchiPro\Collection;
use UchiPro\Courses\Course;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Orders\Order;
use UchiPro\Users\User;

final class SessionsApi
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function newSession(): Session
    {
        return new Session();
    }

    public function newCriteria(): Criteria
    {
        return new Criteria();
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return Session[]|Collection
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Criteria $criteria = null): iterable
    {
        $sessions = new Collection();

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

        if (isset($responseData['pager'])) {
            $sessions->setPager($responseData['pager']);
        }

        return $sessions;
    }

    /**
     * @param Order $order
     *
     * @return Session[]|Collection
     */
    public function findActiveByOrder(Order $order): iterable
    {
        $criteria = $this->newCriteria();
        $criteria->order = $order;

        $allSessions = $this->findBy($criteria);

        $activeSessions = array_filter(iterator_to_array($allSessions), function (Session $session) {
            return $session->isActive();
        });

        return new Collection($activeSessions);
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return string
     */
    private function buildUri(Criteria $criteria = null): string
    {
        $uri = '/training/sessions';

        $uriQuery = ['vendor' => 0, 'active_orders_only' => 0];
        if ($criteria) {
            if (!empty($criteria->order)) {
                $uri = "/orders/{$criteria->order->id}/sessions";
            }

            if (!empty($criteria->vendor)) {
                $uriQuery['vendor'] = $criteria->vendor->id;
            }

            if (!empty($criteria->status)) {
                $uriQuery['status'] = array_map(
                  function (Status $status) { return $status->code; },
                  is_array($criteria->status) ? $criteria->status : [$criteria->status]
                );
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
     * @return Session[]|Collection
     */
    private function parseSessions(array $list): Collection
    {
        $sessions = new Collection();

        foreach ($list as $item) {
            $sessions[] = $this->parseSession($item);
        }

        return $sessions;
    }

    private function parseSession(array $data): Session
    {
        $listener = new User();
        $listener->id = $data['listener_uuid'] ?? null;
        $listener->name = $data['listener_title'] ?? null;

        $course = new Course();
        $course->id = $data['course_uuid'] ?? null;
        $course->title = $data['course_title'] ?? null;

        $order = new Order();
        $order->id = $data['order_uuid'] ?? null;
        $order->course = $course;

        $session = new Session();
        $session->id = $data['uuid'] ?? null;
        $session->createdAt = $this->apiClient->parseDate($data['created_at']);
        $session->deletedAt = !empty($data['is_deleted']) ? $this->apiClient->parseDate($data['deleted_at']) : null;
        $session->startedAt = !empty($data['is_started']) ? $this->apiClient->parseDate($data['started_at']) : null;
        $session->skippedAt = !empty($data['is_skipped']) ? $this->apiClient->parseDate($data['skipped_at']) : null;
        $session->acceptedAt = !empty($data['is_accepted']) ? $this->apiClient->parseDate($data['accepted_at']) : null;
        $session->completedAt = !empty($data['is_completed']) ? $this->apiClient->parseDate($data['completed_at']) : null;
        $session->status = Status::create($data['status']['code'], $data['status']['title']);
        $session->listener = $listener;
        $session->order = $order;

        return $session;
    }

    public static function create(ApiClient $apiClient): SessionsApi
    {
        return new static($apiClient);
    }
}
