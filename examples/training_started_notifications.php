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

require '../vendor/autoload.php';

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
    sendNofication($order, $listener);
    print PHP_EOL;
}

/**
 * @param Order $order
 *
 * @param Listener $listener
 */
function sendNofication(Order $order, Listener $listener)
{
    // Можно отправить сообщение с доступами по электронной почте или в sms.

    $message = <<<TAG
    Здравствуйте, {$listener->name}!
    Вам доступен курс {$order->course->title}
    Логин: {$listener->username}
    Пароль: {$listener->password}
TAG;

    print $message . PHP_EOL;
}

/**
 * @return ApiClient
 */
function getApiClient()
{
    $url = getenv('UCHIPRO_URL');
    $login = getenv('UCHIPRO_LOGIN');
    $password = getenv('UCHIPRO_PASSWORD');

    $identity = Identity::createByLogin($url, $login, $password);
    $apiClient = ApiClient::create($identity);

    return $apiClient;
}

/**
 * @param $orderNumber
 *
 * @return Order|null
 */
function findOrder($orderNumber)
{
    $apiClient = getApiClient();

    $quary = new \UchiPro\Orders\Query();
    $quary->number = $orderNumber;
    $orders = $apiClient->orders()->findBy($quary);

    return isset($orders[0]) ? $orders[0] : null;
}

/**
 * @param Order $order
 *
 * @return array|Listener[]
 */
function getOrderListeners(Order $order)
{
    $apiClient = getApiClient();

    $listeners = $apiClient->orders()->getOrderListeners($order);

    return $listeners;
}
