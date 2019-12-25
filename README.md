# Клиент для работы с API СДО Uchi.pro

## Установка

```bash
$ composer require uchi-pro/api-client
```

## Быстрый старт

```php
use UchiPro\ApiClient;
use UchiPro\Identity;

$apiClient = ApiClient::create(Identity::createByAccessToken('BASE_URL', 'ACCESS_TOKEN'));

// Получение данных пользоявателя, из под которого происходит работа с СДО.
$me = $apiClient->users()->getMe();
print_r($me);

// Получение списка всех курсов.
$courses = $apiClient->courses()->findBy();
print_r($courses);
```
