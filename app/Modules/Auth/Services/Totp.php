<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\SecondFactor;
use InvalidArgumentException;

final class Totp implements SecondFactor
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const DIGITS = 6;

    private const PERIOD = 30;

    private const WINDOW = 1;

    /** 160 bits, the size RFC 4226 recommends for a SHA-1 HMAC key. */
    private const SECRET_BYTES = 20;

    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(self::SECRET_BYTES));
    }

    public function verify(string $secret, string $code, ?int $at = null): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $counter = intdiv($at ?? time(), self::PERIOD);
        $valid = false;

        // No early return; the work must not depend on which step matched.
        for ($step = -self::WINDOW; $step <= self::WINDOW; $step++) {
            if (hash_equals($this->codeForCounter($secret, $counter + $step), $code)) {
                $valid = true;
            }
        }

        return $valid;
    }

    public function codeAt(string $secret, int $timestamp): string
    {
        return $this->codeForCounter($secret, intdiv($timestamp, self::PERIOD));
    }

    public function provisioningUri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer.':'.$account).'?'.http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);
    }

    private function codeForCounter(string $secret, int $counter): string
    {
        $hash = hash_hmac('sha1', pack('J', max($counter, 0)), $this->base32Decode($secret), true);

        $offset = ord($hash[19]) & 0x0F;

        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $bits = '';

        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';

        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[((int) bindec(str_pad($chunk, 5, '0'))) & 0x1F];
        }

        return $encoded;
    }

    private function base32Decode(string $encoded): string
    {
        $encoded = rtrim(strtoupper($encoded), '=');
        $bits = '';

        foreach (str_split($encoded) as $char) {
            $index = strpos(self::ALPHABET, $char);

            if ($index === false) {
                throw new InvalidArgumentException('The secret is not valid base32.');
            }

            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(((int) bindec($chunk)) & 0xFF);
            }
        }

        return $bytes;
    }
}
