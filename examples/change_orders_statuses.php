<?php

/**
 * Пример массового изменения статусов заявок.
 */

use UchiPro\{ApiClient, Identity, Orders\Status};

require __DIR__.'/../vendor/autoload.php';

$apiClient = getApiClient();

$criteria = $apiClient->orders()->createCriteria();
$criteria->status = Status::createTraining();
$trainingOrders = $apiClient->orders()->findBy($criteria);

foreach ($trainingOrders as $order) {
    print "{$order->number} {$order->status->code}\n";
    $apiClient->orders()->changeOrderStatus($order, Status::createCompleted());
}

/**
 * @return ApiClient
 */
function getApiClient(): ApiClient
{
    $url = getenv('UCHIPRO_URL');
    $login = getenv('UCHIPRO_LOGIN');
    $password = getenv('UCHIPRO_PASSWORD');

    $identity = Identity::createByLogin($url, $login, $password);
    return ApiClient::create($identity);
}
