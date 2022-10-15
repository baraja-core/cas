<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\OrganisationMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganisationMemberRepository::class)]
#[ORM\Table(name: 'cas__organisation_member')]
class OrganisationMember
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: Organisation::class)]
	private Organisation $organisation;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private User $user;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $description = null;

	#[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
	private ?self $parent = null;

	/** @var Collection<self> */
	#[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
	private Collection $children;

	/** @var Collection<OrganisationMemberRole> */
	#[ORM\OneToMany(mappedBy: 'member', targetEntity: OrganisationMemberRole::class)]
	private Collection $roles;

	#[ORM\Column(type: 'string', length: 50, nullable: true)]
	private ?string $pathEnumeration = null;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $insertedDate;


	public function __construct(Organisation $organisation, User $user)
	{
		$this->organisation = $organisation;
		$this->user = $user;
		$this->children = new ArrayCollection;
		$this->roles = new ArrayCollection;
		$this->insertedDate = new \DateTimeImmutable;
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getOrganisation(): Organisation
	{
		return $this->organisation;
	}


	public function getUser(): User
	{
		return $this->user;
	}


	public function getDescription(): ?string
	{
		return $this->description;
	}


	public function getParent(): ?OrganisationMember
	{
		return $this->parent;
	}


	/**
	 * @return Collection<self>
	 */
	public function getChildren(): Collection
	{
		return $this->children;
	}


	/**
	 * @return Collection<OrganisationMemberRole>
	 */
	public function getRoles(): Collection
	{
		return $this->roles;
	}


	/**
	 * @return string[]
	 */
	public function getRoleCodes(): array
	{
		$return = [];
		foreach ($this->getRoles() as $role) {
			$return[] = $role->getRole()->getCode();
		}

		return $return;
	}


	public function isAdmin(): bool
	{
		return $this->containRole('admin');
	}


	public function containRole(string $role): bool
	{
		foreach ($this->getRoleCodes() as $roleItem) {
			if ($roleItem === $role) {
				return true;
			}
		}

		return false;
	}


	public function getPathEnumeration(): ?string
	{
		return $this->pathEnumeration;
	}


	public function getInsertedDate(): \DateTimeInterface
	{
		return $this->insertedDate;
	}
}
