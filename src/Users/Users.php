<?php

namespace UchiPro\Users;

use UchiPro\ApiClient;
use UchiPro\Exception\BadResponseException;
use UchiPro\Vendors\Vendor;

class Users
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @return User
     */
    public function createUser()
    {
        return new User();
    }

    /**
     * @return Criteria
     */
    public function createCriteria()
    {
        return new Criteria();
    }

    /**
     * @return User
     *
     * @throws BadResponseException
     */
    public function getMe()
    {
        $responseData = $this->apiClient->request('/account/login');

        if (!isset($responseData['account'])) {
            throw new BadResponseException('Не удалось получить данные пользователя.');
        }

        return $this->parseUser($responseData['account']);
    }

    /**
     * @param $email
     *
     * @return User
     */
    public function findContractorByEmail($email)
    {
        $criteria = $this->createCriteria();
        $criteria->q = $email;
        $criteria->role = Role::createContractor();

        $users = $this->findBy($criteria);
        foreach ($users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @param $email
     *
     * @return User
     */
    public function findListenerByEmail($email)
    {
        $criteria = $this->createCriteria();
        $criteria->q = $email;
        $criteria->role = Role::createListener();

        $users = $this->findBy($criteria);
        foreach ($users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @param User $user
     * @param string|null $password
     *
     * @return User
     */
    public function saveUser(User $user, $password = null)
    {
        $formParams = [
          'role' => $user->role->id,
          'password' => $password,
          'username' => $user->email,
          'active' => 1,
          'vendor' => $user->vendor->id,
          'email' => $user->email,
          'title' => $user->name,
          'phone' => $user->phone,
        ];

        $userId = !empty($user->id) ? $user->id : 0;
        $uri = "/users/$userId/edit?role={$user->role->id}";
        $responseData = $this->apiClient->request($uri, $formParams);

        return $this->parseUser($responseData['user']);
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return array|User[]
     */
    public function findBy(Criteria $criteria = null)
    {
        $users = [];

        $uri = $this->buildUri($criteria);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('users', $responseData)) {
            throw new BadResponseException('Не удалось получить пользователей.');
        }

        if (is_array($responseData['users'])) {
            $users = $this->parseUsers($responseData['users']);
        }

        return $users;
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return string
     */
    private function buildUri(Criteria $criteria = null)
    {
        $uri = '/users';

        $uriQuery = [];
        if ($criteria) {
            if (!empty($criteria->q)) {
                $uriQuery['q'] = $criteria->q;
            }

            if (!empty($criteria->role)) {
                $uriQuery['role'] = $criteria->role->id;
            }

            if (!empty($criteria->vendor)) {
                $uriQuery['vendor'] = $criteria->vendor->id;
            }
        }

        if (!empty($uriQuery)) {
            $uri .= '?'.$this->apiClient::httpBuildQuery($uriQuery);
        }

        return $uri;
    }

    /**
     * @param array $list
     *
     * @return array|User[]
     */
    private function parseUsers(array $list)
    {
        $users = [];

        foreach ($list as $item) {
            $users[] = $this->parseUser($item);
        }

        return $users;
    }

    /**
     * @param array $item
     *
     * @return User
     */
    private function parseUser(array $item)
    {
        $role = new Role();
        $role->id = $item['role']['code'] ?? null;
        $role->title = $item['role']['title'] ?? null;

        $vendor = null;
        $isVendorNotEmpty = isset($item['vendor_uuid']) && ($item['vendor_uuid'] !== $this->apiClient::EMPTY_UUID_VALUE);
        if ($isVendorNotEmpty) {
            $vendor = new Vendor();
            $vendor->id = $item['vendor_uuid'] ?? null;
            $vendor->title = $item['vendor_title'] ?? null;
        }
        if (empty($vendor->id)) {
            $isDomainVendorNotEmpty = isset($item['vendor']['uuid']) && ($item['vendor']['uuid'] !== $this->apiClient::EMPTY_UUID_VALUE);
            if ($isDomainVendorNotEmpty) {
                $vendor->id = $item['vendor']['uuid'] ?? null;
                $vendor->title = $item['vendor']['title'] ?? null;
            }
        }

        $user = new User();
        $user->id = $item['uuid'] ?? null;
        $user->name = $item['title'] ?? null;
        $user->email = $item['email'] ?? null;
        $user->phone = $item['phone'] ?? null;
        $user->role = $role;
        $user->vendor = $vendor;

        return $user;
    }

    /**
     * @return int
     */
    public function getListenersNumber()
    {
        $responseData = $this->apiClient->request('/users?role=listener&_items_per_page=1');

        if (empty($responseData['pager'])) {
            throw new BadResponseException('Не удалось получить список слушателей.');
        }

        return (int)$responseData['pager']['total_items'];
    }

    /**
     * @param ApiClient $apiClient
     *
     * @return static
     */
    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
