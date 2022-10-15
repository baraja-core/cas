<?php

declare(strict_types=1);

namespace Baraja\CAS;


use Baraja\CAS\Entity\Organisation;
use Baraja\CAS\Entity\OrganisationMember;
use Baraja\CAS\Entity\User;
use Baraja\CAS\Entity\User as UserEntity;
use Baraja\CAS\Entity\UserLoginIdentity;
use Baraja\CAS\Identity\IdentityStorageInterface;
use Baraja\CAS\Identity\SessionIdentityStorage;
use Baraja\CAS\Repository\OrganisationMemberRepository;
use Baraja\CAS\Repository\UserLoginIdentityRepository;
use Baraja\CAS\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UserStorage
{
	private UserRepository $userRepository;

	private UserLoginIdentityRepository $userLoginIdentityRepository;

	private OrganisationMemberRepository $organisationMemberRepository;

	private IdentityStorageInterface $identityStorage;


	public function __construct(
		EntityManagerInterface $entityManager,
		?IdentityStorageInterface $identityStorage = null,
	) {
		$userRepository = $entityManager->getRepository(UserEntity::class);
		$userLoginIdentityRepository = $entityManager->getRepository(UserLoginIdentity::class);
		$organisationMemberRepository = $entityManager->getRepository(OrganisationMember::class);
		assert($userRepository instanceof UserRepository);
		assert($userLoginIdentityRepository instanceof UserLoginIdentityRepository);
		assert($organisationMemberRepository instanceof OrganisationMemberRepository);
		$this->userRepository = $userRepository;
		$this->userLoginIdentityRepository = $userLoginIdentityRepository;
		$this->organisationMemberRepository = $organisationMemberRepository;
		$this->identityStorage = $identityStorage ?? new SessionIdentityStorage;
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getByUsername(string $username): UserEntity
	{
		return $this->userRepository->getByUsername($username);
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getUserById(int $id): UserEntity
	{
		return $this->userRepository->getUserById($id);
	}


	public function getIdentity(): ?UserIdentity
	{
		return $this->identityStorage->getIdentity();
	}


	public function userExist(string $email): bool
	{
		return $this->userRepository->userExist($email);
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getIdentityByUser(User $user): UserLoginIdentity
	{
		return $this->userLoginIdentityRepository->getIdentityByUser($user);
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getMemberByUser(User $user, Organisation $organisation): OrganisationMember
	{
		return $this->organisationMemberRepository->getByUser($user, $organisation);
	}


	public function saveAuthentication(UserLoginIdentity $loginIdentity): void
	{
		$member = $loginIdentity->getMember();
		$user = $member->getUser();

		$identity = new UserIdentity(
			id: $loginIdentity->getId(),
			identityId: $loginIdentity->getIdentityId(),
			roles: $member->getRoleCodes(),
			name: $user->getName(),
			avatarUrl: $user->getAvatarUrl(),
		);

		$this->identityStorage->saveIdentity($identity, $loginIdentity->getExpirationDate());
	}


	public function clearAuthentication(): void
	{
		$this->identityStorage->removeIdentity();
	}


	public function getUserRepository(): UserRepository
	{
		return $this->userRepository;
	}
}
