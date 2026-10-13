<?php

namespace App\Application\Errors;

/**
 * Error de negocio con su código del contrato (api-contract.md §2).
 * Solo el adaptador HTTP lo traduce al cuerpo de error único.
 */
class ApplicationError extends \RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        public readonly int $status,
        string $message,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public static function invalidCredentials(): self
    {
        return new self('INVALID_CREDENTIALS', 401, 'Usuario o contraseña incorrectos.');
    }
}
