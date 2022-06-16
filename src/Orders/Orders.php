<?php

declare(strict_types=1);

namespace UchiPro\Orders;

use UchiPro\ApiClient;
use UchiPro\Courses\Course;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Users\User;
use UchiPro\Vendors\Vendor;

final class Orders
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function createOrder(): Order
    {
        return new Order();
    }

    public function createCriteria(): Criteria
    {
        return new Criteria();
    }

    public function findById(string $id): ?Order
    {
        $responseData = $this->apiClient->request("/orders/$id");

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
    public function findBy(Criteria $criteria = null): array
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
    private function buildUri(Criteria $criteria = null): string
    {
        $uri = '/orders';

        $uriQuery = ['vendor' => 0];
        if ($criteria) {
            if (!empty($criteria->number)) {
                $uriQuery['q'] = $criteria->number;
            }

            if (!empty($criteria->status)) {
                $uriQuery['status'] = array_map(
                  function (Status $status) { return $status->code; },
                  is_array($criteria->status) ? $criteria->status : [$criteria->status]
                );
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
     * @param array $list
     *
     * @return array|Order[]
     */
    private function parseOrders(array $list): array
    {
        $orders = [];

        foreach ($list as $item) {
            $orders[] = $this->parseOrder($item);
        }

        return $orders;
    }

    private function parseOrder(array $data): Order
    {
        $course = new Course();
        $course->id = $data['course_uuid'] ?? null;
        $course->title = $data['course_title'] ?? null;

        $vendor = new Vendor();
        $vendor->id = $data['vendor_uuid'] ?? null;
        $vendor->title = $data['vendor_title'] ?? null;

        $contractor = new User();
        $contractor->id = $data['contractor_uuid'] ?? null;
        $contractor->name = $data['contractor_title'] ?? null;

        $order = new Order();
        $order->id = $data['uuid'] ?? null;
        $order->createAt = $this->apiClient->parseDate($data['created_at']);
        $order->updatedAt = $this->apiClient->parseDate($data['updated_at']);
        $order->deletedAt = $this->apiClient->parseDate($data['deleted_at']);
        $order->number = $data['number'] ?? null;
        $order->status = Status::createByCode($data['status']['code']);
        $order->course = $course;
        $order->vendor = $vendor;
        $order->contractor = $contractor;
        $order->listenersCount = (int)$data['listeners_count'];
        $order->listenersFinished = (int)$data['listeners_finished'];

        return $order;
    }

    /**
     * @param Order $order
     *
     * @return array|Listener[]
     */
    public function getOrderListeners(Order $order): array
    {
        $listeners = [];

        $uri = "/orders/$order->id/listeners";
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
    private function parseListeners(array $list): array
    {
        $listeners = [];

        foreach ($list as $item) {
            $listeners[] = $this->parseListener($item);
        }

        return $listeners;
    }

    private function parseListener(array $data): Listener
    {
        $listener = new Listener();
        $listener->id = $data['uuid'] ?? null;
        $listener->name = $data['title'] ?? null;
        $listener->username = $data['username'] ?? null;
        $listener->password = $data['password'] ?? null;
        $listener->email = $data['email'] ?? null;
        $listener->phone = $data['phone'] ?? null;
        return $listener;
    }

    public function changeOrderStatus(Order $order, Status $newStatus): Status
    {
        $uri = "/orders/$order->id/status";
        $params = ['status' => $newStatus->code];
        $responseData = $this->apiClient->request($uri, $params);

        if (empty($responseData['status']['code'])) {
            throw new BadResponseException('Не удалось изменить статус заявки.');
        }

        return Status::createByCode($responseData['status']['code']);
    }

    public function saveOrder(Order $order): Order
    {
        $formParams = [
          'course' => $order->course->id,
          'contractor' => $order->contractor->id,
          'status' => $order->status->code,
        ];
        foreach ($order->listeners as $listener) {
           $formParams['listeners'][] = $listener->id;
        }

        $orderId = !empty($order->id) ? $order->id : 0;
        $responseData = $this->apiClient->request("/orders/$orderId/edit", $formParams);

        return $this->parseOrder($responseData['order']);
    }

    /**
     * @param Order $order
     * @param array $listeners
     * @param array $copyTo
     *
     * @return array
     */
    public function sendCredential(Order $order, array $listeners, array $copyTo = []): array
    {
        $formParams = [];

        if (!empty($copyTo)) {
            $formParams['send_copy_to'] = $copyTo;
        }

        foreach ($listeners as $listener) {
            $formParams['listener'][] = $listener->id;
        }

        return $this->apiClient->request("/orders/$order->id/listeners/send-credentials", $formParams);
    }

    public static function create(ApiClient $apiClient): Orders
    {
        return new self($apiClient);
    }
}
