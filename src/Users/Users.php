<?php

declare(strict_types=1);

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

    public function createUser(): User
    {
        return new User();
    }

    public function createCriteria(): Criteria
    {
        return new Criteria();
    }

    /**
     * @return User
     *
     * @throws BadResponseException
     */
    public function getMe(): User
    {
        $responseData = $this->apiClient->request('/account/login');

        if (!isset($responseData['account'])) {
            throw new BadResponseException('Не удалось получить данные пользователя.');
        }

        return $this->parseUser($responseData['account']);
    }

    /**
     * @param string $id
     *
     * @return User|null
     */
    public function findById(string $id): ?User
    {
        $responseData = $this->apiClient->request("/users/$id");

        if (empty($responseData['user']['uuid'])) {
            return null;
        }

        return $this->parseUser($responseData['user']);
    }

    /**
     * @param User $contractor
     *
     * @return User|null
     */
    public function fetchContractorDefaultListener(User $contractor): ?User
    {
        $responseData = $this->apiClient->request("/users/$contractor->id/settings");

        if (empty($responseData['settings']['default_listener'])) {
            return null;
        }

        return $this->findById((string)$responseData['settings']['default_listener']);
    }

    public function findContractorByEmail(string $email): ?User
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

    public function findListenerByEmail($email): ?User
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
    public function saveUser(User $user, string $password = null): User
    {
        $formParams = [
          'role' => $user->role->id,
          'password' => $password,
          'username' => $user->username ?? $user->email,
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

    public function deleteUser(User $user): User
    {
        $formParams = [
            'uuid' => $user->id,
        ];

        $uri = "/users/$user->id/delete";
        $responseData = $this->apiClient->request($uri, $formParams);

        return $this->parseUser($responseData['user']);
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return array|User[]
     */
    public function findBy(Criteria $criteria = null): array
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
    private function buildUri(Criteria $criteria = null): string
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
    private function parseUsers(array $list): array
    {
        $users = [];

        foreach ($list as $item) {
            $users[] = $this->parseUser($item);
        }

        return $users;
    }

    private function parseUser(array $data): User
    {
        $role = new Role();
        $role->id = $data['role']['code'] ?? null;
        $role->title = $data['role']['title'] ?? null;

        $vendor = null;
        $isVendorNotEmpty = isset($data['vendor_uuid']) && ($data['vendor_uuid'] !== $this->apiClient::EMPTY_UUID_VALUE);
        if ($isVendorNotEmpty) {
            $vendor = new Vendor();
            $vendor->id = $data['vendor_uuid'] ?? null;
            $vendor->title = $data['vendor_title'] ?? null;
        }
        if (empty($vendor->id)) {
            $isDomainVendorNotEmpty = isset($data['vendor']['uuid']) && ($data['vendor']['uuid'] !== $this->apiClient::EMPTY_UUID_VALUE);
            if ($isDomainVendorNotEmpty) {
                $vendor->id = $data['vendor']['uuid'] ?? null;
                $vendor->title = $data['vendor']['title'] ?? null;
            }
        }

        $user = new User();
        $user->id = $data['uuid'] ?? null;
        $user->username = $data['username'] ?? null;
        $user->name = $data['title'] ?? null;
        $user->email = $data['email'] ?? null;
        $user->phone = $data['phone'] ?? null;
        $user->isDeleted = !empty($data['is_deleted']);
        $user->role = $role;
        $user->vendor = $vendor;

        return $user;
    }

    public function getListenersNumber(): int
    {
        $responseData = $this->apiClient->request('/users?role=listener&_items_per_page=1');

        if (empty($responseData['pager'])) {
            throw new BadResponseException('Не удалось получить список слушателей.');
        }

        return (int)$responseData['pager']['total_items'];
    }

    public static function create(ApiClient $apiClient): Users
    {
        return new static($apiClient);
    }
}
