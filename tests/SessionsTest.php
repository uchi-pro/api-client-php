<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Sessions\Session;
use UchiPro\Sessions\Status;

class SessionsTest extends TestCase
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

    public function testCreateSession(): void
    {
        $session = $this->getApiClient()->sessions()->createSession();
        $this->assertInstanceOf(Session::class, $session);
    }

    public function testCreateSessionStatus()
    {
        $status = Status::createStarted();
        $this->assertTrue($status->isStarted());

        $status = Status::createCompleted();
        $this->assertTrue($status->isCompleted());

        $status = Status::createAccepted();
        $this->assertTrue($status->isAccepted());

        $status = Status::createRejected();
        $this->assertTrue($status->isRejected());
    }

    public function testGetOrderSessions(): void
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

        foreach ($this->getSessionsAvailableStatuses() as [$status, $checkFunction]) {
            $sessionsApi = $this->getApiClient()->sessions();
            $sessionsCriteria = $sessionsApi->createCriteria();
            $sessionsCriteria->status = $status;
            $sessions = $sessionsApi->findBy($sessionsCriteria);
            if (!empty($sessions[0])) {
                $session = $sessions[0];
                $this->assertTrue($checkFunction($session), "Статус найденной сессии {$session->status->code} (id: $session->id) не соответсвует искомому $status->code.");
            }
        }
    }

    private function getSessionsAvailableStatuses(): array
    {
        return [
          [Status::createStarted(), function (Session $session) { return $session->isStarted(); }],
          [Status::createCompleted(), function (Session $session) { return $session->isCompleted(); }],
          [Status::createAccepted(), function (Session $session) { return $session->isAccepted(); }],
          [Status::createRejected(), function (Session $session) { return $session->isRejected(); }],
        ];
    }
}
