<?php

declare(strict_types=1);

namespace Baraja\CAS\Entity;


use Baraja\CAS\CasHelper;
use Baraja\CAS\Repository\UserRepository;
use Baraja\CAS\UserIdentityInterface;
use Baraja\Network\Ip;
use Baraja\PhoneNumber\PhoneNumberFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * System user
 *
 * This is basic user table definition for all packages.
 * Current structure is final and developer can not add new columns.
 * If you want add new column, it will be used in all our projects.
 *
 * How to store new specific data?
 *
 * 1. Scalar values set to $data array section with namespace.
 * 2. For complex values create new Doctrine entity with relation here.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'cas__user')]
class User implements UserIdentityInterface
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\Column(type: 'string', length: 64, unique: true)]
	private string $username;

	/**
	 * User real password stored as BCrypt hash.
	 * More info on https://php.baraja.cz/hashovani
	 */
	#[ORM\Column(type: 'string', length: 60)]
	private string $password;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $firstName = null;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $middleName = null;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $lastName = null;

	#[ORM\Column(type: 'string', length: 32, unique: true, nullable: true)]
	private ?string $nick = null;

	#[ORM\OneToOne(targetEntity: UserEmail::class)]
	private UserEmail $email;

	#[ORM\Column(type: 'string', length: 16, nullable: true)]
	private ?string $phone = null;

	#[ORM\Column(type: 'string', length: 39)]
	private string $registerIp;

	#[ORM\Column(type: 'datetime_immutable')]
	private \DateTimeImmutable $registerDate;

	#[ORM\Column(type: 'datetime_immutable')]
	private \DateTimeImmutable $createDate;

	#[ORM\Column(type: 'boolean')]
	private bool $active = true;

	/** @var Collection<UserEmail> */
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: UserEmail::class)]
	private Collection $emails;

	/** @var Collection<UserMeta> */
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: UserMeta::class)]
	private Collection $metas;

	/** @var Collection<UserLogin> */
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: UserLogin::class)]
	private Collection $logins;

	/** @var Collection<UserLoginAttempt> */
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: UserLoginAttempt::class)]
	private Collection $loginAttempts;

	/** @var Collection<UserResetPasswordRequest> */
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: UserResetPasswordRequest::class)]
	private Collection $passwordResets;

	/** @var Collection<OrganisationMember> */
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: OrganisationMember::class)]
	private Collection $organisationMembers;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $personalNumber = null;

	#[ORM\Column(type: 'string', length: 1024, nullable: true)]
	private ?string $avatarUrl = null;

	/** @var string|resource|null */
	#[ORM\Column(type: 'binary', nullable: true)]
	private $otpCode;


	public function __construct(string $username, string $password)
	{
		$this->username = CasHelper::formatUsername($username);
		$this->password = CasHelper::hashPassword($password);
		$this->registerIp = Ip::get();
		$this->registerDate = new \DateTimeImmutable('now');
		$this->createDate = new \DateTimeImmutable('now');
		$this->emails = new ArrayCollection;
		$this->metas = new ArrayCollection;
		$this->logins = new ArrayCollection;
		$this->loginAttempts = new ArrayCollection;
		$this->passwordResets = new ArrayCollection;
		$this->organisationMembers = new ArrayCollection;
	}


	public function __toString(): string
	{
		return $this->getName();
	}


	public function getId(): int
	{
		return $this->id;
	}


	/**
	 * @return array<string, string>
	 */
	public function getData(): array
	{
		return $this->getMetaData();
	}


	public function getSalutation(): ?string
	{
		$name = $this->getName();
		if (trim($name) !== '') {
			return Strings::firstUpper($name); // TODO: Currently not supported
		}

		return null;
	}


	public function getFirstName(): ?string
	{
		return $this->firstName;
	}


	public function setFirstName(?string $firstName): void
	{
		$firstName = Strings::firstUpper(trim($firstName ?? ''));
		if (
			$firstName !== ''
			&& preg_match(
				'/^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.\'-]+$/u',
				$firstName,
			) !== 1
		) {
			throw new \InvalidArgumentException(
				sprintf('User first name is not valid, because "%s" given.', $firstName),
			);
		}
		$this->firstName = $firstName !== '' ? $firstName : null;
	}


	public function getLastName(): ?string
	{
		return $this->lastName;
	}


	public function setLastName(?string $lastName): void
	{
		$lastName = Strings::firstUpper(trim($lastName ?? ''));
		if (
			$lastName !== ''
			&& preg_match(
				'/^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.\'-]+$/u',
				$lastName,
			) !== 1
		) {
			throw new \InvalidArgumentException(sprintf('User last name is not valid, because "%s" given.', $lastName));
		}
		$this->lastName = $lastName !== '' ? $lastName : null;
	}


	public function getUsername(): string
	{
		return CasHelper::formatUsername($this->username);
	}


	public function setUsername(string $username): void
	{
		$this->username = CasHelper::formatUsername($username);
	}


	public function getNick(): ?string
	{
		return $this->nick;
	}


	public function setNick(?string $nick): void
	{
		$nick = Strings::webalize($nick ?? '', '.,-@', false);
		if ($nick === '') {
			$nick = null;
		}
		$this->nick = $nick;
	}


	public function getPassword(): string
	{
		return $this->password;
	}


	public function setPassword(string $password): void
	{
		if (trim($password) === '') {
			throw new \InvalidArgumentException('User (id: "' . $this->getId() . '") password can not be empty.');
		}
		if (strlen($password) < 4) {
			throw new \InvalidArgumentException('Given password is not safe.');
		}

		$this->password = CasHelper::hashPassword($password);
	}


	/**
	 * Set password as legacy MD5/SHA1 or other crypt.
	 * Never store passwords in a readable form!
	 *
	 * @internal never use it for new users! Back compatibility only!
	 */
	public function setLegacyRawPassword(string $password): void
	{
		if (trim($password) === '') {
			$password = '---empty-password---';
		}
		$this->password = $password;
		throw new \RuntimeException(
			'The password was passed unsafely. Please catch this exception if it was intended.',
		);
	}


	public function passwordVerify(string $password): bool
	{
		return password_verify($password, $this->password)
			|| md5($password) === $this->password
			|| sha1(md5($password)) === $this->password;
	}


	/**
	 * Return primary user e-mail.
	 */
	public function getEmail(): string
	{
		return $this->email->getEmail();
	}


	public function setEmail(UserEmail $email): void
	{
		$this->email = $email;
	}


	/**
	 * @return array<int, string>
	 */
	public function getEmails(): array
	{
		$return = [];
		foreach ($this->emails as $email) {
			$return[] = $email->getEmail();
		}

		return $return;
	}


	public function addEmail(UserEmail $email): void
	{
		$this->emails[] = $email;
	}


	public function getRegisterDate(): \DateTimeImmutable
	{
		return $this->registerDate;
	}


	public function setRegisterDate(\DateTimeImmutable $registerDate): void
	{
		$this->registerDate = $registerDate;
	}


	public function getCreateDate(): \DateTimeImmutable
	{
		return $this->createDate;
	}


	public function setCreateDate(\DateTimeImmutable $createDate): void
	{
		$this->createDate = $createDate;
	}


	/** @return Collection<UserMeta> */
	public function getMetas(): Collection
	{
		return $this->metas;
	}


	/**
	 * @return array<string, string>
	 */
	public function getMetaData(): array
	{
		$return = [];
		foreach ($this->metas as $meta) {
			$value = $meta->getValue();
			if ($value !== null) {
				$return[$meta->getKey()] = $value;
			}
		}

		return $return;
	}


	/**
	 * @return Collection<UserLoginAttempt>
	 */
	public function getLoginAttempts(): Collection
	{
		return $this->loginAttempts;
	}


	/**
	 * @return Collection<UserResetPasswordRequest>
	 */
	public function getPasswordResets(): Collection
	{
		return $this->passwordResets;
	}


	/**
	 * @return Collection<OrganisationMember>
	 */
	public function getOrganisationMembers(): Collection
	{
		return $this->organisationMembers;
	}


	public function addLogin(UserLogin $login): void
	{
		$this->logins[] = $login;
	}


	public function getOtpCode(): ?string
	{
		if ($this->otpCode === null) {
			return null;
		}
		if (is_resource($this->otpCode) === true) {
			return (string) stream_get_contents($this->otpCode);
		}

		return $this->otpCode;
	}


	public function setOtpCode(?string $otpCode): void
	{
		$this->otpCode = $otpCode;
	}


	public function isActive(): bool
	{
		return $this->active;
	}


	public function setActive(bool $active): void
	{
		$this->active = $active;
	}


	public function getAvatarUrl(): string
	{
		return $this->avatarUrl ?? sprintf('https://cdn.baraja.cz/avatar/%s.png', md5($this->getEmail()));
	}


	public function setAvatarUrl(?string $avatarUrl): void
	{
		$this->avatarUrl = $avatarUrl;
	}


	public function getName(bool $reverse = false): string
	{
		if ($this->getFirstName() === null && $this->getLastName() === null) {
			return Strings::firstUpper((string) preg_replace('/^(.*)@.*$/', '$1', $this->getUsername()));
		}
		$name = $this->getFirstName() ?? '';
		if ($name !== '' || $this->getLastName() !== null) {
			$name = $reverse === true
				? $this->getLastName() . ($name !== '' ? ', ' : '') . $name
				: $name . ($name !== '' ? ' ' : '') . $this->getLastName();
		}

		return trim($name, ', ');
	}


	public function getPhone(): ?string
	{
		return $this->phone;
	}


	public function setPhone(?string $phone, int $region = 420): void
	{
		if (class_exists(PhoneNumberFormatter::class) && $phone !== null && $phone !== '') {
			$phone = PhoneNumberFormatter::fix($phone, $region);
		}
		$this->phone = $phone;
	}


	/**
	 * @return Collection<UserLogin>
	 */
	public function getLogins(): Collection
	{
		return $this->logins;
	}


	public function getRegisterIp(): string
	{
		return $this->registerIp ?? '127.0.0.1';
	}


	public function setRegisterIp(string $registerIp): void
	{
		$this->registerIp = $registerIp;
	}


	public function getMiddleName(): ?string
	{
		return $this->middleName;
	}


	public function setMiddleName(?string $middleName): void
	{
		$this->middleName = $middleName;
	}


	public function getPersonalNumber(): ?string
	{
		return $this->personalNumber;
	}


	public function setPersonalNumber(?string $personalNumber): void
	{
		$this->personalNumber = $personalNumber;
	}
}
