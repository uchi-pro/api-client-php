<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use DateTimeImmutable;
use UchiPro\Orders\Order;
use UchiPro\Orders\Status;
use UchiPro\Sessions\Session;

class OrdersTest extends TestCase
{
    public function testNewOrder(): void
    {
        $order = $this->getApiClient()->orders()->newOrder();
        $this->assertInstanceOf(Order::class, $order);
    }

    public function testNewOrderWithId(): void
    {
        $orderId = '123-456';
        $order = $this->getApiClient()->orders()->newOrder($orderId);
        $this->assertEquals($orderId, $order->id);
    }

    public function testNewSession(): void
    {
        $session = $this->getApiClient()->sessions()->newSession();
        $this->assertInstanceOf(Session::class, $session);
    }

    public function testCreateOrderStatus()
    {
        $pendingStatus = Status::createPending();
        $this->assertTrue($pendingStatus->isPending());
    }

    public function testFindAllOrders(): void
    {
        $allOrders = $this->getApiClient()->orders()->findAll();
        $this->assertNotCount(0, $allOrders, 'Не удалось найти все заявки.');
    }

    public function testFindAllOrdersByPage(): void
    {
        $ordersApi = $this->getApiClient()->orders();
        $criteria = $ordersApi->newCriteria();
        $criteria->page = 2;
        $criteria->perPage = 10;
        $foundOrders = $this->getApiClient()->orders()->findBy($criteria);

        $this->assertEquals($criteria->page, $foundOrders->getPage(), 'Вернулась не та страница.');
        $this->assertEquals($criteria->perPage, $foundOrders->getPerPage(), 'Вернулось не то число заявок на страницу.');
        $this->assertCount($criteria->perPage, $foundOrders, 'Вернулось не то число результатов');

        $this->assertGreaterThanOrEqual($foundOrders->getTotalItems(), $foundOrders->getTotalPages() * $foundOrders->getPerPage());
        $this->assertLessThanOrEqual($foundOrders->getTotalItems(), ($foundOrders->getTotalPages() - 1) * $foundOrders->getPerPage());

        $i = 0;
        foreach ($foundOrders as $order) {
            $i++;
        }
        $this->assertEquals($criteria->perPage, $i, 'Вернулось не то число результатов');
    }

    public function testFindCompletedOrders(): void
    {
        $ordersApi = $this->getApiClient()->orders();
        $criteria = $ordersApi->newCriteria();
        $criteria->status = Status::createCompleted();
        $foundOrders = $this->getApiClient()->orders()->findBy($criteria);

        $this->assertNotEmpty($foundOrders, 'Не удалось найти завершенные заявки.');
    }

    public function testFindOrderByNumber(): void
    {
        $existsOrder = $this->findOrderWithListeners();

        $ordersApi = $this->getApiClient()->orders();
        $ordersCriteria = $ordersApi->newCriteria();
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
        $originalOrder->id = null;

        $newOrder = $ordersApi->saveOrder($originalOrder);

        $this->assertNotSame($originalOrder->id, $newOrder->id, 'Не удалось сохранить заявку.');
        $this->assertSame($originalOrder->listenersCount, $newOrder->listenersCount);
        $this->assertSame($originalOrder->course->id, $newOrder->course->id);

        $today = new DateTimeImmutable('now');
        $this->assertSame($today->format('Y-m-d'), $newOrder->createdAt->format('Y-m-d'));
    }

    public function testSendCredential(): void
    {
        $ordersApi = $this->getApiClient()->orders();

        $existsOrder = $ordersApi->findById('75e487db-836a-4980-92d0-e01d25de18f9');

        $copyTo = [];
        $listeners = $ordersApi->getOrderListeners($existsOrder);
        $result = $ordersApi->sendCredential($existsOrder, $listeners, $copyTo);
        $this->assertArrayHasKey('success', $result, "Не удалось отправить доступы слушателям заявки $existsOrder->number");
    }

    public function testSortStatuses()
    {
        $statuses = [
            Status::createTraining(),
            Status::createDocumentsReady(),
            Status::createAccepted(),
            Status::createPending(),
            Status::createCanceled(),
            Status::createCompleted(),
            Status::createTrainingComplete(),
            Status::createAwaitingPayment(),
        ];

        usort($statuses, function (Status $a, Status $b) {
            return $a->greaterThan($b);
        });

        $this->assertEquals(Status::STATUS_PENDING, $statuses[0]->code);
        $this->assertEquals(Status::STATUS_CANCELED, $statuses[count($statuses) - 1]->code);
    }

    public function testCreatePendingStatus()
    {
        $this->assertTrue(Status::createPending()->isPending());
    }

    public function testCreateAcceptedStatus()
    {
        $this->assertTrue(Status::createAccepted()->isAccepted());
    }

    public function testCreateTrainingStatus()
    {
        $this->assertTrue(Status::createTraining()->isTraining());
    }

    public function testCreateTrainingCompleteStatus()
    {
        $this->assertTrue(Status::createTrainingComplete()->isTrainingComplete());
    }

    public function testCreateAwaitingPaymentStatus()
    {
        $this->assertTrue(Status::createAwaitingPayment()->isAwaitingPayment());
    }

    public function testCreateDocumentReadyStatus()
    {
        $this->assertTrue(Status::createDocumentsReady()->isDocumentsReady());
    }

    public function testCreateCompletedStatus()
    {
        $this->assertTrue(Status::createCompleted()->isCompleted());
    }

    public function testCreateCanceledStatus()
    {
        $this->assertTrue(Status::createCanceled()->isCanceled());
    }

    private function findAllOrders(): iterable
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
