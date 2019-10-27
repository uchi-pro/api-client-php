<?php

namespace UchiPro\Orders;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;

class Orders
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
     * @param Query|null $query
     *
     * @return array|Order[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Query $query = null)
    {
        $orders = [];

        $uri = $this->buildUri($query);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('orders', $responseData)) {
            throw new BadResponseException('Не удалось получить заявки.');
        }

        if (is_array($responseData['orders'])) {
            $orders = $this->parseOrders($responseData['orders']);
        }

        return $orders;
    }

    private function buildUri(Query $searchQuery = null)
    {
        $uri = '/orders';

        $uriQuery = [];
        if ($searchQuery) {
            if (!empty($searchQuery->number)) {
                $uriQuery['q'] = $searchQuery->number;
            }
        }

        if (!empty($uriQuery)) {
            $uri .= '?'.http_build_query($uriQuery);
        }

        return $uri;
    }

    private function parseOrders(array $list)
    {
        $orders = [];

        foreach ($list as $item) {
            $course = new Course();
            $course->id = $item['course_uuid'] ?? null;
            $course->title = $item['course_title'] ?? null;

            $order = new Order();
            $order->id = $item['uuid'] ?? null;
            $order->number = $item['number'] ?? null;
            $order->course = $course;
            $order->listenersCount = (int)$item['listeners_count'];

            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * @param Order $order
     *
     * @return array|Listener[]
     */
    public function getOrderListeners(Order $order)
    {
        $listeners = [];

        $uri = "/orders/{$order->id}/listeners";
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('listeners', $responseData)) {
            throw new BadResponseException('Не удалось получить слушателей по заявке.');
        }

        if (is_array($responseData['listeners'])) {
            $listeners = $this->parseListeners($responseData['listeners']);
        }

        return $listeners;
    }

    /**
     * @param array $list
     *
     * @return array|Listener[]
     */
    private function parseListeners(array $list)
    {
        $listeners = [];

        foreach ($list as $item) {
            $listener = new Listener();
            $listener->id = $item['uuid'] ?? null;
            $listener->name = $item['title'] ?? null;
            $listener->username = $item['username'] ?? null;
            $listener->password = $item['password'] ?? null;
            $listener->email = $item['email'] ?? null;
            $listener->phone = $item['phone'] ?? null;

            $listeners[] = $listener;
        }

        return $listeners;
    }

    /**
     * @param ApiClient $apiClient
     *
     * @return static
     */
    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}