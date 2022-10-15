<?php

declare(strict_types=1);

namespace Baraja\CAS;


class AuthenticationService
{
	/** Exception error code */
	public const
		IdentityNotFound = 1,
		InvalidCredential = 2,
		Failure = 3,
		NotApproved = 4;


	public function authentication(string $username, string $password): UserIdentity
	{
	}
}
