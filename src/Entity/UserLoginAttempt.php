<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\Repository\UserLoginAttemptRepository;
use Baraja\Network\Ip;
use Baraja\Url\Url;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

#[ORM\Entity(repositoryClass: UserLoginAttemptRepository::class)]
#[ORM\Table(name: 'cas__user_login_attempt')]
class UserLoginAttempt
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'loginAttempts')]
	private ?User $user;

	#[ORM\Column(type: 'string', length: 64)]
	private string $username;

	#[ORM\Column(type: 'boolean')]
	private bool $password = false;

	#[ORM\Column(type: 'string', length: 2048, nullable: true)]
	private ?string $loginUrl = null;

	#[ORM\Column(type: 'string', length: 39, nullable: true)]
	private string $ip;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeImmutable $insertedDateTime;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $notice;


	public function __construct(?User $user, string $username)
	{
		$this->user = $user;
		$this->username = mb_strtolower(mb_substr(trim($username), 0, 64, 'UTF-8'), 'UTF-8');
		$this->ip = PHP_SAPI === 'cli' ? 'cli' : Ip::get();
		$this->insertedDateTime = new \DateTimeImmutable('now');
		$this->setLoginUrl();
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getUser(): ?User
	{
		return $this->user;
	}


	public function setUser(User $user): void
	{
		$this->user = $user;
	}


	public function getUsername(): string
	{
		return $this->username;
	}


	public function isPasswordOk(): bool
	{
		return $this->password;
	}


	public function setOkPassword(): bool
	{
		return $this->password = true;
	}


	public function getLoginUrl(): ?string
	{
		return $this->loginUrl;
	}


	public function getIp(): ?string
	{
		return $this->ip;
	}


	public function getInsertedDateTime(): \DateTimeImmutable
	{
		return $this->insertedDateTime;
	}


	public function getNotice(): ?string
	{
		return $this->notice;
	}


	public function setNotice(?string $notice): void
	{
		$this->notice = $notice;
	}


	public function addNotice(?string $notice): void
	{
		$this->notice = trim($this->notice . "\n" . $notice);
	}


	private function setLoginUrl(): void
	{
		try {
			$this->loginUrl = Strings::substring(Url::get()->getCurrentUrl(), 0, 2_000);
		} catch (\Throwable) {
			// Silence is golden.
		}
	}
}
