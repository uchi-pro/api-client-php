<?php

declare(strict_types=1);

namespace UchiPro\Users;

use UchiPro\ApiClient;
use UchiPro\Collection;
use UchiPro\Exception\BadResponseException;
use UchiPro\Vendors\Vendor;

final readonly class UsersApi
{
    private function __construct(private ApiClient $apiClient) {}

    public function newUser(?string $id = null, ?string $name = null): User
    {
        return User::create($id, $name);
    }

    public function newAdministrator(?string $id = null, ?string $name = null): User
    {
        return User::createAdministrator($id, $name);
    }

    public function newContractor(?string $id = null, ?string $name = null): User
    {
        return User::createContractor($id, $name);
    }

    public function newListener(?string $id = null, ?string $name = null): User
    {
        return User::createListener($id, $name);
    }

    public function newCriteria(): Criteria
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
    public function getContractorDefaultListener(User $contractor): ?User
    {
        $responseData = $this->apiClient->request("/users/$contractor->id/settings");

        if (empty($responseData['settings']['default_listener'])) {
            return null;
        }

        return $this->findById((string)$responseData['settings']['default_listener']);
    }

    public function findContractorByEmail(string $email): ?User
    {
        $criteria = $this->newCriteria();
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
        $criteria = $this->newCriteria()
            ->withQ($email)
            ->withRole(Role::createListener())
        ;

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
     * @param array $additionalParams Дополнительные параметры в виде ключ => значение, которые будут переданы в POST-запросе.
     *
     * @return User
     */
    public function saveUser(User $user, string $password = null, array $additionalParams = []): User
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

        if (!empty($user->parent)) {
            $formParams['parent'] = $user->parent->id;
        }

        foreach ($additionalParams as $key => $value) {
            $formParams[$key] = $value;
        }

        $userId = $user->id ?? 0;
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
     * @return User[]|Collection
     */
    public function findBy(Criteria $criteria = null): iterable|Collection
    {
        $users = new Collection();

        $uri = $this->buildUri($criteria);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('users', $responseData)) {
            throw new BadResponseException('Не удалось получить пользователей.');
        }

        if (is_array($responseData['users'])) {
            $users = $this->parseUsers($responseData['users']);
        }

        if (isset($responseData['pager'])) {
            $users->setPager($responseData['pager']);
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
     * @return User[]|Collection
     */
    private function parseUsers(array $list): iterable|Collection
    {
        $users = new Collection();

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
        $user->isActive = !empty($data['is_active']);
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

    public static function create(ApiClient $apiClient): self
    {
        return new self($apiClient);
    }
}
