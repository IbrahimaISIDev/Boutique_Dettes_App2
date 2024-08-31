<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use App\Traits\RestResponseTrait;
use App\Enums\StateEnum;

class Handler extends ExceptionHandler
{
    use RestResponseTrait;

    // ... autres propriétés et méthodes ...

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return $this->sendResponse(
                    null,
                    StateEnum::ECHEC,
                    $exception->errors()['libelle'][0] ?? 'Erreur de validation',
                    422
                );
            }

            // Gérer d'autres types d'exceptions si nécessaire

            return $this->sendResponse(
                null,
                StateEnum::ECHEC,
                'Une erreur inattendue est survenue',
                500
            );
        }

        return parent::render($request, $exception);
    }
}