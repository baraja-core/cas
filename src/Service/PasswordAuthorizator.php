<?php

declare(strict_types=1);

namespace Baraja\CAS\Service;


class PasswordAuthorizator
{
	public function verify(string $password, string $hash): bool
	{
		return password_verify($password, $hash)
			|| md5($password) === $hash
			|| sha1(md5($password)) === $hash;
	}
}
