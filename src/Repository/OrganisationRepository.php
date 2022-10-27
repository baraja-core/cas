<?php

declare(strict_types=1);

namespace Baraja\CAS\Repository;


use Baraja\CAS\Entity\Organisation;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class OrganisationRepository extends EntityRepository
{
	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getById(int $id): Organisation
	{
		$organisation = $this->createQueryBuilder('o')
			->where('o.id = :id')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($organisation instanceof Organisation);

		return $organisation;
	}


	/**
	 * @throws NoResultException|NonUniqueResultException
	 */
	public function getDefaultOrganisation(): Organisation
	{
		$organisation = $this->createQueryBuilder('o')
			->where('o.default = TRUE')
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($organisation instanceof Organisation);

		return $organisation;
	}
}
