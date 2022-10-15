<?php

declare(strict_types=1);

namespace Baraja\CAS\Service;


use Baraja\CAS\Entity\User;
use Baraja\CAS\Entity\UserMeta;
use Baraja\CAS\Repository\UserMetaRepository;
use Baraja\CAS\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class UserMetaManager
{
	/** @var array<string, UserMeta> */
	private static array $cache = [];

	private UserRepository $userRepository;


	public function __construct(
		private EntityManagerInterface $entityManager,
	) {
		$userRepository = $this->entityManager->getRepository(User::class);
		assert($userRepository instanceof UserRepository);
		$this->userRepository = $userRepository;
	}


	public function loadAll(int $userId): self
	{
		$userMetaRepository = $this->entityManager->getRepository(UserMeta::class);
		assert($userMetaRepository instanceof UserMetaRepository);

		foreach ($userMetaRepository->loadAll($userId) as $meta) {
			$cacheKey = $this->getCacheKey($userId, $meta->getKey());
			self::$cache[$cacheKey] = $meta;
		}

		return $this;
	}


	public function get(int $userId, string $key): ?string
	{
		$cacheKey = $this->getCacheKey($userId, $key);
		if (isset(self::$cache[$cacheKey])) {
			return self::$cache[$cacheKey]->getValue();
		}
		try {
			$userMetaRepository = $this->entityManager->getRepository(UserMeta::class);
			assert($userMetaRepository instanceof UserMetaRepository);
			$meta = $userMetaRepository->load($userId, $key);
			self::$cache[$cacheKey] = $meta;

			return $meta->getValue();
		} catch (NoResultException | NonUniqueResultException) {
			// Silence is golden.
		}

		return null;
	}


	public function set(int $userId, string $key, ?string $value): self
	{
		try {
			$user = $this->userRepository->getUserById($userId);
		} catch (NoResultException | NonUniqueResultException $e) {
			throw new \InvalidArgumentException(sprintf('User "%s" does not exist.', $userId), 500, $e);
		}
		$cacheKey = $this->getCacheKey($user->getId(), $key);
		try {
			$userMetaRepository = $this->entityManager->getRepository(UserMeta::class);
			assert($userMetaRepository instanceof UserMetaRepository);
			$meta = self::$cache[$cacheKey] ?? $userMetaRepository->load($user->getId(), $key);
			assert($meta instanceof UserMeta);
		} catch (NoResultException | NonUniqueResultException) {
			if ($value === null) {
				return $this;
			}
			$meta = new UserMeta($user, $key, $value);
			$this->entityManager->persist($meta);
		}
		if ($value === null) {
			$this->entityManager->remove($meta);
			unset(self::$cache[$cacheKey]);
		} else {
			$meta->setValue($value);
			self::$cache[$cacheKey] = $meta;
		}
		$this->entityManager->flush();

		return $this;
	}


	private function getCacheKey(int $userId, string $key): string
	{
		return $userId . '__' . $key;
	}
}
