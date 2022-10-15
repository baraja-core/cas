<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\UserLoginIdentityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Nette\Utils\Random;

#[ORM\Entity(repositoryClass: UserLoginIdentityRepository::class)]
#[ORM\Table(name: 'cas__user_login_identity')]
class UserLoginIdentity
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[Column(type: 'string', length: 32, unique: true)]
	private string $identityId;

	#[ORM\ManyToOne(targetEntity: OrganisationMember::class)]
	private OrganisationMember $member;

	#[Column(type: 'string', length: 64, nullable: true)]
	private ?string $location;

	#[Column(type: 'string', length: 8, nullable: true)]
	private ?string $device = null;

	#[Column(type: 'datetime')]
	private \DateTimeInterface $insertedDate;

	#[Column(type: 'datetime')]
	private \DateTimeInterface $expirationDate;

	#[Column(type: 'datetime')]
	private \DateTimeInterface $lastActivityDate;


	public function __construct(OrganisationMember $member, \DateTimeInterface $expirationDate)
	{
		$this->identityId = Random::generate(32);
		$this->member = $member;
		$this->insertedDate = new \DateTimeImmutable;
		$this->expirationDate = $expirationDate;
		$this->lastActivityDate = new \DateTimeImmutable;
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getIdentityId(): string
	{
		return $this->identityId;
	}


	public function getMember(): OrganisationMember
	{
		return $this->member;
	}


	public function getLocation(): ?string
	{
		return $this->location;
	}


	public function setLocation(?string $location): void
	{
		$this->location = $location;
	}


	public function getDevice(): ?string
	{
		return $this->device;
	}


	public function setDevice(?string $device): void
	{
		$this->device = $device;
	}


	public function getInsertedDate(): \DateTimeInterface
	{
		return $this->insertedDate;
	}


	public function getExpirationDate(): \DateTimeInterface
	{
		return $this->expirationDate;
	}


	public function setExpirationDate(\DateTimeInterface $expirationDate): void
	{
		$this->expirationDate = $expirationDate;
	}


	public function getLastActivityDate(): \DateTimeInterface
	{
		return $this->lastActivityDate;
	}
}
