<?php

declare(strict_types=1);

namespace Baraja\CAS\Api;


use Baraja\CAS\Api\DTO\LoginResponse;
use Baraja\CAS\User;
use Baraja\StructuredApi\BaseEndpoint;

final class CasEndpoint extends BaseEndpoint
{
	public function __construct(
		public User $userService,
	) {
	}


	public function postLogin(string $username, string $password, bool $permanent = false): LoginResponse
	{
		$identity = $this->userService->getAuthenticator()->authentication($username, $password, $permanent);

		return new LoginResponse(
			identityId: $identity->getIdentityId(),
			requireOtp: $identity->getMember()->getUser()->getOtpCode() !== null,
		);
	}


	public function postLogout(?string $identityId = null): void
	{
		$this->userService->logout($identityId);
		$this->sendOk();
	}
}
