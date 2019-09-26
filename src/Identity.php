<?php

namespace UchiPro;

class Identity
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $urlScheme;

    /**
     * @var string
     */
    public $urlHost;

    /**
     * @var string
     */
    public $login;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $accessToken;

    /**
     * @param string $url
     * @param string $login
     * @param string $password
     *
     * @return Identity
     */
    public static function createByLogin($url, $login, $password)
    {
        $identity = new self();

        $identity->url = $url;

        $identity->urlScheme = parse_url($identity->url, PHP_URL_SCHEME);

        $identity->urlHost = parse_url($identity->url, PHP_URL_HOST);

        $identity->login = $login;

        $identity->password = $password;

        return $identity;
    }

    /**
     * @param string $url
     * @param string $accessToken
     *
     * @return Identity
     */
    public static function createByAccessToken($url, $accessToken)
    {
        $identity = new self();

        $identity->url = $url;

        $identity->urlScheme = parse_url($identity->url, PHP_URL_SCHEME);

        $identity->urlHost = parse_url($identity->url, PHP_URL_HOST);

        $identity->accessToken = $accessToken;

        return $identity;
    }
}
