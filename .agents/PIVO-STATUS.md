# 📋 PIVO-STATUS.md — Log de Implementação do Pivô (Fase 1)

> **Para agentes de IA:** este arquivo é o log passo a passo da implementação do pivô descrito na **seção 14 do AGENTS.md**. Leia os dois antes de continuar qualquer trabalho. Atualize este arquivo a cada passo concluído.

**Última atualização:** 03/07/2026
**Fase atual:** Fase 1 — Upload inteligente (CONCLUÍDA — publicada em 03/07/2026)

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

## Fase 2 (não iniciada)
Rubrica da SEMED + análise/correção automática pela IA + edição pelo coordenador.

## Fase 3 (não iniciada)
Reescrever estatísticas do `ContextBuilder` e dashboards (sai "enviados/atrasados", entra "corrigidos/metodologias/temas").

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

---

## Histórico passo a passo

| Data | Commit | O que foi feito |
|---|---|---|
| 01/07/2026 | `973909f` | Pivô documentado na seção 14 do AGENTS.md |
| 01/07/2026 | `1f83fdc` | Trabalho pendente (vice-diretores) commitado separado do pivô |
