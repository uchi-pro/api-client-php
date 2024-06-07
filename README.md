# Клиент для работы с API СДО Uchi.pro

Полное описание API можно посмотреть [здесь](https://demo.uchi.pro/docs/api.html "Описание HTTP-API СДО UCHI.PRO v4")

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
$ordersCriteria = $ordersApi->newCriteria();
$ordersCriteria->status = Status::createTraining();
$orders = $ordersApi->findBy($ordersCriteria);
print 'Заявок в статусе обучения: '.count($orders).PHP_EOL;
```

Больше примеров в каталоге _examples_.
