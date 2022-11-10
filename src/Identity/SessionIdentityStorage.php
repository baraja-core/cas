<?php

declare(strict_types=1);

namespace Baraja\CAS\Identity;


use Baraja\CAS\UserIdentity;

final class SessionIdentityStorage implements IdentityStorageInterface
{
	private const SessionKey = '_BRJ-cas-identity';


	public function __construct()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}


	public function getIdentity(): ?UserIdentity
	{
		$identity = $_SESSION[self::SessionKey] ?? null;

		try {
			return $identity instanceof UserIdentity && $identity->getId() > 0 ? $identity : null;
		} catch (\Throwable) {
			return null;
		}
	}


	public function removeIdentity(): void
	{
		if (isset($_SESSION) && session_status() === PHP_SESSION_ACTIVE) {
			$_SESSION['__BRJ_CMS'] = [];
			unset($_SESSION[self::SessionKey]);
			session_destroy();
		}
	}


	public function saveIdentity(UserIdentity $identity, ?\DateTimeInterface $expiration = null): void
	{
		$_SESSION[self::SessionKey] = $identity;
	}
}
