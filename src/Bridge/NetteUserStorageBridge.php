<?php

declare(strict_types=1);

namespace Baraja\CAS\Bridge;


use Baraja\CAS\UserStorage as CasUserStorage;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;
use Nette\Security\UserStorage;

final class NetteUserStorageBridge implements UserStorage
{
	public function __construct(
		private CasUserStorage $userStorage,
	) {
	}


	/**
	 * Sets the authenticated state of user.
	 */
	public function saveAuthentication(IIdentity $identity): void
	{
		throw new \LogicException('Save identity by Nette User is not safe. Please use native CAS service.');
	}


	/**
	 * Removed authenticated state of user.
	 */
	public function clearAuthentication(bool $clearIdentity): void
	{
		$this->userStorage->clearAuthentication();
	}


	/**
	 * Returns user authenticated state, identity and logout reason.
	 *
	 * @return array{bool, ?IIdentity, ?int}
	 */
	public function getState(): array
	{
		$identity = $this->userStorage->getIdentity();

		return $identity === null
			? [false, null, null]
			: [
				true,
				new SimpleIdentity(
					id: $identity->getUserId(),
					roles: $identity->getRoles(),
					data: $identity->toArray(),
				),
				null,
			];
	}


	/**
	 * Enables log out from the persistent storage after inactivity (like '20 minutes').
	 */
	public function setExpiration(?string $expire, bool $clearIdentity): void
	{
		// Silence is golden.
	}
}
