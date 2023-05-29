<?php

/**
 * Пример построения дерева тегов.
 */

use UchiPro\{ApiClient, Identity};

require __DIR__.'/../vendor/autoload.php';

$tagsTree = fetchTagsTree();

print buildTagsTreeOutput($tagsTree);

function buildTagsTreeOutput(array $tagsTree, string $level = ''): string
{
    $output = '';

    if (!empty($tagsTree)) {
        $i = 0;
        foreach ($tagsTree as $tag) {
            $i++;

            $prefix = "$level$i.";
            $output .= "$prefix $tag->title" . PHP_EOL;
            if (!empty($tag->children)) {
                $output .= buildTagsTreeOutput($tag->children, $prefix);
            }
        }
    }

    return $output;
}

function getApiClient(): ApiClient
{
    $url = getenv('UCHIPRO_URL');
    $login = getenv('UCHIPRO_LOGIN');
    $password = getenv('UCHIPRO_PASSWORD');

    $identity = Identity::createByLogin($url, $login, $password);
    return ApiClient::create($identity);
}

/**
 * @return array
 */
function fetchTagsTree(): iterable
{
    $apiClient = getApiClient();

    return $apiClient->courses()->fetchTagsTree();
}
