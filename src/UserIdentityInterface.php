<?php

declare(strict_types=1);

namespace Baraja\CAS;


interface UserIdentityInterface
{
	public function getId(): int;

	public function getName(): ?string;

	public function getAvatarUrl(): ?string;

	public function getOtpCode(): ?string;
}
