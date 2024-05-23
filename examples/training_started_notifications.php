<?php

/**
 * Пример альтернативной рассылки уведомлений слушателям о начале обучения.
 *
 * Может использоваться:
 * - если требуется отправить уведомление о начале обучения с текстом,
 * отличным от заданного по умолчанию в СДО.
 * - если требуется отправить уведомление по альтернативным каналам (sms, viber и пр.).
 */

use UchiPro\{ApiClient, Identity, Orders\Listener, Orders\Order};

require __DIR__.'/../vendor/autoload.php';

if (empty($argv[1])) {
    exit('Укажите номер заявки.');
}

$orderNumber = $argv[1];

$order = findOrder($orderNumber);
if (empty($order)) {
    exit('Заявка не найдена.');
}

$listeners = getOrderListeners($order);
foreach ($listeners as $listener) {
    sendNotification($order, $listener);
    print PHP_EOL;
}

/**
 * @param Order $order
 *
 * @param Listener $listener
 */
function sendNotification(Order $order, Listener $listener): void
{
    // Можно отправить сообщение с доступами по электронной почте или в sms.

    $message = <<<TAG
    Здравствуйте, $listener->name!
    Вам доступен курс {$order->course->title}
    Логин: $listener->username
    Пароль: $listener->password
TAG;

    print $message . PHP_EOL;
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

/**
 * @param $orderNumber
 *
 * @return Order|null
 */
function findOrder($orderNumber): ?Order
{
    $ordersApi = getApiClient()->orders();

    $criteria = $ordersApi->newCriteria();
    $criteria->number = $orderNumber;
    $orders = $ordersApi->findBy($criteria);

    return $orders[0] ?? null;
}

/**
 * @param Order $order
 *
 * @return array|Listener[]
 */
function getOrderListeners(Order $order): iterable
{
    $apiClient = getApiClient();
    return $apiClient->orders()->getOrderListeners($order);
}
