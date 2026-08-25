<?php

namespace App\Support;

class TempPassword
{
    /**
     * Regras de validação do telefone/WhatsApp nos cadastros cuja senha inicial
     * é derivada dele (diretor, vice-diretor, coordenador, supervisores).
     * Exige ao menos 4 dígitos — sem eles não há senha a derivar.
     */
    public static function phoneRules(): array
    {
        return ['required', 'string', 'max:20', 'regex:/(?:\D*\d){4,}/'];
    }

    /**
     * @return array<string, string>
     */
    public static function phoneMessages(): array
    {
        return [
            'whatsapp.required' => 'Informe o WhatsApp: a senha inicial serão os 4 últimos dígitos dele.',
            'whatsapp.regex' => 'O WhatsApp precisa ter pelo menos 4 dígitos.',
        ];
    }

    /**
     * Os 4 últimos dígitos do telefone, ignorando máscara (parênteses, traço,
     * espaço). Devolve null quando não há dígitos suficientes — caso dos
     * cadastros feitos antes de o telefone virar obrigatório.
     */
    public static function fromPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return strlen($digits) >= 4 ? substr($digits, -4) : null;
    }

    /**
     * Senha inicial de um cadastro gerenciado + a explicação da origem, para a
     * mensagem que o gestor lê na tela. Cai no aleatório quando o cadastro é
     * antigo e não tem telefone.
     *
     * @return array{0: string, 1: string} [senha, origem]
     */
    public static function resolve(?string $phone): array
    {
        $fromPhone = self::fromPhone($phone);

        return $fromPhone !== null
            ? [$fromPhone, 'os 4 últimos dígitos do WhatsApp cadastrado']
            : [self::generate(), 'gerada aleatoriamente (cadastro sem WhatsApp)'];
    }

    /**
     * Generates a random temporary password avoiding visually ambiguous
     * characters (0/O/o, 1/l/I) that are easily mistyped when a user
     * manually retypes a password shown on screen.
     */
    public static function generate(int $length = 10): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
        $max = strlen($chars) - 1;

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }

        return $password;
    }
}
