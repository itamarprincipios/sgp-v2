<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
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

        // Sessão expirada (419). Sem isto o gestor cai na tela crua
        // "419 PAGE EXPIRED", em inglês e sem caminho de volta.
        $this->renderable(function (TokenMismatchException $e, Request $request) {
            $mensagem = 'A página ficou aberta tempo demais e o envio expirou. Entre novamente e refaça a operação.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $mensagem], 419);
            }

            // Sessão ainda válida (só o token venceu): devolve ao formulário
            // com o que já tinha sido digitado.
            if (auth()->check()) {
                return redirect()->back()
                    ->withInput($request->except($this->dontFlash))
                    ->with('error', 'O envio expirou por inatividade. Confira os dados e envie novamente.');
            }

            return redirect()->guest(route('login'))->with('status', $mensagem);
        });
    }
}
