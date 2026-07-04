# 📋 PIVO-STATUS.md — Log de Implementação do Pivô (Fase 1)

> **Para agentes de IA:** este arquivo é o log passo a passo da implementação do pivô descrito na **seção 14 do AGENTS.md**. Leia os dois antes de continuar qualquer trabalho. Atualize este arquivo a cada passo concluído.

**Última atualização:** 04/07/2026
**Fase atual:** Fases 1 e 2 CONCLUÍDAS — próxima: Fase 3 (diagnóstico da rede)

---

## Checklist da Fase 1

- [x] Commitar trabalho pendente anterior ao pivô (vice-diretores) — commit `1f83fdc`
- [x] Migração do `documents` (novos campos + SQL para produção)
- [x] Atualizar model `Document` (fillable + relações)
- [x] `AIService`: modo JSON + envio de arquivo inline (PDF multimodal)
- [x] `DocumentExtractor`: suporte a PDF (via Gemini multimodal)
- [x] Novo serviço `MetadataInference` (infere professor/turma/disciplina/período do texto)
- [x] Novo `CorrectionController` (index, store multi-arquivo, confirm, destroy)
- [x] Rotas `/school/correcoes*`
- [x] View `school/corrections.blade.php` (drag-and-drop + confirmação de metadados)
- [x] Sidebar: substituir "Cronogramas (Prazos)" e "Envios de Planejamentos" por "Correções"
- [x] `npm run build` + push final

## Fase 2 — Correção assistida (CONCLUÍDA em 04/07/2026)

- [x] `PlanAnalyzer` gera parecer da IANNE com rubrica de 10 critérios — commit `da07786`
- [x] Botão "Analisar" + diálogo (vigência, nº de aulas, observações) + parecer editável pelo coordenador
- [x] Campos `documents.analysis` / `documents.analyzed_at` (migration `2026_07_04_000002`, guardada com `hasColumn`)
- [x] Provedor de IA trocado de Gemini para **Claude (Anthropic)** — commit `a15894e`. `AIService` lê via `config()`.
- [x] Professor auto-criado a partir do documento (`new_professor_name` no confirm; `users.email` agora é NULLABLE — professor sem login tem email NULL, decisão de design, NÃO usar placeholder) — commits `0610649`, migration `2026_07_04_000001`
- [x] Card de correção mostra motivo quando a IANNE falha (`ai_error`) — commit `b87bfe1`
- [x] **Refinamento da inferência (04/07/2026, commit `95ed21e`):** planos são QUINZENAIS. Documento com 2+ componentes → disciplina "Polivalente" (professor titular dos anos iniciais, ex: Ana Cristina); 1 componente → nome dele (ex: "Educação Física", professor que atende 2º-5º). Título gerado a partir das datas de aplicação extraídas do documento ("Planejamento 12/05 a 23/05 — 3º Ano B"). DCRR do polivalente cobre os 7 componentes do titular (LP, MAT, CIE, HIS, GEO, ER, Arte), teto 42k chars.
- [x] **Etapa B concluída (04/07/2026): DCRR integrado ao parecer.** O DCRR completo (586 páginas, PDF oficial fornecido pelo Itamar) foi fatiado em **69 arquivos** por componente/ano em `resources/dcrr/*.txt`. O serviço `DcrrLibrary::excerptFor(discipline, className)` seleciona a seção certa (ex: `matematica_3.txt` para Matemática do 3º ano; núcleo comum de 5 componentes para polivalente; `educacao_infantil.txt` para turmas Pré/Maternal) com teto de 36k chars, e o `CorrectionController::analyze` injeta no contexto `dcrr` do `PlanAnalyzer`. Sem turma/ano identificável → `null` → parecer declara "não verificado" (nunca inventa). O PDF original NÃO está no repo (5,9MB); só o texto fatiado.

## Fase 3 (não iniciada)
Reescrever estatísticas do `ContextBuilder` e dashboards (sai "enviados/atrasados", entra "corrigidos/metodologias/temas").

## Camada comercial (implementada em 03/07/2026, fora do plano original)
3 modelos de venda (coordenador individual / escola / SEMED), wizard "Nova Venda" no SuperAdmin, `TenantProvisioner`, middleware de assinatura com modo somente leitura, contador mensal de uso de IA (`ai_usage` + `AiQuota`), registro público desativado. Spec: `docs/superpowers/specs/2026-07-03-camada-comercial-design.md`.

---

## Decisões de arquitetura tomadas (NÃO REVERTER sem motivo)

1. **Fluxo antigo fica dormente, não deletado.** Rotas de cronograma (`school.planning.*`) e upload do professor (`professor.documents.store`) continuam registradas (blades antigas referenciam `route()` e quebrariam). Elas apenas saem do menu lateral.
2. **`documents.user_id` = professor dono do documento** (mantém compatibilidade com `ContextBuilder`); novo campo `uploaded_by` = coordenador que subiu o arquivo. Ambos nullable.
3. **`period_id` vira nullable**; novo campo `reference_label` (texto livre, ex: "1º Bimestre 2026") substitui o vínculo obrigatório com cronograma.
4. **Novos status do documento:** `aguardando_confirmacao` (subiu, IA inferiu metadados, coordenador precisa confirmar) → `em_correcao` (confirmado) → `corrigido` (Fase 2). Status antigos permanecem no enum.
5. **PDF é extraído via Gemini multimodal** (inline base64), porque o shared hosting não permite instalar libs de PDF via composer com facilidade. DOCX continua com ZipArchive local (grátis).
6. **Professor deixa de precisar de login** — vira cadastro para vincular documentos. Login antigo continua funcionando (dormente), apenas não se cria mais credenciais.
7. **Inferência de metadados:** 1 chamada Gemini por documento com modo JSON, recebendo o texto extraído (primeiros ~4000 chars) + lista de professores/turmas da escola para matching. Coordenador SEMPRE confirma antes do documento valer.

---

## ⚠️ SQL PENDENTE DE EXECUÇÃO EM PRODUÇÃO (phpMyAdmin)

> O autodeploy da Hostinger NÃO roda migrations. Todo ALTER TABLE precisa ser executado manualmente no phpMyAdmin do banco `u199671261_smartsheets1`.

### 1. Vice-diretores (commit `1f83fdc` — verificar se já foi executado antes de rodar)

```sql
ALTER TABLE users MODIFY role ENUM('superadmin','admin','semed','director','vice_director','coordinator','professor','supervisor_edfis','supervisor_monitor','supervisor_infantil','supervisor_fundamental') NOT NULL;

ALTER TABLE schools DROP COLUMN director_name, DROP COLUMN director_phone;
```

### 2. Pivô Fase 1 — documents (passado ao Itamar em 03/07/2026 para execução antes do push)

```sql
ALTER TABLE documents MODIFY user_id BIGINT UNSIGNED NULL;
ALTER TABLE documents MODIFY period_id BIGINT UNSIGNED NULL;
ALTER TABLE documents MODIFY status ENUM('pendente','enviado','atrasado','aprovado','rejeitado','ajustado','aguardando_confirmacao','em_correcao','corrigido') NOT NULL DEFAULT 'enviado';
ALTER TABLE documents MODIFY type ENUM('planejamento','relatorio','outro') NOT NULL;

ALTER TABLE documents
  ADD COLUMN uploaded_by BIGINT UNSIGNED NULL AFTER user_id,
  ADD COLUMN school_id BIGINT UNSIGNED NULL AFTER uploaded_by,
  ADD COLUMN class_id BIGINT UNSIGNED NULL AFTER period_id,
  ADD COLUMN discipline VARCHAR(100) NULL AFTER title,
  ADD COLUMN reference_label VARCHAR(100) NULL AFTER discipline,
  ADD CONSTRAINT documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
  ADD CONSTRAINT documents_school_id_foreign FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL,
  ADD CONSTRAINT documents_class_id_foreign FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL;
```

### 3. Camada comercial (JÁ EXECUTADO em 03/07/2026 — colunas de tenants + tabela ai_usage)

Ver spec `docs/superpowers/specs/2026-07-03-camada-comercial-design.md` §2.3.

### 4. Professor sem login (JÁ EXECUTADO em 04/07/2026, conforme comentário na migration `2026_07_04_000001`)

```sql
ALTER TABLE users MODIFY email VARCHAR(255) NULL;
```

### 4b. Limpeza de professores duplicados (gerados pelo bug corrigido no commit `d3a3640` — rodar UMA vez, na ordem)

```sql
-- 1º) Reapontar documentos para o professor mais antigo de cada nome/escola
--     (OBRIGATÓRIO antes do DELETE: documents.user_id tem ON DELETE CASCADE)
UPDATE documents d
JOIN users u ON u.id = d.user_id
JOIN (
    SELECT MIN(id) AS keep_id, name, school_id
    FROM users WHERE role = 'professor'
    GROUP BY name, school_id
) k ON k.name = u.name AND k.school_id = u.school_id
SET d.user_id = k.keep_id
WHERE u.role = 'professor' AND d.user_id <> k.keep_id;

-- 2º) Aproveitar turma de alguma duplicata se o mantido não tiver
UPDATE users keep
JOIN (
    SELECT MIN(id) AS keep_id, MAX(class_id) AS class_id
    FROM users WHERE role = 'professor'
    GROUP BY name, school_id HAVING COUNT(*) > 1
) k ON keep.id = k.keep_id
SET keep.class_id = COALESCE(keep.class_id, k.class_id);

-- 3º) Excluir as duplicatas (mantém o id mais antigo)
DELETE u FROM users u
JOIN (
    SELECT MIN(id) AS keep_id, name, school_id
    FROM users WHERE role = 'professor'
    GROUP BY name, school_id HAVING COUNT(*) > 1
) k ON k.name = u.name AND k.school_id = u.school_id
WHERE u.role = 'professor' AND u.id <> k.keep_id;
```

### 5. Fase 2 — parecer da IANNE (VERIFICAR se já foi executado; commit `da07786` diz "requer SQL")

```sql
ALTER TABLE documents
  ADD COLUMN analysis LONGTEXT NULL AFTER reference_label,
  ADD COLUMN analyzed_at TIMESTAMP NULL AFTER analysis;
```

> Se o botão "Analisar" já funciona em produção, este SQL já foi executado. Conferir com `SHOW COLUMNS FROM documents LIKE 'analysis';` antes de rodar.

---

## Histórico passo a passo

| Data | Commit | O que foi feito |
|---|---|---|
| 01/07/2026 | `973909f` | Pivô documentado na seção 14 do AGENTS.md |
| 01/07/2026 | `1f83fdc` | Trabalho pendente (vice-diretores) commitado separado do pivô |
| 03/07/2026 | `22ed755` | **Fase 1 completa**: upload inteligente com drag-and-drop multi-arquivo, inferência de metadados, PDF multimodal |
| 03/07/2026 | `08f266b`…`4221a8f` | Camada comercial: 3 planos, wizard Nova Venda, `TenantProvisioner`, middleware de assinatura, cota de IA, registro público desativado |
| 03/07/2026 | `7140395`, `3308444` | Login redireciona ao painel correto; página Segurança da Conta para diretor/vice/coordenador |
| 04/07/2026 | `0610649` | Professor auto-criado do documento (email NULL, sem login) + visão de docs por professor |
| 04/07/2026 | `ebafa59`, `b87bfe1` | Fix ParseError no blade de correções; card mostra motivo quando a IANNE falha |
| 04/07/2026 | `e01ce70`, `a15894e` | `AIService` via `config()`; **troca de provedor Gemini → Claude (Anthropic)** |
| 04/07/2026 | `da07786`, `c7a4502` | **Fase 2 completa**: parecer da IANNE (rubrica de 10 critérios), botão Analisar + diálogo, parecer editável; fix das aspas no `@click` |
| 04/07/2026 | `13d6bd5`+`70283fa` | Fix indevido de email placeholder aplicado e revertido (email NULL é design — ver migration `2026_07_04_000001`) |
