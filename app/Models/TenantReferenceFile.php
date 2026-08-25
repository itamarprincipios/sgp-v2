<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Material de referência que a rede envia para a IANNE levar em conta ao
 * analisar planejamentos: modelo de requisitos, portaria, rubrica própria,
 * recorte de currículo. Complementa o DCRR fatiado de resources/dcrr/.
 *
 * O que vai para o prompt é o content_text extraído no momento do upload —
 * o arquivo original fica guardado só para o gestor reconferir/baixar.
 */
class TenantReferenceFile extends Model
{
    protected $fillable = [
        'tenant_id',
        'title',
        'original_name',
        'file_path',
        'extension',
        'content_text',
        'chars',
        'extraction_ok',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'extraction_ok' => 'boolean',
        'is_active' => 'boolean',
        'chars' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * PDFs são extraídos pela própria IA, cuja resposta é limitada a 8192
     * tokens. Perto desse teto o texto provavelmente veio cortado, e o gestor
     * precisa saber — um recorte silencioso faria a IANNE cobrar exigências
     * que ela nunca chegou a ler.
     */
    public function provavelmenteTruncado(): bool
    {
        return $this->extension === 'pdf' && $this->chars >= 20000;
    }
}
