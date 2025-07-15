<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle API requests
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions.
     */
    protected function handleApiException(Request $request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof HttpException) {
            return $this->convertHttpException($exception);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->modelNotFound($exception);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof ValidationException) {
            return $this->convertValidationException($exception);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->notFound();
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->methodNotAllowed();
        }

        return $this->customApiResponse($exception);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
            'error' => 'Authentication required'
        ], 401);
    }

    /**
     * Convert a validation exception into a JSON response.
     */
    protected function convertValidationException(ValidationException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $exception->errors()
        ], 422);
    }

    /**
     * Convert HTTP exception into a JSON response.
     */
    protected function convertHttpException(HttpException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage() ?: 'Server Error',
            'error' => class_basename($exception)
        ], $exception->getStatusCode());
    }

    /**
     * Handle model not found exception.
     */
    protected function modelNotFound(ModelNotFoundException $exception)
    {
        $model = class_basename($exception->getModel());
        
        return response()->json([
            'success' => false,
            'message' => "{$model} not found.",
            'error' => 'Resource not found'
        ], 404);
    }

    /**
     * Handle not found exception.
     */
    protected function notFound()
    {
        return response()->json([
            'success' => false,
            'message' => 'Route not found.',
            'error' => 'Not found'
        ], 404);
    }

    /**
     * Handle method not allowed exception.
     */
    protected function methodNotAllowed()
    {
        return response()->json([
            'success' => false,
            'message' => 'Method not allowed.',
            'error' => 'Method not allowed'
        ], 405);
    }

    /**
     * Handle custom API response for general exceptions.
     */
    protected function customApiResponse(Throwable $exception)
    {
        if (config('app.debug')) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'error' => class_basename($exception),
                'trace' => $exception->getTraceAsString()
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Internal server error.',
            'error' => 'Server error'
        ], 500);
    }
} 