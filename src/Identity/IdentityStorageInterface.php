<?php

declare(strict_types=1);

namespace Baraja\CAS\Identity;


use Baraja\CAS\UserIdentity;

interface IdentityStorageInterface
{
	public function getIdentity(): ?UserIdentity;

	public function saveIdentity(UserIdentity $identity, ?\DateTimeInterface $expiration = null): void;

	public function removeIdentity(): void;
}
