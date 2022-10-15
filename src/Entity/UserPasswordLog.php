<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Helpers;
use Baraja\CAS\Repository\UserPasswordLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPasswordLogRepository::class)]
#[ORM\Table(name: 'cas__user_password_log')]
class UserPasswordLog
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private User $user;

	/**
	 * User real password stored as BCrypt hash.
	 * More info on https://php.baraja.cz/hashovani
	 */
	#[ORM\Column(type: 'string', length: 60)]
	private string $password;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $insertedDate;


	public function __construct(User $user, string $password)
	{
		if (trim($password) === '') {
			throw new \InvalidArgumentException('User password can not be empty.');
		}
		if (strlen($password) < 4) {
			throw new \InvalidArgumentException('Given password is not safe.');
		}

		$this->user = $user;
		$this->password = Helpers::hashPassword($password);
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


	public function getPassword(): string
	{
		return $this->password;
	}


	public function getInsertedDate(): \DateTimeInterface
	{
		return $this->insertedDate;
	}
}
