<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Sessions\Criteria as SessionsCriteria;
use UchiPro\Sessions\Session;

class OrdersTest extends TestCase
{
    /**
     * @var Identity
     */
    private $identity;

    public function setUp()
    {
        $url = getenv('UCHIPRO_URL');
        $login = getenv('UCHIPRO_LOGIN');
        $password = getenv('UCHIPRO_PASSWORD');
        $accessToken = getenv('UCHIPRO_ACCESS_TOKEN');

        if (!empty($accessToken)) {
            $this->identity = Identity::createByAccessToken($url, $accessToken);
        } else {
            $this->identity = Identity::createByLogin($url, $login, $password);
        }
    }

    /**
     * @return ApiClient
     */
    public function getApiClient()
    {
        return ApiClient::create($this->identity);
    }

    public function testGetOrders()
    {
        $ordersApi = $this->getApiClient()->orders();
        $criteria = $ordersApi->createCriteria();
        $criteria->status = $criteria::STATUS_COMPLETED;
        $orders = $this->getApiClient()->orders()->findBy($criteria);

        $this->assertTrue(is_array($orders));
    }

    public function testGetOrder()
    {
        $ordersApi = $this->getApiClient()->orders();
        $orders = $ordersApi->findBy();

        $this->assertTrue(is_array($orders), 'Не удалось получить список заявок.');

        if (empty($orders)) {
            return;
        }

        $order = $orders[0];

        $ordersApi = $this->getApiClient()->orders();
        $ordersCriteria = $ordersApi->createCriteria();
        $ordersCriteria->number = $order->number;
        $ordersCriteria->vendor = $order->vendor;
        $orders = $ordersApi->findBy($ordersCriteria);

        $this->assertTrue(count($orders) === 1, 'Заявка по номеру не найдена.');

        if (!empty($orders[0])) {
            $order = $orders[0];
            $listeners = $ordersApi->getOrderListeners($order);
            $this->assertTrue(is_array($listeners));
            $this->assertTrue(count($listeners) > 0);
        }
    }

    public function testGetOrderSessions()
    {
        $ordersApi = $this->getApiClient()->orders();
        $ordersCriteria = $ordersApi->createCriteria();
        $ordersCriteria->withFullAcceptedOnly = true;
        $orders = $ordersApi->findBy($ordersCriteria);

        if (empty($orders)) {
            $this->markTestSkipped('Не найдено курсов с сессиями.');
        }

        $order = $orders[0];

        $sessionsApi = $this->getApiClient()->sessions();
        $sessionsCriteria = $sessionsApi->createCriteria();
        $sessionsCriteria->order = $order;
        $sessions = $sessionsApi->findBy($sessionsCriteria);
        $this->assertTrue(is_array($sessions));

        $sessions = $sessionsApi->findActiveByOrder($order);
        foreach ($sessions as $session) {
            $this->assertTrue($session->isActive(), 'Найденная сессия не активна.');
        }

        foreach ($this->getSessionsAvailableStatuses() as $status => $checkFunction) {
            $sessionsApi = $this->getApiClient()->sessions();
            $sessionsCriteria = $sessionsApi->createCriteria();
            $sessionsCriteria->status = $status;
            $sessions = $sessionsApi->findBy($sessionsCriteria);
            if (!empty($sessions[0])) {
                $session = $sessions[0];
                $this->assertTrue($checkFunction($session), "Статус найденной сессии {$session->status} (id: {$session->id}) не соответсвует искомому {$status}.");
            }
        }
    }

    private function getSessionsAvailableStatuses(): array
    {
        return [
          SessionsCriteria::STATUS_STARTED => function (Session $session) { return $session->isStarted(); },
          SessionsCriteria::STATUS_COMPLETED => function (Session $session) { return $session->isCompleted(); },
          SessionsCriteria::STATUS_ACCEPTED => function (Session $session) { return $session->isAccepted(); },
          SessionsCriteria::STATUS_REJECTED => function (Session $session) { return $session->isRejected(); },
        ];
    }
}
