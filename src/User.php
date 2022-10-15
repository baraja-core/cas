<?php

declare(strict_types=1);

namespace Baraja\CAS;


use Baraja\CAS\Entity\User as UserEntity;
use Baraja\CAS\Entity\UserLoginIdentity;
use Baraja\CAS\Service\UserMetaManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Základní aplikační rozhraní pro obsluhu uživatelských účtů.
 * Ověření přihlášení, získání identity, přihlášení, odhlášení a manipulace s účty.
 */
class User
{
	public function __construct(
		private UserStorage $userStorage,
		private Authenticator $authenticator,
		private UserMetaManager $metaManager,
	) {
	}


	public function isLoggedIn(): bool
	{
		return $this->userStorage->getIdentity() !== null;
	}


	public function getId(): int
	{
		$id = $this->userStorage->getIdentity()?->getId();
		if ($id === null) {
			throw new \LogicException('User is not logged in.');
		}

		return $id;
	}


	public function getIdentity(?UserLoginIdentity $identity = null): ?UserIdentity
	{
		return $this->userStorage->getIdentity();
	}


	public function getIdentityEntity(): ?UserEntity
	{
		if ($this->isLoggedIn()) {
			try {
				return $this->userStorage->getUserById($this->getId());
			} catch (NoResultException | NonUniqueResultException $e) {
				// User does not exist.
			}
		}

		return null;
	}


	public function logout(?string $identityId = null): void
	{
		$this->userStorage->clearAuthentication();
	}


	public function isOnline(int $userId): bool
	{
		$lastActivity = $this->metaManager->get($userId, 'last-activity');
		if ($lastActivity === null) {
			return false;
		}

		return (new \DateTime($lastActivity))->getTimestamp() + 30 >= time();
	}


	public function isAdmin(): bool
	{
		$identity = $this->getIdentity();
		if ($identity !== null) {
			return in_array('admin', $identity->getRoles(), true);
		}

		return false;
	}


	public function createUser(
		string $email,
		?string $username = null,
		?string $password = null,
		?string $phone = null,
	): UserEntity
	{
		return $this->getUserStorage()->getUserRepository()->createUser($email, $username, $password, $phone);
	}


	public function getAuthenticator(): Authenticator
	{
		return $this->authenticator;
	}


	public function getUserStorage(): UserStorage
	{
		return $this->userStorage;
	}
}
