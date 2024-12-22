<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Database\QueryException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e) {
            return $this->handleException($e);
        });
    }

    private function handleException(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()->toArray(),
                'code' => 'VALIDATION_ERROR'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found',
                'code' => 'RESOURCE_NOT_FOUND'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Route not found',
                'code' => 'ROUTE_NOT_FOUND'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof AccessDeniedHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied',
                'code' => 'ACCESS_DENIED'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($e instanceof QueryException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database error occurred',
                'code' => 'DATABASE_ERROR'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Handle any other exceptions
        if (config('app.debug')) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => 'SERVER_ERROR',
                'trace' => $e->getTrace()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'An unexpected error occurred',
            'code' => 'SERVER_ERROR'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
} 