<?php

namespace App\Exceptions;

use Exception;

class AiQuotaExceededException extends Exception
{
    public function __construct()
    {
        parent::__construct('Cota mensal de IA atingida. Renove seu plano ou aguarde o próximo mês.');
    }
}
