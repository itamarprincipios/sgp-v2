# Design — Camada Comercial do SGP (3 modelos de venda)

**Data:** 03/07/2026
**Status:** aprovado pelo Itamar em conversa (seções 1–4 validadas individualmente)
**Contexto prévio:** AGENTS.md seção 14 (pivô plataforma de correção) e `.agents/PIVO-STATUS.md`

## 1. Objetivo

Permitir que o SGP seja vendido em três níveis sobre a mesma base de código e a mesma hierarquia Tenant → Escola → Usuários, diferenciados apenas por **quais usuários do tenant recebem login**:

| Plano | O que é | Logins criados |
|---|---|---|
| `coordenador` | Coordenador compra para si | 1 coordenador (+ escola cadastrada sem outros logins) |
| `escola` | Escola compra | Diretor + vice opcional + coordenadores |
| `semed` | Rede municipal (modelo original) | Todos os papéis |

Decisões de negócio confirmadas:

- **Venda manual agora** (WhatsApp/presencial, ativação pelo painel SuperAdmin); self-service com gateway fica para depois, reaproveitando o mesmo provisionador.
- **Cobrança mensal ou vitalícia.** Vitalícia = sem vencimento (`expires_at` null).
- **Assinatura vencida ⇒ modo somente leitura** (vê tudo, não escreve nada, IA desligada), com banner de renovação apontando para o WhatsApp (95) 99124-8941.
- **IA sem limite durante o período de testes**, mas o mecanismo de cota nasce pronto (e já contando uso) para ser ligado quando as vendas começarem — sem deploy, só preenchendo um campo.
- **Sem trial gratuito.** Acesso só depois de pagar.

## 2. Modelo de dados

### 2.1 `tenants` — três colunas novas

| Coluna | Tipo | Significado |
|---|---|---|
| `plan` | `ENUM('coordenador','escola','semed') NOT NULL DEFAULT 'semed'` | O que foi vendido |
| `billing_type` | `ENUM('mensal','vitalicio') NULL` | Como cobra; vitalício mantém `expires_at` null |
| `ai_monthly_limit` | `INT UNSIGNED NULL` | Cota mensal de chamadas de IA; **null = ilimitado** |

Campos existentes que passam a ser respeitados de verdade: `is_active`, `expires_at`, `ai_enabled`, `max_schools_limit`.

**Regra de bloqueio:** tenant em somente leitura quando `is_active = false` OU (`expires_at` não-null E no passado). Renovação = editar `expires_at` na tela de tenants (já existe).

Tenants existentes ficam com `plan = 'semed'` (default) — comportamento de produção inalterado.

### 2.2 `ai_usage` — contador mensal de IA (tabela nova)

```sql
CREATE TABLE ai_usage (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  period CHAR(7) NOT NULL,            -- "2026-07"
  count INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY ai_usage_tenant_period (tenant_id, period)
);
```

### 2.3 ⚠️ SQL já executado em produção

**Ambos os comandos já foram rodados no phpMyAdmin (`u199671261_smartsheets1`) em 03/07/2026:**

```sql
ALTER TABLE tenants
  ADD COLUMN plan ENUM('coordenador','escola','semed') NOT NULL DEFAULT 'semed' AFTER slug,
  ADD COLUMN billing_type ENUM('mensal','vitalicio') NULL AFTER plan,
  ADD COLUMN ai_monthly_limit INT UNSIGNED NULL AFTER ai_enabled;
```

e o `CREATE TABLE ai_usage` acima. A implementação deve criar as migrations correspondentes para o histórico local, mas **elas não podem quebrar se as estruturas já existirem** (guardar com `Schema::hasColumn` / `Schema::hasTable`), pois o autodeploy não roda migrations e o banco de produção já está alterado.

## 3. Middleware de assinatura — `CheckSubscription`

Registrado no grupo de rotas autenticadas de `routes/web.php`, logo após `auth`.

- **Ignora:** `superadmin` (tenant null) e a rota de `logout`.
- **Tenant ativo:** segue sem custo extra (o tenant já vem carregado; só lê dois campos).
- **Tenant bloqueado (somente leitura):**
  - GET passa; uma flag compartilhada com as views acende **banner fixo** em todas as páginas: "Assinatura vencida — modo somente leitura. Fale conosco para renovar" + link WhatsApp.
  - POST/PUT/PATCH/DELETE barrados: formulário tradicional → redirect back com erro; requisição JSON/AJAX (drag-and-drop de correções) → `403` com a mesma mensagem.
  - IA morre naturalmente (endpoints RAG são POST).
- **`ai_enabled`** vira interruptor independente respeitado no `RAGController`: desliga só a IA de um cliente sem bloquear o resto.
- O `TenantScope` não é tocado; o modo somente leitura não altera query nenhuma, apenas recusa métodos de escrita. Rotas de escrita futuras nascem protegidas por estarem no grupo.

## 4. Provisionamento — `TenantProvisioner` + assistente "Nova venda"

### 4.1 Serviço `App\Services\TenantProvisioner`

Método único que cria a venda inteira numa **transação**: tenant → escola (quando o plano tem) → usuários conforme o plano. Retorna as credenciais geradas.

- Senhas temporárias com o gerador existente (sem caracteres ambíguos 0/O, 1/l/I — commit `32e6dfc`).
- `expires_at`: mensal → data informada (default hoje +30 dias); vitalício → null.
- Reutilizável pelo futuro webhook de gateway (self-service): mesmo código, muda só quem chama.

### 4.2 Tela "Nova venda" (painel SuperAdmin)

Item novo no menu do SuperAdmin. Formulário único que se adapta ao plano (Alpine.js):

- **Comuns:** nome do cliente/tenant, plano, tipo de cobrança (+ vencimento se mensal).
- **Plano coordenador:** nome da escola + nome/e-mail do coordenador.
- **Plano escola:** nome da escola + diretor (nome/e-mail) + vice opcional + 1º coordenador opcional (demais o diretor cria pelo fluxo existente).
- **Plano semed:** nome/e-mail do usuário SEMED (escolas/diretores ele cadastra depois, fluxo atual).
- **Checkbox "criar turmas padrão"** (default ligado): controla a auto-criação das 30 turmas (1º–5º ano A–F) do evento `School::created`; desmarcável para clientes de outras etapas.
- Ao concluir: **tela de resumo com credenciais, exibida uma única vez**, com botão copiar (para colar no WhatsApp do cliente).

## 5. Cota de IA (preparada, desligada)

Ponto único de enforcement: **`AIService`** (todas as chamadas Gemini — RAG, inferência de metadados, extração de PDF — passam por ele).

1. **Contador:** incrementa `ai_usage` (tenant + mês corrente) a cada chamada, **desde já, mesmo sem cota** — gera dado real de consumo para precificar planos ao fim do período de testes.
2. **Checagem antes da chamada:** `ai_monthly_limit` null → segue (estado atual de todos). Com número e mês esgotado → chamada não acontece:
   - RAG responde "cota mensal de IA atingida; renove ou aguarde o próximo mês".
   - Upload de correção: **documento entra mesmo assim**, apenas sem inferência automática; coordenador preenche metadados manualmente (fluxo que já existe).

Ligar a cota = preencher `ai_monthly_limit` na tela de editar tenant. Sem deploy.

## 6. Comportamento consolidado por plano

| | Coordenador | Escola | SEMED |
|---|---|---|---|
| Quem loga | só o coordenador | diretor, vice, coordenadores | todos os papéis |
| Correções + IA/RAG | ✔ escopo da sua escola | ✔ escopo da sua escola | ✔ rede toda |
| Cadastro de professores/turmas | o próprio coordenador | coordenadores/diretor | escolas fazem |
| Vencido | somente leitura + banner | idem | idem |
| Vitalício | sem vencimento; cota de IA mensal ainda aplicável | idem | idem |

## 7. Fora de escopo (adicionável depois sem retocar o design)

- Tela "minha assinatura" para o cliente, nota fiscal, e-mail automático de aviso de vencimento (venda e cobrança manuais; o banner com WhatsApp é o lembrete).
- Gateway de pagamento / signup self-service.

**Exceção dentro do escopo:** o `/register` público do Breeze hoje cria usuário órfão (sem role/tenant/escola) — ele **será desativado nesta implementação** (item 4 da ordem abaixo) e só volta no futuro como onboarding pago.
- Histórico financeiro de renovações (Abordagem B, descartada por ora).

## 8. Ordem de implementação sugerida

1. Migrations guardadas (banco de produção já alterado) + model `Tenant` atualizado.
2. Middleware `CheckSubscription` + banner no layout + respeito ao `ai_enabled` no RAG.
3. Contador `ai_usage` + checagem de cota no `AIService`.
4. `TenantProvisioner` + tela "Nova venda" + desativação do `/register` público.
5. `npm run build` + push (autodeploy) a cada etapa concluída, com SQL adicional se surgir.
