<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit;

use App\Modules\Auth\Services\Totp;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TotpTest extends TestCase
{
    /** ASCII "12345678901234567890", the RFC's SHA-1 seed, in base32. */
    private const RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    /**
     * @return list<array{int, string}>
     */
    public static function rfcVectors(): array
    {
        return [
            [59, '287082'],
            [1111111109, '081804'],
            [1111111111, '050471'],
            [1234567890, '005924'],
            [2000000000, '279037'],
            [20000000000, '353130'],
        ];
    }

    #[Test]
    #[DataProvider('rfcVectors')]
    public function it_matches_the_rfc_6238_vectors(int $timestamp, string $expected): void
    {
        $this->assertSame($expected, (new Totp)->codeAt(self::RFC_SECRET, $timestamp));
    }

    #[Test]
    public function it_accepts_a_code_from_the_current_step(): void
    {
        $totp = new Totp;
        $at = 1111111111;

        $this->assertTrue($totp->verify(self::RFC_SECRET, $totp->codeAt(self::RFC_SECRET, $at), $at));
    }

    #[Test]
    public function it_tolerates_one_step_of_drift_in_either_direction(): void
    {
        $totp = new Totp;
        $at = 1111111111;

        $this->assertTrue($totp->verify(self::RFC_SECRET, $totp->codeAt(self::RFC_SECRET, $at - 30), $at));
        $this->assertTrue($totp->verify(self::RFC_SECRET, $totp->codeAt(self::RFC_SECRET, $at + 30), $at));
    }

    #[Test]
    public function it_rejects_a_code_two_steps_away(): void
    {
        $totp = new Totp;
        $at = 1111111111;

        $this->assertFalse($totp->verify(self::RFC_SECRET, $totp->codeAt(self::RFC_SECRET, $at - 90), $at));
        $this->assertFalse($totp->verify(self::RFC_SECRET, $totp->codeAt(self::RFC_SECRET, $at + 90), $at));
    }

    #[Test]
    public function it_rejects_anything_that_is_not_six_digits(): void
    {
        $totp = new Totp;

        $this->assertFalse($totp->verify(self::RFC_SECRET, '', 59));
        $this->assertFalse($totp->verify(self::RFC_SECRET, '12345', 59));
        $this->assertFalse($totp->verify(self::RFC_SECRET, '1234567', 59));
        $this->assertFalse($totp->verify(self::RFC_SECRET, 'abcdef', 59));
    }

    #[Test]
    public function it_ignores_the_spaces_authenticator_apps_display(): void
    {
        $totp = new Totp;

        $this->assertTrue($totp->verify(self::RFC_SECRET, '287 082', 59));
    }

    #[Test]
    public function a_generated_secret_is_usable_base32(): void
    {
        $totp = new Totp;
        $secret = $totp->generateSecret();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
        $this->assertTrue($totp->verify($secret, $totp->codeAt($secret, 1700000000), 1700000000));
    }

    #[Test]
    public function two_generated_secrets_differ(): void
    {
        $totp = new Totp;

        $this->assertNotSame($totp->generateSecret(), $totp->generateSecret());
    }

    #[Test]
    public function it_refuses_a_secret_that_is_not_base32(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Totp)->codeAt('not-base32!', 59);
    }

    #[Test]
    public function it_builds_a_provisioning_uri_an_authenticator_can_read(): void
    {
        $uri = (new Totp)->provisioningUri(self::RFC_SECRET, 'ada@example.com', 'Modularity');

        $this->assertStringStartsWith('otpauth://totp/Modularity%3Aada%40example.com?', $uri);
        $this->assertStringContainsString('secret='.self::RFC_SECRET, $uri);
        $this->assertStringContainsString('issuer=Modularity', $uri);
        $this->assertStringContainsString('digits=6', $uri);
        $this->assertStringContainsString('period=30', $uri);
    }
}
