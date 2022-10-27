<?php

declare(strict_types=1);

namespace Baraja\CAS\Repository;


use Baraja\CAS\Entity\Role;
use Doctrine\ORM\EntityRepository;

final class RoleRepository extends EntityRepository
{
	public function getById(int $id): Role
	{
		$role = $this->createQueryBuilder('role')
			->where('role.id = :id')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($role instanceof Role);

		return $role;
	}


	public function getByCode(string $code): Role
	{
		$role = $this->createQueryBuilder('role')
			->where('role.code :code')
			->setParameter('code', $code)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();
		assert($role instanceof Role);

		return $role;
	}
}
