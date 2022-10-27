<?php

declare(strict_types=1);

namespace Baraja\CAS;


final class CasHelper
{
	public static function formatUsername(string $username): string
	{
		$username = mb_strtolower(trim($username), 'UTF-8');
		if (mb_strlen($username, 'UTF-8') > 64) {
			throw new \InvalidArgumentException(sprintf('Username "%s" is too long.', $username));
		}
		if (preg_match('/^[a-z0-9@\-_.]+$/', $username) !== 1) {
			throw new \InvalidArgumentException(
				sprintf('Username "%s" is not valid, because it contains forbidden characters.', $username),
			);
		}

		return $username;
	}


	public static function generateOtpCode(): string
	{
		try {
			$code = random_bytes(10);
		} catch (\Throwable $e) {
			throw new \RuntimeException($e->getMessage(), 500, $e);
		}

		return $code;
	}


	/**
	 * @param string $data -> a string of length divisible by five
	 */
	public static function otpBase32Encode(string $data): string
	{
		static $codes = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$bits = '';
		foreach (str_split($data) as $c) {
			$bits .= sprintf('%08b', \ord($c));
		}
		$return = '';
		foreach (str_split($bits, 5) as $c) {
			$return .= $codes[bindec($c)];
		}

		return $return;
	}


	/**
	 * Generate URL for OTP QR code
	 *
	 * @param string $issuer -> service (or project) name
	 * @param string $user -> username (displayed in Authenticator app)
	 * @param string $secret -> in binary format
	 */
	public static function getOtpQrUrl(string $issuer, string $user, string $secret): string
	{
		return 'https://chart.googleapis.com/chart?chs=500x500&chld=M|0&cht=qr&chl='
			. urlencode(
				'otpauth://totp/' . rawurlencode($issuer)
				. ':' . $user . '?secret=' . self::otpBase32Encode($secret)
				. '&issuer=' . rawurlencode($issuer),
			);
	}


	/**
	 * Generate one-time password
	 *
	 * @param string $secret -> in binary format
	 * @param string $timeSlot -> example: floor(time() / 30)
	 */
	public static function getOtp(string $secret, string $timeSlot): int
	{
		$data = str_pad(pack('N', $timeSlot), 8, "\0", STR_PAD_LEFT);
		$hash = hash_hmac('sha1', $data, $secret, true);
		$offset = \ord(\substr($hash, -1)) & 0xF;
		$unpacked = (array) unpack('N', substr($hash, $offset, 4));

		return ($unpacked[1] & 0x7FFFFFFF) % 1e6;
	}


	public static function hashPassword(string $password): string
	{
		if ($password === '') {
			return '---empty-password---';
		}

		$hash = @password_hash($password, PASSWORD_DEFAULT); // @ is escalated to exception
		if ($hash === '') {
			$error = error_get_last();
			throw new \LogicException(
				sprintf(
					'Computed hash is invalid "%s".',
					$error !== null ? $error['message'] : ''),
			);
		}

		return $hash;
	}


	public static function checkAuthenticatorOtpCodeManually(string $otpCode, int $code): bool
	{
		$checker = static fn(int $timeSlot): bool => self::getOtp($otpCode, (string) $timeSlot) === $code;

		return $checker($slot = (int) floor(time() / 30)) || $checker($slot - 1) || $checker($slot + 1);
	}


	/**
	 * Advance function for parsing real user full name.
	 * Accept name in format "Doc. Ing. Jan Barášek, PhD."
	 *
	 * @return array{firstName: string|null, lastName: string|null, degreeBefore: string|null, degreeAfter: string|null}
	 */
	public static function nameParser(string $name): array
	{
		static $degreePattern = '((?:(?:\s*(?:[A-Za-z]{2,8})\.\s*)+))?';
		$normalized = str_replace(',', '', trim(str_replace('/\s+/', ' ', $name)));
		$degreeBefore = '';
		$degreeAfter = '';

		if (preg_match('/^' . $degreePattern . '\s*([^.]+?)?\s*' . $degreePattern . '$/', $normalized, $degreeParser) === 1) {
			$normalized = trim($degreeParser[2] ?? '');
			$degreeBefore = trim($degreeParser[1] ?? '');
			$degreeAfter = trim($degreeParser[3] ?? '');
		}

		$parts = explode(' ', $normalized, 2);
		$firstName = self::firstUpper($parts[0] ?? '');
		$lastName = self::firstUpper($parts[1] ?? '');

		return [
			'firstName' => $firstName !== '' ? $firstName : null,
			'lastName' => $lastName !== '' ? $lastName : null,
			'degreeBefore' => $degreeBefore !== '' ? $degreeBefore : null,
			'degreeAfter' => $degreeAfter !== '' ? $degreeAfter : null,
		];
	}


	private static function firstUpper(string $s): string
	{
		return mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($s, 1, null, 'UTF-8');
	}
}
