<?php

namespace Tests\Unit\Http;

use App\Adapters\Inbound\Http\Middleware\AuthenticateToken;
use App\Adapters\Outbound\Security\JwtTokenIssuer;
use App\Application\Errors\ApplicationError;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

/** CA-01.3: sin token 401, token inválido 401, rol insuficiente 403. */
final class AuthenticateTokenTest extends TestCase
{
    private const SECRET = 'test-secret-with-enough-length-for-hs256-signing';

    private JwtTokenIssuer $tokens;

    private AuthenticateToken $middleware;

    protected function setUp(): void
    {
        $this->tokens = new JwtTokenIssuer(self::SECRET);
        $this->middleware = new AuthenticateToken($this->tokens);
    }

    public function test_request_without_token_is_unauthenticated(): void
    {
        $this->assertErrorCode('UNAUTHENTICATED', 401, $this->request(null));
    }

    public function test_request_with_invalid_token_is_unauthenticated(): void
    {
        $this->assertErrorCode('UNAUTHENTICATED', 401, $this->request('not-a-jwt'));
    }

    public function test_insufficient_role_is_forbidden(): void
    {
        $token = $this->tokens->issue('user-1', 'librarian');

        $this->assertErrorCode('FORBIDDEN_ROLE', 403, $this->request($token), 'admin');
    }

    public function test_valid_token_passes_and_exposes_user_and_role(): void
    {
        $request = $this->request($this->tokens->issue('user-1', 'admin'));

        $response = $this->middleware->handle($request, fn () => new Response('ok'), 'admin');

        $this->assertSame('ok', $response->getContent());
        $this->assertSame('user-1', $request->attributes->get('user_id'));
        $this->assertSame('admin', $request->attributes->get('role'));
    }

    private function request(?string $token): Request
    {
        $request = Request::create('/api/v1/categories');
        if ($token !== null) {
            $request->headers->set('Authorization', 'Bearer '.$token);
        }

        return $request;
    }

    private function assertErrorCode(string $code, int $status, Request $request, string ...$roles): void
    {
        try {
            $this->middleware->handle($request, fn () => new Response('ok'), ...$roles);
        } catch (ApplicationError $error) {
            $this->assertSame($code, $error->errorCode);
            $this->assertSame($status, $error->status);

            return;
        }

        $this->fail("Se esperaba {$code}.");
    }
}
