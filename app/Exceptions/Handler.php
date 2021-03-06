<?php

namespace App\Exceptions;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  Exception $e
     * @return void
     * @throws Exception
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Exception  $e
     * @return Response|JsonResponse
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof NotFoundHttpException) {
            return Controller::response(Response::HTTP_NOT_FOUND, ['errors' => ['Method not found']]);
        }
        if ($e instanceof MethodNotAllowedHttpException) {
            return Controller::response(Response::HTTP_METHOD_NOT_ALLOWED, ['errors' => ['Method not allowed']]);
        }
        if ($e instanceof FatalThrowableError) {
            Log::alert('internal.error', ['error' => $e]);
            return Controller::response(Response::HTTP_BAD_REQUEST, ['errors' => ['Invalid request']]);
        }
        return parent::render($request, $e);
    }
}
