# Клиент для работы с API СДО Uchi.pro

## Установка

```bash
$ composer require uchi-pro/api-client
```

## Быстрый старт

```php
use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Orders\Criteria as OrdersCriteria;

$apiClient = ApiClient::create(Identity::createByAccessToken('BASE_URL', 'ACCESS_TOKEN'));

$currentUser = $apiClient->users()->getMe();
print 'Меня зовут: '.$currentUser->name.PHP_EOL;

$courses = $apiClient->courses()->findBy();
print 'Найдено курсов: '.count($courses).PHP_EOL;

$ordersCriteria = new OrdersCriteria();
$ordersCriteria->status = $ordersCriteria::STATUS_TRAINING;
$orders = $apiClient->orders()->findBy($ordersCriteria);
print 'Заявок в статусе обучения: '.count($orders).PHP_EOL;
```

Больше примеров в каталоге _examples_.
