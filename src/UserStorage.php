<?php

declare(strict_types=1);

namespace Baraja\CAS;


use Baraja\CAS\Entity\Organisation;
use Baraja\CAS\Entity\OrganisationMember;
use Baraja\CAS\Entity\User as UserEntity;
use Baraja\CAS\Entity\UserLoginIdentity;
use Baraja\CAS\Identity\IdentityStorageInterface;
use Baraja\CAS\Identity\SessionIdentityStorage;
use Baraja\CAS\Repository\OrganisationMemberRepository;
use Baraja\CAS\Repository\OrganisationRepository;
use Baraja\CAS\Repository\UserLoginIdentityRepository;
use Baraja\CAS\Repository\UserRepository;
use Baraja\CAS\Service\UserMetaManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UserStorage
{
	private UserRepository $userRepository;

	private UserLoginIdentityRepository $userLoginIdentityRepository;

	private OrganisationMemberRepository $organisationMemberRepository;

	private OrganisationRepository $organisationRepository;

	private IdentityStorageInterface $identityStorage;


	public function __construct(
		private EntityManagerInterface $entityManager,
		private UserMetaManager $metaManager,
		?IdentityStorageInterface $identityStorage = null,
	) {
		$userRepository = $entityManager->getRepository(UserEntity::class);
		$userLoginIdentityRepository = $entityManager->getRepository(UserLoginIdentity::class);
		$organisationMemberRepository = $entityManager->getRepository(OrganisationMember::class);
		$organisationRepository = $entityManager->getRepository(Organisation::class);
		assert($userRepository instanceof UserRepository);
		assert($userLoginIdentityRepository instanceof UserLoginIdentityRepository);
		assert($organisationMemberRepository instanceof OrganisationMemberRepository);
		assert($organisationRepository instanceof OrganisationRepository);
		$this->userRepository = $userRepository;
		$this->userLoginIdentityRepository = $userLoginIdentityRepository;
		$this->organisationMemberRepository = $organisationMemberRepository;
		$this->organisationRepository = $organisationRepository;
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
	public function getIdentityByUser(UserEntity $user): UserLoginIdentity
	{
		return $this->userLoginIdentityRepository->getIdentityByUser($user);
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getMemberByUser(UserEntity|int $user, ?Organisation $organisation = null): OrganisationMember
	{
		if ($organisation === null) {
			try {
				$identity = $this->getIdentity();
				$organisation = $identity !== null
					? $this->organisationRepository->getById($identity->getOrganisationId())
					: $this->organisationRepository->getDefaultOrganisation();
			} catch (\Throwable) {
				// Silence is golden.
			}
		}
		if ($organisation === null) {
			throw new \InvalidArgumentException('Organisation has not detected.');
		}

		return $this->organisationMemberRepository->getByUser($user, $organisation);
	}


	public function saveAuthentication(UserLoginIdentity $loginIdentity): void
	{
		$member = $loginIdentity->getMember();
		$user = $member->getUser();

		$identity = new UserIdentity(
			id: $loginIdentity->getId(),
			memberId: $loginIdentity->getMember()->getId(),
			userId: $loginIdentity->getMember()->getUser()->getId(),
			identityId: $loginIdentity->getIdentityId(),
			organisationId: $member->getOrganisation()->getId(),
			roles: $member->getRoleCodes(),
			name: $user->getName(),
			avatarUrl: $user->getAvatarUrl(),
		);

		$this->identityStorage->saveIdentity($identity, $loginIdentity->getExpirationDate());
	}


	public function disableMember(OrganisationMember $member, string $reason): void
	{
		$currentIdentity = $this->getIdentity();
		if ($currentIdentity === null) {
			throw new \InvalidArgumentException('User must be logged in.');
		}

		$currentMember = $this->getMemberByUser($currentIdentity->getId());
		if ($currentMember->isAdmin() === false) {
			throw new \InvalidArgumentException('You are not a admin.');
		}
		if ($currentMember->getId() === $member->getId()) {
			throw new \InvalidArgumentException('Your account can not be blocked.');
		}

		$reason = trim($reason);
		if ($reason === '') {
			throw new \InvalidArgumentException('Block reason can not be empty.');
		}

		$id = $member->getId();
		$member->getUser()->setActive(false);
		foreach ($member->getRoles() as $role) {
			$this->entityManager->remove($role);
		}
		$this->entityManager->flush();

		$this->metaManager->set($id, 'blocked', 'true');
		$this->metaManager->set($id, 'block-reason', $reason);
		$this->metaManager->set($id, 'block-admin', (string) $currentMember->getId());
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
