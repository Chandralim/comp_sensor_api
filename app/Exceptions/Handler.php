<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

use Illuminate\Auth\AuthenticationException;

use App\Exceptions\MyException;
use App\Helpers\MyLog;

class Handler extends ExceptionHandler
{
  /**
   * A list of the exception types that are not reported.
   *
   * @var array
   */
  protected $dontReport = [
    //
  ];

  /**
   * A list of the inputs that are never flashed for validation exceptions.
   *
   * @var array
   */
  protected $dontFlash = [
    'password',
    'password_confirmation',
  ];

  /**
   * Report or log an exception.
   *
   * @param  \Throwable  $exception
   * @return void
   *
   * @throws \Throwable
   */
  public function report(Throwable $exception)
  {
    parent::report($exception);
  }

  /**
   * Render an exception into an HTTP response.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Throwable  $exception
   * @return \Symfony\Component\HttpFoundation\Response
   *
   * @throws \Throwable
   */
  public function render($request, Throwable $exception)
  {
    $rendered = parent::render($request, $exception);
    $status_code = $rendered->getStatusCode();

    $debug = config('app.debug');
    $message = '';
    // cek jika eksepsinya dikarenakan model tidak ditemukan
    if ($exception instanceof ModelNotFoundException) {
      $message = 'Resource is not found';
    }
    // cek jika eksepsinya dikarenakan resource tidak ditemukan
    elseif ($exception instanceof NotFoundHttpException) {
      $message = 'Endpoint is not found';
    }
    // cek jika eksepsinya dikarenakan method tidak diizinkan
    elseif ($exception instanceof MethodNotAllowedHttpException) {
      $message = 'Method is not allowed';
    }
    // cek jika eksepsinya dikarenakan kegagalan validasi
    else if ($exception instanceof ValidationException) {
      return response()->json($exception->errors(), $rendered->getStatusCode());
    }
    // cek jika eksepsinya dikarenakan kegagalan query
    else if ($exception instanceof QueryException) {
      if ($debug) {
        $message = $exception->getMessage();
      } else {
        $message = 'Query failed to execute';
      }
    } else if ($exception instanceof MyException) {
      $status_code = $exception->getCode() == 0 ? 400 : $exception->getCode();
      return response()->json($exception->getData(), $status_code);
    }
    if (empty($message)) {
      $message = $exception->getMessage();
    }
    $errors = [];
    if ($debug) {
      $errors['message'] = $message;
      $errors['exception'] = get_class($exception);
      $errors['trace'] = explode("\n", $exception->getTraceAsString());
    }

    $return = [
      'message' => $message,
      'data' => null,
      'errors' => $errors,
    ];

    return response()->json($return, $status_code);
  }


  protected function unauthenticated($request, AuthenticationException $exception)
  {
    return response()->json([
      'status' => 'error',
      'message' => 'Unauthenticate',
      'data' => null
    ], 401);
  }
}
