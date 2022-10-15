<?php

declare(strict_types=1);

namespace Baraja\CAS\Repository;


use Baraja\CAS\Entity\User;
use Baraja\CAS\Entity\UserEmail;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class UserRepository extends EntityRepository
{
	/** @var array<int, User> */
	private static array $byIdCache = [];


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getByUsername(string $username): User
	{
		/** @phpstan-ignore-next-line */
		return $this->createQueryBuilder('user')
			->where('user.username = :username')
			->setParameter('username', $username)
			->getQuery()
			->setMaxResults(1)
			->getSingleResult();
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getUserById(int $id): User
	{
		$find = function (int $id): User
		{
			$entity = $this->createQueryBuilder('user')
				->where('user.id = :id')
				->setParameter('id', $id)
				->setMaxResults(1)
				->getQuery()
				->getSingleResult();
			assert($entity instanceof User);

			return $entity;
		};

		return self::$byIdCache[$id] ?? self::$byIdCache[$id] = $find($id);
	}


	public function userExist(string $email): bool
	{
		try {
			$this->createQueryBuilder('user')
				->select('PARTIAL user.{id}')
				->where('user.username = :username')
				->setParameter('username', $email)
				->setMaxResults(1)
				->getQuery()
				->getSingleResult();

			return true;
		} catch (NoResultException|NonUniqueResultException) {
		}

		return false;
	}


	public function getCountUsers(): int
	{
		try {
			$return = $this->createQueryBuilder('user')
				->select('COUNT(user.id)')
				->getQuery()
				->getSingleScalarResult();
			if (is_numeric($return)) {
				return (int) $return;
			}
		} catch (\Throwable) {
			// Silence is golden.
		}

		throw new \LogicException('Can not count users.');
	}


	public function createUser(
		string $email,
		?string $username = null,
		?string $password = null,
		?string $phone = null,
	): User {
		if ($this->userExist($email)) {
			throw new \InvalidArgumentException(sprintf('User "%s" already exist.', $email));
		}

		$user = new User(
			username: $username ?? $email,
			password: $password ?? '',
		);
		$emailEntity = new UserEmail($user, $email);
		$user->setEmail($emailEntity);
		if ($phone !== null) {
			$user->setPhone($phone);
		}

		$this->getEntityManager()->persist($user);
		$this->getEntityManager()->persist($emailEntity);
		$this->getEntityManager()->flush();

		return $user;
	}
}
