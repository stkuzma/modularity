<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit;

use App\Modules\Auth\Services\QrCode;
use App\Modules\Auth\Services\Totp;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class QrCodeTest extends TestCase
{
    #[Test]
    public function it_renders_an_inline_svg(): void
    {
        $svg = (new QrCode)->svg('otpauth://totp/Modularity:ada@example.com?secret=JBSWY3DPEHPK3PXP');

        $this->assertStringStartsWith('<svg', trim($svg));
        $this->assertStringNotContainsString('<?xml', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    #[Test]
    public function it_encodes_a_real_provisioning_uri(): void
    {
        $totp = new Totp;
        $uri = $totp->provisioningUri($totp->generateSecret(), 'ada@example.com', 'Modularity');

        $this->assertGreaterThan(500, strlen((new QrCode)->svg($uri)));
    }

    #[Test]
    public function the_requested_size_reaches_the_output(): void
    {
        $svg = (new QrCode)->svg('otpauth://totp/x?secret=JBSWY3DPEHPK3PXP', 320);

        $this->assertStringContainsString('width="320"', $svg);
    }
}
