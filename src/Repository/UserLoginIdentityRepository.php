<?php

declare(strict_types=1);

namespace Baraja\CAS\Repository;


use Baraja\CAS\Entity\User;
use Baraja\CAS\Entity\UserLoginIdentity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class UserLoginIdentityRepository extends EntityRepository
{
	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getIdentityByUser(User $user): UserLoginIdentity
	{
		$identity = $this->createQueryBuilder('identity')
			->where('identity.user = :userId')
			->andWhere('identity.expirationDate <= :now')
			->setParameter('userId', $user->getId())
			->setParameter('now', date('Y-m-d H:i:s'))
			->orderBy('identity.insertedDate', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($identity instanceof UserLoginIdentity);

		return $identity;
	}
}
