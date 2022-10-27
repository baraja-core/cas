<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\OrganisationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
#[ORM\Table(name: 'cas__organisation')]
class Organisation
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\Column(type: 'boolean')]
	private bool $default = false;

	#[ORM\Column(type: 'string', length: 128, unique: true)]
	private string $name;

	#[ORM\Column(type: 'string', length: 128, unique: true)]
	private string $slug;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $description = null;

	#[ORM\ManyToOne(targetEntity: OrganisationMember::class)]
	private OrganisationMember $supportPerson;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $companyNumber = null;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $companyVatNumber = null;


	public function __construct(string $name, string $slug)
	{
		$this->setName($name);
		$this->setSlug($slug);
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function isDefault(): bool
	{
		return $this->default;
	}


	public function setDefault(bool $default): void
	{
		$this->default = $default;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function setName(string $name): void
	{
		$this->name = $name;
	}


	public function getSlug(): string
	{
		return $this->slug;
	}


	public function setSlug(string $slug): void
	{
		$this->slug = $slug;
	}


	public function getDescription(): ?string
	{
		return $this->description;
	}


	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}


	public function getSupportPerson(): OrganisationMember
	{
		return $this->supportPerson;
	}


	public function setSupportPerson(OrganisationMember $supportPerson): void
	{
		if ($supportPerson->getOrganisation()->getId() !== $this->getId()) {
			throw new \LogicException('Support person must be member of this organisation.');
		}
		$this->supportPerson = $supportPerson;
	}


	public function getCompanyNumber(): ?string
	{
		return $this->companyNumber;
	}


	public function setCompanyNumber(?string $companyNumber): void
	{
		$this->companyNumber = $companyNumber;
	}


	public function getCompanyVatNumber(): ?string
	{
		return $this->companyVatNumber;
	}


	public function setCompanyVatNumber(?string $companyVatNumber): void
	{
		$this->companyVatNumber = $companyVatNumber;
	}
}
