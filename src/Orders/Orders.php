<?php

namespace UchiPro\Orders;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Users\User;
use UchiPro\Vendors\Vendor;

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
     * @return Order
     */
    public function createOrder()
    {
        return new Order();
    }

    /**
     * @return Criteria
     */
    public function createCriteria()
    {
        return new Criteria();
    }

    /**
     * @param string $id
     *
     * @return Order|null
     */
    public function findById(string $id)
    {
        $responseData = $this->apiClient->request("/orders/{$id}");

        if (empty($responseData['order']['uuid'])) {
            return null;
        }

        return $this->parseOrder($responseData['order']);
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return array|Order[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Criteria $criteria = null)
    {
        $orders = [];

        $uri = $this->buildUri($criteria);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('orders', $responseData)) {
            throw new BadResponseException('Не удалось получить заявки.');
        }

        if (is_array($responseData['orders'])) {
            $orders = $this->parseOrders($responseData['orders']);
        }

        return $orders;
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return string
     */
    private function buildUri(Criteria $criteria = null)
    {
        $uri = '/orders';

        $uriQuery = ['vendor' => 0];
        if ($criteria) {
            if (!empty($criteria->number)) {
                $uriQuery['q'] = $criteria->number;
            }

            if (!empty($criteria->status)) {
                $uriQuery['status'] = is_array($criteria->status)
                  ? array_values($criteria->status)
                  : $criteria->status;
            }

            if (!empty($criteria->vendor)) {
                $uriQuery['vendor'] = $criteria->vendor->id;
            }

            if (!empty($criteria->withFullAcceptedOnly)) {
                $uriQuery['with_full_accepted_only'] = 1;
            }
        }

        if (!empty($uriQuery)) {
            $uri .= '?'.$this->apiClient::httpBuildQuery($uriQuery);
        }

        return $uri;
    }

    /**
     * @param array|Order[] $list
     *
     * @return array
     */
    private function parseOrders(array $list)
    {
        $orders = [];

        foreach ($list as $item) {
            $orders[] = $this->parseOrder($item);
        }

        return $orders;
    }

    private function parseOrder(array $item)
    {
        $course = new Course();
        $course->id = $item['course_uuid'] ?? null;
        $course->title = $item['course_title'] ?? null;

        $vendor = new Vendor();
        $vendor->id = $item['vendor_uuid'] ?? null;
        $vendor->title = $item['vendor_title'] ?? null;

        $contractor = new User();
        $contractor->id = $item['contractor_uuid'] ?? null;
        $contractor->name = $item['contractor_title'] ?? null;

        $order = new Order();
        $order->id = $item['uuid'] ?? null;
        $order->number = $item['number'] ?? null;
        $order->status = $item['status']['code'] ?? null;
        $order->course = $course;
        $order->vendor = $vendor;
        $order->contractor = $contractor;
        $order->listenersCount = (int)$item['listeners_count'];
        $order->listenersFinished = (int)$item['listeners_finished'];

        return $order;
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

    public function changeOrderStatus(Order $order, Status $newStatus)
    {
        $uri = "/orders/{$order->id}/status";
        $params = ['status' => $newStatus->code];
        $responseData = $this->apiClient->request($uri, $params);

        if (empty($responseData['status']['code'])) {
            throw new BadResponseException('Не удалось изменить статус заявки.');
        }

        return Status::create(
          $responseData['status']['id'],
          $responseData['status']['code'],
          $responseData['status']['title']
        );
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
