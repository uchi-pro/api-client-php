<?php

namespace UchiPro\Sessions;

use UchiPro\ApiClient;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;
use UchiPro\Orders\Order;

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
     * @param array $criteria
     *
     * @return array|Session[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(array $criteria = [])
    {
        $sessions = [];

        $url = '/training/sessions';

        if (isset($criteria['order']) && ($criteria['order'] instanceof Order)) {
            $url = "/orders/{$criteria['order']->id}/sessions";
        }

        $responseData = $this->apiClient->request($url);

        if (!array_key_exists('sessions', $responseData)) {
            throw new BadResponseException('Не удалось получить список сессий.');
        }

        if (is_array($responseData['sessions'])) {
            foreach ($responseData['sessions'] as $item) {
                $session = new Session();
                $session->id = $item['uuid'] ?? null;

                $sessions[] = $session;
            }
        }

        return $sessions;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
