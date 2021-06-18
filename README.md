# Клиент для работы с API СДО Uchi.pro

## Установка

```bash
$ composer require uchi-pro/api-client
```

## Быстрый старт

```php
use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Orders\Status;

$apiClient = ApiClient::create(Identity::createByLogin('UCHIPRO_URL', 'UCHIPRO_LOGIN', 'UCHIPRO_PASSWORD'));

$currentUser = $apiClient->users()->getMe();
print 'Меня зовут: '.$currentUser->name.PHP_EOL;

$courses = $apiClient->courses()->findBy();
print 'Найдено курсов: '.count($courses).PHP_EOL;

$ordersApi = $apiClient->orders();
$ordersCriteria = $ordersApi->createCriteria();
$ordersCriteria->status = Status::createTraining();
$orders = $ordersApi->findBy($ordersCriteria);
print 'Заявок в статусе обучения: '.count($orders).PHP_EOL;
```

Больше примеров в каталоге _examples_.
