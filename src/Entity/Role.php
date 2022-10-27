<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'cas__role')]
class Role
{
	public const RoleAdmin = 'ADMIN';

	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\Column(type: 'string', length: 64, unique: true)]
	private string $code;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $description = null;

	#[ORM\ManyToOne(targetEntity: RoleGroup::class, inversedBy: 'roles')]
	private ?RoleGroup $group = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private ?User $guarantor = null;

	#[ORM\Column(type: 'boolean')]
	private bool $production = false;


	public function __construct(string $code, ?string $description)
	{
		$this->code = $code;
		$this->description = $description;
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getCode(): string
	{
		return $this->code;
	}


	public function getDescription(): ?string
	{
		return $this->description;
	}


	public function getGroup(): ?RoleGroup
	{
		return $this->group;
	}


	public function getGuarantor(): ?User
	{
		return $this->guarantor;
	}


	public function isProduction(): bool
	{
		return $this->production;
	}
}
