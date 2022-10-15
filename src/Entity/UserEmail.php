<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\UserEmailRepository;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Validators;

#[ORM\Entity(repositoryClass: UserEmailRepository::class)]
#[ORM\Table(name: 'cas__user_email')]
class UserEmail
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private User $user;

	#[ORM\Column(type: 'string', length: 128, unique: true)]
	private string $email;

	#[ORM\Column(type: 'boolean')]
	private bool $verified = false;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $insertedDate;


	public function __construct(User $user, string $email)
	{
		if (Validators::isEmail($email) === false) {
			throw new \InvalidArgumentException(sprintf('Invalid user email "%s".', $email));
		}
		$this->user = $user;
		$this->email = $email;
		$this->insertedDate = new \DateTimeImmutable;
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getUser(): User
	{
		return $this->user;
	}


	public function getEmail(): string
	{
		return $this->email;
	}


	public function isVerified(): bool
	{
		return $this->verified;
	}


	public function setVerified(bool $verified): void
	{
		$this->verified = $verified;
	}


	public function getInsertedDate(): \DateTimeInterface
	{
		return $this->insertedDate;
	}
}
