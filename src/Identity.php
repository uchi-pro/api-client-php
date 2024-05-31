<?php

declare(strict_types=1);

namespace UchiPro;

class Identity
{
    public ?string $url = null;

    public ?string $urlScheme = null;

    public ?string $urlHost = null;

    public ?string $login = null;

    public ?string $password = null;

    public ?string $accessToken = null;

    public static function createByLogin(string $url, string $login, string $password): Identity
    {
        $identity = new self();

        $identity->url = $url;

        $identity->urlScheme = parse_url($identity->url, PHP_URL_SCHEME);

        $identity->urlHost = parse_url($identity->url, PHP_URL_HOST);

        $identity->login = $login;

        $identity->password = $password;

        return $identity;
    }

    public static function createByAccessToken(string $url, string $accessToken): Identity
    {
        $identity = new self();

        $identity->url = $url;

        $identity->urlScheme = parse_url($identity->url, PHP_URL_SCHEME);

        $identity->urlHost = parse_url($identity->url, PHP_URL_HOST);

        $identity->accessToken = $accessToken;

        return $identity;
    }
}
