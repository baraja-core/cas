<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\OrganisationMemberRoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganisationMemberRoleRepository::class)]
#[ORM\Table(name: 'cas__organisation_member_role')]
class OrganisationMemberRole
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: OrganisationMember::class)]
	private OrganisationMember $member;

	#[ORM\ManyToOne(targetEntity: Role::class)]
	private Role $role;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $validFrom;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $validTo = null;


	public function __construct(OrganisationMember $member, Role $role)
	{
		$this->member = $member;
		$this->role = $role;
		$this->validFrom = new \DateTimeImmutable;
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


	public function getValidFrom(): \DateTimeInterface
	{
		return $this->validFrom;
	}


	public function getValidTo(): ?\DateTimeInterface
	{
		return $this->validTo;
	}


	public function setValidTo(?\DateTimeInterface $validTo): void
	{
		$this->validTo = $validTo;
	}
}
