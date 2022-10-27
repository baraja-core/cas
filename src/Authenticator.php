<?php

declare(strict_types=1);

namespace Baraja\CAS;


use Baraja\CAS\Entity\Organisation;
use Baraja\CAS\Entity\UserLoginAttempt;
use Baraja\CAS\Entity\UserLoginIdentity;
use Baraja\CAS\Repository\OrganisationRepository;
use Baraja\CAS\Repository\UserLoginAttemptRepository;
use Baraja\CAS\Service\UserMetaManager;
use Baraja\DynamicConfiguration\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Authenticator
{
	/** Exception error code */
	public const
		IdentityNotFound = 1,
		InvalidCredential = 2,
		Failure = 3,
		NotApproved = 4;

	private OrganisationRepository $organisationRepository;


	public function __construct(
		private EntityManagerInterface $entityManager,
		private UserStorage $userStorage,
		private UserMetaManager $metaManager,
		private ?Configuration $configuration = null,
	) {
		$organisationRepository = $entityManager->getRepository(Organisation::class);
		assert($organisationRepository instanceof OrganisationRepository);
		$this->organisationRepository = $organisationRepository;
	}


	/**
	 * @throws AuthenticationException
	 */
	public function authentication(
		string $username,
		string $password,
		string|bool $remember = '14 days',
		?Organisation $organisation = null,
	): UserLoginIdentity
	{
		if (is_bool($remember)) {
			$expiration = $remember ? '14 days' : '15 minutes';
		} else {
			$expiration = $remember;
		}
		$this->processSecuritySleep();
		try {
			$organisation ??= $this->organisationRepository->getDefaultOrganisation();
		} catch (NoResultException | NonUniqueResultException) {
			throw new AuthenticationException('Organisation does not exist.', self::Failure);
		}
		if ($this->isLoginFirewallBlocked($username) === true) {
			throw new AuthenticationException('Too many failed login attempts.', self::NotApproved);
		}
		try {
			$username = CasHelper::formatUsername($username);
		} catch (\InvalidArgumentException $e) {
			throw new AuthenticationException($e->getMessage(), self::InvalidCredential);
		}

		$password = trim($password);
		if ($username === '' || $password === '') {
			throw new AuthenticationException('Username or password is empty.', self::InvalidCredential);
		}

		try {
			$user = $this->userStorage->getByUsername($username);
		} catch (NoResultException | NonUniqueResultException) {
			throw new AuthenticationException('Username or password is incorrect.', self::InvalidCredential);
		}
		try {
			$member = $this->userStorage->getMemberByUser($user, $organisation);
		} catch (NoResultException | NonUniqueResultException) {
			throw new AuthenticationException(
				sprintf('User profile is not associated with organisation "%s".', $organisation->getName()),
				self::NotApproved,
			);
		}

		if ($this->metaManager->get($user->getId(), 'blocked') === 'true') {
			throw new AuthenticationException(
				$this->metaManager->get($user->getId(), 'block-reason') ?? '',
				self::NotApproved,
			);
		}

		$loginAttempt = new UserLoginAttempt($user, $username);
		$this->entityManager->persist($loginAttempt);
		$this->entityManager->flush();

		$hash = $user->getPassword();
		if ($hash === '---empty-password---') {
			throw new AuthenticationException(
				sprintf(
					'User password is empty or account is locked, please contact your administrator. Username "%s" given.',
					$username,
				),
				self::Failure,
			);
		}
		if ($user->passwordVerify($password) === false) {
			throw new AuthenticationException(sprintf('The password is incorrect. Username "%s" given.', $username));
		}
		if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
			try {
				$user->setPassword($password);
			} catch (\InvalidArgumentException) {
				// Silence is golden.
			}
		}

		$loginIdentity = new UserLoginIdentity($member, new \DateTimeImmutable('now + ' . $expiration));
		$this->entityManager->persist($loginIdentity);
		$this->entityManager->flush();

		$this->userStorage->saveAuthentication($loginIdentity);

		return $loginIdentity;
	}


	public function isLoginFirewallBlocked(string $username, ?string $ip = null): bool
	{
		if (PHP_SAPI === 'cli' || $this->configuration === null) {
			return false;
		}
		$blockCountKey = 'user-login-attempts-block-count';
		$blockIntervalKey = 'user-login-attempts-block-interval';

		$configuration = $this->configuration->getSection('core');
		$blockCount = $configuration->get($blockCountKey);
		if ($blockCount === null) {
			$blockCount = 10;
			$configuration->save($blockCountKey, (string) $blockCount);
		}
		$blockInterval = $configuration->get($blockIntervalKey);
		if ($blockInterval === null) {
			$blockInterval = '20 minutes';
			$configuration->save($blockIntervalKey, $blockInterval);
		}

		$attemptRepository = $this->entityManager->getRepository(UserLoginAttempt::class);
		assert($attemptRepository instanceof UserLoginAttemptRepository);
		$attempts = $attemptRepository->getUsedAttempts($username, $blockInterval, $ip, (int) $blockCount);

		return count($attempts) >= (int) $blockCount;
	}


	/**
	 * Sets the application to sleep for a randomly long period of time and prevents a possible attack.
	 * If all login requests were handled immediately, an attacker could guess from the response time which way
	 * the code was processed and what login credentials he needs to guess. If the authentication time is random,
	 * the success of the attempt cannot be inferred from it.
	 */
	private function processSecuritySleep(): void
	{
		// 1 ms = 1 000 microseconds
		usleep(random_int(1, 300) * 1000);
	}
}
