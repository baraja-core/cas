<?php

declare(strict_types=1);

namespace Baraja\CAS;


use Nette\Utils\Validators;

class UserIdentity implements UserIdentityInterface
{
	/**
	 * @param array<int, string> $roles
	 */
	public function __construct(
		private int $id,
		private int $memberId,
		private int $userId,
		private string $identityId,
		private int $organisationId,
		private array $roles = [],
		private ?string $name = null,
		private ?string $avatarUrl = null,
	) {
		if ($this->avatarUrl !== null && Validators::isUrl($this->avatarUrl) === false) {
			throw new \InvalidArgumentException(
				sprintf('Avatar URL is not valid URL, because "%s" given.', $this->avatarUrl),
			);
		}
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getMemberId(): int
	{
		return $this->memberId;
	}


	public function getUserId(): int
	{
		return $this->userId;
	}


	public function getIdentityId(): string
	{
		return $this->identityId;
	}


	public function getOrganisationId(): int
	{
		return $this->organisationId;
	}


	/** @return array<int, string> */
	public function getRoles(): array
	{
		return $this->roles;
	}


	public function getName(): ?string
	{
		return $this->name;
	}


	public function getAvatarUrl(): ?string
	{
		return $this->avatarUrl;
	}


	public function getOtpCode(): ?string
	{
		return null;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		$rel = new \ReflectionClass($this);
		$return = [];
		foreach ($rel->getProperties() as $property) {
			$property->setAccessible(true);
			$return[$property->getName()] = $property->getValue($this);
		}

		return $return;
	}
}
