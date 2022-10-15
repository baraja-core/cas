<?php

declare(strict_types=1);

namespace Baraja\CAS\Api\DTO;


final class LoginResponse
{
	public function __construct(
		public string $identityId,
		public bool $requireOtp,
	) {
	}
}
