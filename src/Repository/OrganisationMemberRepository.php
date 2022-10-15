<?php

declare(strict_types=1);

namespace Baraja\CAS\Repository;


use Baraja\CAS\Entity\Organisation;
use Baraja\CAS\Entity\OrganisationMember;
use Baraja\CAS\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class OrganisationMemberRepository extends EntityRepository
{
	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getByUser(User|int $user, ?Organisation $organisation = null): OrganisationMember
	{
		$selector = $this->createQueryBuilder('m')
			->select('m, user, role')
			->join('m.user', 'user')
			->leftJoin('m.roles', 'role')
			->where('m.user = :userId')
			->setParameter('userId', is_int($user) ? $user : $user->getId());

		if ($organisation !== null) {
			$selector->andWhere('m.organisation = :organisationId')
				->setParameter('organisationId', $organisation->getId());
		}

		$member = $selector
			->getQuery()
			->getSingleResult();
		assert($member instanceof OrganisationMember);

		return $member;
	}
}
