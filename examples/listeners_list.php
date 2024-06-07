<?php

/**
 * Пример вывода списка слушателей.
 */

use UchiPro\{ApiClient, Identity, Users\Role};

require __DIR__.'/../vendor/autoload.php';

$apiClient = getApiClient();

$usersApi = $apiClient->users();
$criteria = $usersApi->newCriteria()->withRole(Role::createListener());
$listeners = $usersApi->findBy($criteria);

print "Список слушателей:\n";

$counter = 0;
foreach ($listeners as $listener) {
    $counter++;
    print "$counter. $listener->name $listener->email\n";
}

print "Всего слушателей: $counter\n";

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
