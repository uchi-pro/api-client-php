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
        $createdOrder = $this->getApiClient()->orders()->createOrder();
        $this->assertInstanceOf(Order::class, $createdOrder);
    }

    public function testCreateSession(): void
    {
        $createdSession = $this->getApiClient()->sessions()->createSession();
        $this->assertInstanceOf(Session::class, $createdSession);
    }

    public function testCreateOrderStatus()
    {
        $createdPendingStatus = Status::createPending();
        $this->assertTrue($createdPendingStatus->isPending());
    }

    public function testFindCompletedOrders(): void
    {
        $ordersApi = $this->getApiClient()->orders();
        $criteria = $ordersApi->createCriteria();
        $criteria->status = Status::createCompleted();
        $foundOrders = $this->getApiClient()->orders()->findBy($criteria);

        $this->assertNotEmpty($foundOrders, 'Не удалось найти завершенные заявки.');
    }

    public function testFindOrderByNumber(): void
    {
        $existsOrder = $this->findOrderWithListeners();

        $ordersApi = $this->getApiClient()->orders();
        $ordersCriteria = $ordersApi->createCriteria();
        $ordersCriteria->number = $existsOrder->number;
        $ordersCriteria->vendor = $existsOrder->vendor;
        $foundOrders = $ordersApi->findBy($ordersCriteria);

        $this->assertTrue(count($foundOrders) === 1, 'Заявка по номеру не найдена.');

        if (!empty($foundOrders[0])) {
            $foundOrder = $foundOrders[0];
            $listeners = $ordersApi->getOrderListeners($foundOrder);
            $this->assertNotEmpty($foundOrder->contractor->id, 'Не удалось найти заявку по номеру.');
            $this->assertNotEmpty($listeners);
            $this->assertInstanceOf(Status::class, $foundOrder->status);
        }
    }

    public function testFindOrderById(): void
    {
        $ordersApi = $this->getApiClient()->orders();

        $existsOrder = $this->findFirstOrder();
        $foundOrder = $ordersApi->findById($existsOrder->id);

        $this->assertSame($foundOrder->id, $existsOrder->id, 'Не удалось найти заявку по идентификатору.');
    }

    public function testChangeOrderStatus(): void
    {
        $ordersApi = $this->getApiClient()->orders();

        $order = $this->findFirstOrder();

        $newStatus = !$order->status->isPending()
          ? Status::createPending()
          : Status::createTraining();

        $changedStatus = $ordersApi->changeOrderStatus($order, $newStatus);

        $this->assertTrue($newStatus->code === $changedStatus->code, 'Не удалось изменить статус заявки.');
    }

    public function testSaveOrder(): void
    {
        $ordersApi = $this->getApiClient()->orders();

        $originalOrder = $this->findOrderWithListeners(5);
        $originalOrder->listeners = $ordersApi->getOrderListeners($originalOrder);

        $originalOrder->id = 0;
        $newOrder = $ordersApi->saveOrder($originalOrder);

        $this->assertNotSame($originalOrder->id, $newOrder->id, 'Не удалось сохранить заявку.');
        $this->assertSame($originalOrder->listenersCount, $newOrder->listenersCount);
        $this->assertSame($originalOrder->course->id, $newOrder->course->id);
    }

    public function testSendCredential(): void
    {
        $ordersApi = $this->getApiClient()->orders();

        $existsOrder = $this->findOrderWithListeners(5);

        $copyTo = [];
        $listeners = $ordersApi->getOrderListeners($existsOrder);
        $result = $ordersApi->sendCredential($existsOrder, $listeners, $copyTo);
        $this->assertArrayHasKey('success', $result, "Не удалось отправить доступы слушателям заявки $existsOrder->number");
    }

    private function findAllOrders(): array
    {
        $ordersApi = $this->getApiClient()->orders();
        $orders = $ordersApi->findBy();
        if (empty($orders)) {
            $this->markTestSkipped('В СДО нет заявок.');
        }
        return $orders;
    }

    private function findFirstOrder(): Order
    {
        return $this->findAllOrders()[0];
    }

    private function findOrderWithListeners(int $minListeners = 0): Order
    {
        foreach ($this->findAllOrders() as $order) {
            if ($order->listenersCount > $minListeners) {
                return $order;
            }
        }

        $this->markTestSkipped('Заявка со слушателями не найдена.');
    }
}
