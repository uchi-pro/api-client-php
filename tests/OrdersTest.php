<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Orders\Order;
use UchiPro\Orders\Status;
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

        $this->identity = !empty($accessToken)
          ? Identity::createByAccessToken($url, $accessToken)
          : Identity::createByLogin($url, $login, $password);
    }

    public function getApiClient(): ApiClient
    {
        return ApiClient::create($this->identity);
    }

    public function testCreateOrder(): void
    {
        $order = $this->getApiClient()->orders()->createOrder();
        $this->assertInstanceOf(Order::class, $order);
    }

    public function testCreateSession(): void
    {
        $session = $this->getApiClient()->sessions()->createSession();
        $this->assertInstanceOf(Session::class, $session);
    }

    public function testCreateOrderStatus()
    {
        $status = Status::createPending();
        $this->assertTrue($status->isPending());
    }

    public function testGetOrders(): void
    {
        $ordersApi = $this->getApiClient()->orders();
        $criteria = $ordersApi->createCriteria();
        $criteria->status = Status::createCompleted();
        $orders = $this->getApiClient()->orders()->findBy($criteria);

        $this->assertTrue(is_array($orders));
    }

    public function testGetOrder(): void
    {
        $ordersApi = $this->getApiClient()->orders();
        $orders = $ordersApi->findBy();

        $this->assertTrue(is_array($orders), 'Не удалось получить список заявок.');

        if (empty($orders)) {
            return;
        }

        $order = null;
        foreach ($orders as $existsOrder) {
            if ($existsOrder->listenersCount > 0) {
                $order = $existsOrder;
                break;
            }
        }

        if (empty($order)) {
            $this->markTestSkipped('Не найдена заявка со слушателями.');
        }

        $ordersApi = $this->getApiClient()->orders();
        $ordersCriteria = $ordersApi->createCriteria();
        $ordersCriteria->number = $order->number;
        $ordersCriteria->vendor = $order->vendor;
        $orders = $ordersApi->findBy($ordersCriteria);

        $this->assertTrue(count($orders) === 1, 'Заявка по номеру не найдена.');

        if (!empty($orders[0])) {
            $order = $orders[0];
            $listeners = $ordersApi->getOrderListeners($order);
            $this->assertTrue(!empty($order->contractor->id));
            $this->assertTrue(is_array($listeners));
            $this->assertTrue(count($listeners) > 0);
            $this->assertInstanceOf(Status::class, $order->status);
        }
    }

    public function testFindOrderById(): void
    {
        $ordersApi = $this->getApiClient()->orders();

        $orders = $ordersApi->findBy();
        if (empty($orders)) {
            $this->markTestSkipped('В СДО нет заявок.');
        }

        $existsOrder = $orders[0];
        $foundOrder = $ordersApi->findById($existsOrder->id);

        $this->assertSame($foundOrder->id, $existsOrder->id);
    }

    public function testChangeOrderStatus(): void
    {
        $ordersApi = $this->getApiClient()->orders();
        $orders = $ordersApi->findBy();

        if (empty($orders)) {
            $this->markTestSkipped('Не найдено курсов с сессиями.');
        }

        $order = $orders[0];

        $newStatus = !$order->status->isPending()
          ? Status::createPending()
          : Status::createTraining();

        $changedStatus = $ordersApi->changeOrderStatus($order, $newStatus);

        $this->assertTrue($newStatus->code === $changedStatus->code);
    }

    public function testSaveOrder(): void
    {
        $ordersApi = $this->getApiClient()->orders();

        $orders = $ordersApi->findBy();

        if (empty($orders)) {
            $this->markTestSkipped('Заявки не найдены.');
        }

        $originalOrder = null;
        foreach ($orders as $existsOrder) {
            if ($existsOrder->listenersCount > 5) {
                $originalOrder = $existsOrder;
            }
        }

        if (empty($originalOrder)) {
            $this->markTestSkipped('Заявка не найдена.');
        }

        $originalOrder->listeners = $ordersApi->getOrderListeners($originalOrder);

        $originalOrder->id = 0;
        $newOrder = $ordersApi->saveOrder($originalOrder);

        $this->assertNotSame($originalOrder->id, $newOrder->id);
        $this->assertSame($originalOrder->listenersCount, $newOrder->listenersCount);
        $this->assertSame($originalOrder->course->id, $newOrder->course->id);
    }

    public function testSendCredential(): void
    {
        $ordersApi = $this->getApiClient()->orders();

        $orders = $ordersApi->findBy();

        if (empty($orders)) {
            $this->markTestSkipped('Заявки не найдены.');
        }

        $order = null;
        foreach ($orders as $existsOrder) {
            if ($existsOrder->listenersCount > 5) {
                $order = $existsOrder;
            }
        }

        if (empty($order)) {
            $this->markTestSkipped('Заявка не найдена.');
        }

        $copyTo = [];
        $listeners = $ordersApi->getOrderListeners($order);
        $result = $ordersApi->sendCredential($order, $listeners, $copyTo);
        $this->assertNotEmpty($result['success']);
    }
}
