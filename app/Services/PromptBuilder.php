<?php

namespace App\Services;

class PromptBuilder
{
    /**
     * Constrói prompt para análise pedagógica geral
     * @param array $context Contexto estruturado (escola, professor, rede)
     * @param string $question Pergunta do usuário
     * @return string Prompt formatado
     */
    public function buildAnalysisPrompt(array $context, string $question): string
    {
        $prompt = "Você é IANNE (Inteligência Artificial para ANálise Educacional), uma assistente pedagógica especializada em análise de planejamentos escolares da rede pública municipal.\n\n";
        
        $prompt .= "CONTEXTO PEDAGÓGICO:\n";
        $prompt .= $this->formatContext($context);
        
        $prompt .= "\n\nPERGUNTA DO USUÁRIO:\n";
        $prompt .= $question . "\n\n";
        
        $prompt .= "INSTRUÇÕES IMPORTANTES:\n";
        $prompt .= "- Analise SOMENTE com base nos dados fornecidos no contexto\n";
        $prompt .= "- Seja objetivo, claro e direto nas respostas\n";
        $prompt .= "- Use formatação com tópicos e listas quando apropriado\n";
        $prompt .= "- Cite dados específicos do contexto para fundamentar suas análises\n";
        $prompt .= "- Responda em português do Brasil\n";
        $prompt .= "- Se os dados forem insuficientes, indique claramente\n";
        $prompt .= "- CRÍTICO: Retorne APENAS TEXTO PURO, SEM tags HTML como <br>, <b>, <p>, etc.\n";
        $prompt .= "- Use quebras de linha simples (\\n) quando necessário\n";
        $prompt .= "- Seja amigável e profissional\n\n";
        
        $prompt .= "RESPOSTA:\n";
        
        return $prompt;
    }

    /**
     * Constrói prompt para identificação de metodologias
     * @param array $context Contexto do professor/planejamento
     * @return string Prompt formatado
     */
    public function buildMethodologyPrompt(array $context): string
    {
        $prompt = "Você é um especialista em metodologias de ensino e práticas pedagógicas.\n\n";
        
        $prompt .= "DADOS DO PLANEJAMENTO:\n";
        $prompt .= $this->formatContext($context);
        
        $prompt .= "\n\nTAREFA:\n";
        $prompt .= "Identifique as metodologias de ensino utilizadas com base nos planejamentos fornecidos.\n\n";
        
        $prompt .= "FORMATO DA RESPOSTA:\n";
        $prompt .= "Liste as metodologias encontradas em tópicos, incluindo:\n";
        $prompt .= "- Nome da metodologia\n";
        $prompt .= "- Evidências encontradas nos planejamentos\n";
        $prompt .= "- Avaliação da adequação ao contexto\n\n";
        
        return $prompt;
    }

    /**
     * Constrói prompt para validação BNCC
     * @param array $planningData Dados do planejamento
     * @return string Prompt formatado
     */
    public function buildBNCCValidationPrompt(array $planningData): string
    {
        $prompt = "Você é um especialista em Base Nacional Comum Curricular (BNCC) e currículo escolar.\n\n";
        
        $prompt .= "DADOS DO PLANEJAMENTO:\n";
        $prompt .= $this->formatContext($planningData);
        
        $prompt .= "\n\nTAREFA:\n";
        $prompt .= "Valide a correlação entre os objetos de conhecimento e as habilidades BNCC selecionadas.\n\n";
        
        $prompt .= "FORMATO DA RESPOSTA:\n";
        $prompt .= "Organize em seções:\n";
        $prompt .= "✅ CORRETAS: Habilidades adequadas aos objetos de conhecimento\n";
        $prompt .= "⚠️ ATENÇÃO: Habilidades que precisam de ajustes\n";
        $prompt .= "❌ INCORRETAS: Habilidades inadequadas ou de ano/série errada\n";
        $prompt .= "💡 SUGESTÕES: Recomendações de habilidades mais adequadas\n\n";
        
        return $prompt;
    }

    /**
     * Constrói prompt para análise de desempenho
     */
    public function buildPerformancePrompt(array $performanceData, array $planningContext): string
    {
        $prompt = "Você é um analista educacional especializado em avaliação de desempenho e estratégias pedagógicas.\n\n";
        
        $prompt .= "DADOS DE DESEMPENHO:\n";
        $prompt .= $this->formatContext($performanceData);
        
        $prompt .= "\n\nCONTEXTO PEDAGÓGICO:\n";
        $prompt .= $this->formatContext($planningContext);
        
        $prompt .= "\n\nTAREFA:\n";
        $prompt .= "Analise o desempenho dos alunos em relação às estratégias pedagógicas utilizadas.\n\n";
        
        $prompt .= "FORMATO DA RESPOSTA:\n";
        $prompt .= "📊 ANÁLISE DO DESEMPENHO: Descrição dos resultados\n";
        $prompt .= "🔍 ESTRATÉGIAS UTILIZADAS: Metodologias identificadas nos planejamentos\n";
        $prompt .= "⚠️ POSSÍVEIS CAUSAS: Fatores que podem ter influenciado o desempenho\n";
        $prompt .= "💡 SUGESTÕES: Recomendações de melhorias pedagógicas\n\n";
        
        return $prompt;
    }

    /**
     * Formata o contexto em texto legível
     */
    private function formatContext(array $context, int $indent = 0): string
    {
        $formatted = "";
        $spaces = str_repeat("  ", $indent);
        
        foreach ($context as $key => $value) {
            $label = $this->formatLabel($key);
            
            if (is_array($value)) {
                $formatted .= $spaces . "• " . $label . ":\n";
                $formatted .= $this->formatContext($value, $indent + 1);
            } else {
                $formatted .= $spaces . "• " . $label . ": " . $value . "\n";
            }
        }
        
        return $formatted;
    }

    /**
     * Formata labels para melhor legibilidade
     */
    private function formatLabel(string $key): string
    {
        $label = str_replace('_', ' ', $key);
        return ucfirst($label);
    }

    /**
     * Constrói prompt com contexto simplificado
     */
    public function buildSimplePrompt(string $question): string
    {
        return "Você é um assistente pedagógico. Responda a seguinte pergunta de forma clara e objetiva:\n\n" . $question;
    }
}
