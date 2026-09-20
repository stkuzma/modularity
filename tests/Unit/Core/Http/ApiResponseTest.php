<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http;

use App\Core\Http\Responses\ApiResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApiResponseTest extends TestCase
{
    #[Test]
    public function it_wraps_data_in_a_data_key(): void
    {
        $response = ApiResponse::success(['id' => 1]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"data":{"id":1}}', $response->getContent());
    }

    #[Test]
    public function it_omits_meta_when_there_is_none(): void
    {
        $response = ApiResponse::success(['id' => 1]);

        $this->assertStringNotContainsString('meta', (string) $response->getContent());
    }

    #[Test]
    public function it_includes_meta_when_given(): void
    {
        $response = ApiResponse::success([], ['page' => 2]);

        $this->assertStringContainsString('"meta":{"page":2}', (string) $response->getContent());
    }

    #[Test]
    public function it_shapes_failures_under_an_error_key(): void
    {
        $response = ApiResponse::failure('users.email_taken', 'Taken.', ['email' => 'a@b.c'], 409);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame(
            '{"error":{"code":"users.email_taken","message":"Taken.","details":{"email":"a@b.c"}}}',
            $response->getContent(),
        );
    }

    #[Test]
    public function it_omits_details_when_there_are_none(): void
    {
        $response = ApiResponse::failure('core.not_found', 'Missing.', [], 404);

        $this->assertStringNotContainsString('details', (string) $response->getContent());
    }
}
