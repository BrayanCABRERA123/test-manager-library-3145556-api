<?php

namespace App\Adapters\Inbound\Http;

use App\Application\Errors\ApplicationError;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * RNF-4: todo error HTTP usa el mismo cuerpo { code, message, details } (api-contract.md §1).
 * Nunca incluye trazas, SQL, rutas del servidor ni la contraseña.
 */
final class ErrorResponse
{
    public static function from(Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ApplicationError => self::make($e->errorCode, $e->getMessage(), $e->status, $e->details),
            $e instanceof ValidationException => self::make(
                'VALIDATION_ERROR',
                'Hay campos inválidos.',
                422,
                self::validationDetails($e),
            ),
            $e instanceof AuthenticationException => self::make('UNAUTHENTICATED', 'Falta el token o no es válido.', 401),
            $e instanceof AuthorizationException,
            $e instanceof AccessDeniedHttpException => self::make('FORBIDDEN_ROLE', 'Tu rol no permite esta operación.', 403),
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => self::make('NOT_FOUND', 'El recurso no existe.', 404),
            // Ruta o método inexistente: el contrato solo conoce NOT_FOUND para "no existe".
            $e instanceof HttpExceptionInterface && $e->getStatusCode() < 500 => self::make('NOT_FOUND', 'El recurso no existe.', 404),
            default => self::make('INTERNAL_ERROR', 'Ocurrió un error inesperado.', 500),
        };
    }

    private static function make(string $code, string $message, int $status, array $details = []): JsonResponse
    {
        return response()->json(['code' => $code, 'message' => $message, 'details' => $details], $status);
    }

    /** @return list<array{field: string, issue: string}> */
    private static function validationDetails(ValidationException $e): array
    {
        $details = [];
        foreach ($e->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $details[] = ['field' => $field, 'issue' => $message];
            }
        }

        return $details;
    }
}
