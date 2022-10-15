<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\RoleGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleGroupRepository::class)]
#[ORM\Table(name: 'cas__role_group')]
class RoleGroup
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\Column(type: 'string', length: 64, unique: true)]
	private string $name;

	/** @var Collection<Role> */
	#[ORM\OneToMany(mappedBy: 'group', targetEntity: Role::class)]
	private Collection $roles;


	public function __construct(string $name)
	{
		$this->name = $name;
		$this->roles = new ArrayCollection;
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getName(): string
	{
		return $this->name;
	}


	/**
	 * @return Collection<Role>
	 */
	public function getRoles(): Collection
	{
		return $this->roles;
	}
}
