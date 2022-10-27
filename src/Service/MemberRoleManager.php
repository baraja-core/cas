<?php

declare(strict_types=1);

namespace Baraja\CAS\Service;


use Baraja\CAS\Entity\OrganisationMember;
use Baraja\CAS\Entity\OrganisationMemberRole;
use Baraja\CAS\Entity\Role;
use Baraja\CAS\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;

final class MemberRoleManager
{
	private RoleRepository $roleRepository;


	public function __construct(
		private EntityManagerInterface $entityManager,
	) {
		$roleRepository = $entityManager->getRepository(Role::class);
		assert($roleRepository instanceof RoleRepository);
		$this->roleRepository = $roleRepository;
	}


	public function addRole(OrganisationMember $member, Role|string $role): void
	{
		$role = is_string($role) ? $this->roleRepository->getByCode($role) : $role;
		$relation = new OrganisationMemberRole($member, $role);
		$this->entityManager->persist($relation);
		$member->addRole($relation);
		$this->entityManager->flush();
	}
}
