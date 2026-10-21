<?php

namespace Tests\Unit;

use App\Services\DocumentKinds;
use App\Services\PromptSettings;
use PHPUnit\Framework\TestCase;

/**
 * Catálogo de tipos de documento da Central de Correção. O tipo manda na
 * rubrica do parecer, então o que está testado aqui é justamente o que não pode
 * falhar em silêncio: documento antigo (sem doc_kind) continuar sendo lido,
 * tipo inválido não vazar, e todo tipo ter rubrica.
 */
class DocumentKindsTest extends TestCase
{
    public function test_tipo_valido_e_devolvido_como_esta(): void
    {
        $this->assertSame('pei', DocumentKinds::resolve('pei'));
        $this->assertSame('relatorio_educacao_infantil', DocumentKinds::resolve('relatorio_educacao_infantil', 'relatorio'));
    }

    public function test_documento_anterior_ao_catalogo_cai_no_balde_antigo(): void
    {
        // doc_kind NULL (registro de antes da migração) → decide pelo `type`
        $this->assertSame('planejamento', DocumentKinds::resolve(null, 'planejamento'));
        $this->assertSame('relatorio_professor', DocumentKinds::resolve(null, 'relatorio'));
        $this->assertSame('outro', DocumentKinds::resolve(null, 'outro'));
        $this->assertSame('planejamento', DocumentKinds::resolve(null, null));
    }

    public function test_tipo_desconhecido_nao_vaza_para_o_resto_do_sistema(): void
    {
        $this->assertSame('planejamento', DocumentKinds::resolve('plano_de_fuga'));
        $this->assertFalse(DocumentKinds::exists('plano_de_fuga'));
        $this->assertFalse(DocumentKinds::exists(null));
        $this->assertTrue(DocumentKinds::exists('relatorio_aluno_especial'));
    }

    public function test_todo_tipo_tem_balde_valido_na_coluna_antiga(): void
    {
        foreach (DocumentKinds::keys() as $kind) {
            $this->assertContains(
                DocumentKinds::legacyType($kind),
                ['planejamento', 'relatorio', 'outro'],
                "Tipo {$kind} mapeia para um enum que documents.type não aceita"
            );
        }
    }

    public function test_todo_tipo_tem_persona_criterios_e_erros_graves(): void
    {
        $padroes = PromptSettings::defaults();

        foreach (DocumentKinds::keys() as $kind) {
            $def = DocumentKinds::get($kind);
            $rubrica = DocumentKinds::rubrica($kind, $padroes);

            // Planejamento usa a persona editável (parecer_persona); os demais
            // trazem a própria, fixa no catálogo.
            if ($kind !== 'planejamento') {
                $this->assertNotEmpty($def['persona'], "Tipo {$kind} sem persona");
            }

            $this->assertNotEmpty($rubrica['criterios'], "Tipo {$kind} sem critérios");
            $this->assertNotEmpty($rubrica['erros'], "Tipo {$kind} sem erros graves");
        }
    }

    public function test_rubrica_usa_o_texto_personalizado_do_municipio(): void
    {
        $blocos = PromptSettings::defaults();
        $blocos['doc_pei_criterios'] = 'critério próprio desta rede';

        $rubrica = DocumentKinds::rubrica('pei', $blocos);

        $this->assertSame('critério próprio desta rede', $rubrica['criterios']);
        $this->assertSame($blocos['doc_pei_erros'], $rubrica['erros']);
    }

    public function test_tipo_sem_blocos_editaveis_cai_na_rubrica_generica(): void
    {
        $rubrica = DocumentKinds::rubrica('outro', PromptSettings::defaults());

        $this->assertNotEmpty($rubrica['criterios']);
        $this->assertNotEmpty($rubrica['erros']);
    }

    public function test_lista_do_prompt_descreve_todos_os_tipos(): void
    {
        $lista = DocumentKinds::promptList();

        foreach (DocumentKinds::keys() as $kind) {
            $this->assertStringContainsString("\"{$kind}\"", $lista, "Tipo {$kind} não aparece no prompt da inferência");
        }
    }

    public function test_catalogo_do_frontend_cobre_todos_os_tipos_e_campos(): void
    {
        $frontend = DocumentKinds::forFrontend();

        $this->assertSame(DocumentKinds::keys(), array_keys($frontend));

        foreach ($frontend as $kind => $info) {
            foreach (['label', 'badge', 'pede_aluno', 'pede_disciplina', 'pede_aulas', 'usa_calendario'] as $campo) {
                $this->assertArrayHasKey($campo, $info, "Tipo {$kind} sem {$campo} para a tela");
            }
        }
    }

    public function test_aluno_e_pedido_apenas_nos_documentos_individuais(): void
    {
        foreach (['pei', 'relatorio_aluno_especial', 'relatorio_educacao_infantil'] as $kind) {
            $this->assertTrue(DocumentKinds::get($kind)['pede_aluno'], "Tipo {$kind} deveria pedir o aluno");
        }

        foreach (['planejamento', 'relatorio_professor'] as $kind) {
            $this->assertFalse(DocumentKinds::get($kind)['pede_aluno'], "Tipo {$kind} não deveria pedir aluno");
        }
    }

    public function test_calendario_e_numero_de_aulas_sao_so_do_planejamento(): void
    {
        foreach (DocumentKinds::keys() as $kind) {
            $esperado = $kind === 'planejamento';
            $def = DocumentKinds::get($kind);

            $this->assertSame($esperado, $def['usa_calendario'], "Regras de calendário erradas em {$kind}");
            $this->assertSame($esperado, $def['pede_aulas'], "Pergunta de nº de aulas errada em {$kind}");
        }
    }
}
