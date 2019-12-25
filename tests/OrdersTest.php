<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Orders\Criteria;

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
        $query = new Criteria();
        $query->status = $query::STATUS_COMPLETED;
        $orders = $this->getApiClient()->orders()->findBy($query);

        $this->assertTrue(is_array($orders));
    }

    public function testGetOrder()
    {
        $criteria = new Criteria();
        $criteria->number = '1804/2019-1';
        $orders = $this->getApiClient()->orders()->findBy($criteria);

        $this->assertTrue(is_array($orders));

        if (isset($orders[0])) {
            $order = $orders[0];
            $listeners = $this->getApiClient()->orders()->getOrderListeners($order);
            $this->assertTrue(is_array($listeners));
            $this->assertTrue(count($listeners) > 0);
        }
    }
}
