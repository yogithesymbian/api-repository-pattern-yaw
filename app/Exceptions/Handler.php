<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    public function render($request, Throwable $exception)
    {
        // return parent::render($request, $e);

        if ($exception instanceof ValidationException) {
            $errors = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => $exception->errors()
            ];
            return response()->json(
                $errors,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            $errors = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => [$exception->getMessage()]
            ];
            return response()->json(
                $errors,
                Response::HTTP_NOT_FOUND
            );
        }

        if ($exception instanceof InvalidCredentialException){
            $errors = [
                'status' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'errors' => [
                    'Invalid email or username.'
                ]
            ];

            return response()->json(
                $errors,
                Response::HTTP_NOT_FOUND
            );
        }

        $rendered = parent::render($request, $exception);
        return response()->json([
            'code' => $rendered->getStatusCode(),
            'errors' => [
                'message' => $exception->getMessage(),
            ]
        ], $rendered->getStatusCode());
        // return parent::render($request, $exception);

    }
}
