<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\UserRoleRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRoleRequestRepository::class)]
#[ORM\Table(name: 'cas__organisation_member_role_request')]
class OrganisationMemberRoleRequest
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: OrganisationMember::class)]
	private OrganisationMember $member;

	#[ORM\ManyToOne(targetEntity: Role::class)]
	private Role $role;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $reason;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $insertedDate;


	public function __construct(OrganisationMember $member, Role $role, ?string $reason = null)
	{
		$this->member = $member;
		$this->role = $role;
		$this->reason = $reason;
		$this->insertedDate = new \DateTimeImmutable;
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getMember(): OrganisationMember
	{
		return $this->member;
	}


	public function getRole(): Role
	{
		return $this->role;
	}


	public function getReason(): ?string
	{
		return $this->reason;
	}


	public function getInsertedDate(): \DateTimeInterface
	{
		return $this->insertedDate;
	}
}
