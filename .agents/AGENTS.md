# 🤖 AGENTS.md — Contexto Completo para Agentes de IA

> **LEIA ESTE ARQUIVO INTEIRAMENTE ANTES DE FAZER QUALQUER ALTERAÇÃO NO CÓDIGO.**
> Este documento contém todas as informações necessárias para que qualquer agente de IA continue a implementação do projeto SGP sem erros, duplicações ou perda de contexto.

---

## 1. Visão Geral do Projeto

O **SGP (Sistema de Gestão Pedagógica)** é uma plataforma web para gerenciamento e acompanhamento do desempenho pedagógico de redes municipais de ensino. Ele permite que a **Secretaria Municipal de Educação (SEMED)**, **diretores**, **coordenadores pedagógicos** e **professores** gerenciem cronogramas de planejamento, envio de planos de aula, feedbacks, rankings gamificados e relatórios.

**Desenvolvedor:** N Circuits Technologies  
**Contato:** (95) 99124-8941 (WhatsApp)

---

## 2. Stack Tecnológica

| Componente       | Tecnologia                     |
|------------------|--------------------------------|
| **Framework**    | Laravel 10.x (PHP 8.3)        |
| **Frontend**     | Blade Templates + Tailwind CSS |
| **Build Tool**   | Vite 4.x                      |
| **Banco de Dados** | MySQL 8.x                   |
| **Fonte Padrão** | Google Fonts — Inter           |
| **Componentes JS** | Alpine.js (embutido no app.js) |
| **Auth**         | Laravel Breeze (Blade)         |
| **Multitenancy** | Trait customizado `BelongsToTenant` |

---

## 3. Deploy e Infraestrutura

### 🚀 AUTO DEPLOY ATIVO VIA GITHUB

- **Repositório:** `https://github.com/itamarprincipios/sgp-v2.git`
- **Branch de produção:** `main`
- **Servidor:** Hostinger (Shared Hosting com PHP 8.3 e MySQL)
- **Domínio temporário:** `https://frequenciasmart-cloud-133049.hostingersite.com`

### ⚠️ REGRAS CRÍTICAS DE DEPLOY

1. **Todo push para `main` sobe automaticamente para produção.** Não faça push de código quebrado ou incompleto.
2. **A pasta `public/build/` está NO Git** (removida do `.gitignore`). Sempre execute `npm run build` antes de fazer push para atualizar os assets compilados (CSS/JS).
3. **O arquivo `.env` NÃO está no Git** (e NÃO deve ser incluído). Ele é configurado manualmente no servidor.
4. **NUNCA comite arquivos sensíveis** (dumps SQL, senhas, chaves de API) no repositório.
5. **A pasta `vendor/` NÃO está no Git.** O `composer install` precisa ser executado no servidor ou os arquivos enviados manualmente.

### 📦 Fluxo de Deploy

```
1. Desenvolver localmente
2. npm run build  (compilar CSS/JS do Tailwind/Vite)
3. git add . && git commit -m "descrição" && git push origin main
4. O deploy automático da Hostinger sincroniza os arquivos
```

### 🗄️ Banco de Dados de Produção

| Campo        | Valor                        |
|--------------|------------------------------|
| Host         | 127.0.0.1                   |
| Porta        | 3306                         |
| Banco        | u199671261_smartsheets1      |
| Usuário      | u199671261_smart1            |

> A senha está configurada no `.env` do servidor. Não a inclua neste arquivo.

---

## 4. Estrutura de Diretórios Relevantes

```
sgp-v2/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php    # Dashboards de todos os perfis
│   │   │   ├── DocumentController.php     # Upload e extração de .docx
│   │   │   ├── SchoolController.php       # CRUD completo da gestão escolar
│   │   │   ├── SuperAdminController.php   # Painel SaaS (gerenciar tenants)
│   │   │   ├── RAGController.php          # Endpoints da IA (IANNE)
│   │   │   └── ProfileController.php      # Perfil do usuário (Breeze)
│   │   └── Middleware/
│   │       └── RoleMiddleware.php         # Controle de acesso por role
│   ├── Models/
│   │   ├── User.php           # Usuário (com role, school_id, class_id)
│   │   ├── Tenant.php         # Inquilino SaaS (prefeitura/município)
│   │   ├── School.php         # Escola (auto-cria 30 turmas no boot)
│   │   ├── SchoolClass.php    # Turma (1º ao 5º Ano, A-F)
│   │   ├── Period.php         # Período/Cronograma de planejamento
│   │   ├── Document.php       # Planejamento enviado (.docx)
│   │   ├── Notice.php         # Avisos oficiais da SEMED
│   │   ├── UserMedal.php      # Medalhas de gamificação
│   │   └── AiQuery.php        # Histórico de consultas à IA
│   └── Traits/
│       └── BelongsToTenant.php  # Trait de multitenancy (filtra por tenant_id)
├── database/
│   ├── migrations/            # 13 migrations (tenants, schools, classes, users, periods, documents, medals, ai_queries, notices, etc.)
│   └── seeders/
│       └── DatabaseSeeder.php # Seeder completo com dados de exemplo
├── resources/views/
│   ├── welcome.blade.php      # Homepage pública (landing page)
│   ├── layouts/
│   │   ├── app.blade.php      # Layout principal (sidebar + topbar)
│   │   ├── guest.blade.php    # Layout de login/registro
│   │   └── sidebar-menu.blade.php  # Menu lateral dinâmico por role
│   ├── dashboard/
│   │   ├── school.blade.php          # Dashboard do Diretor/Coordenador
│   │   ├── professor.blade.php       # Dashboard do Professor
│   │   ├── semed.blade.php           # Dashboard da SEMED (rankings)
│   │   ├── admin.blade.php           # Dashboard do Admin
│   │   ├── superadmin.blade.php      # Dashboard do SuperAdmin (SaaS)
│   │   ├── supervisor_edfis.blade.php    # Dashboard Supervisor Ed. Física
│   │   └── supervisor_monitor.blade.php  # Dashboard Supervisor de Monitores
│   └── school/
│       ├── plannings.blade.php        # Lista de cronogramas
│       ├── planning_create.blade.php  # Criar cronograma
│       ├── planning_edit.blade.php    # Editar cronograma
│       ├── planning_detail.blade.php  # Detalhe com avaliação de envios
│       ├── classes.blade.php          # Gestão de turmas
│       ├── professors.blade.php       # Gestão de professores
│       ├── coordinator_edit.blade.php # Editar coordenador
│       └── reports.blade.php          # Relatórios A4 (impressão)
├── public/
│   ├── build/                 # ⚠️ Assets compilados (NO GIT, não deletar)
│   ├── images/
│   │   └── ncircuits-logo.png # Logo da N Circuits Technologies
│   └── .htaccess              # Redirecionamento Apache (Laravel public)
├── .htaccess                  # Redirecionamento raiz → public/
└── .env.example               # Template de variáveis de ambiente
```

---

## 5. Sistema de Roles (Papéis de Usuário)

O controle de acesso é feito pelo middleware `RoleMiddleware` registrado como `role` no Kernel. Ele verifica o campo `role` da tabela `users`.

| Role                 | Descrição                                        | Dashboard Path              |
|----------------------|--------------------------------------------------|-----------------------------|
| `superadmin`         | Administrador SaaS (gerencia tenants/prefeituras) | `/superadmin/dashboard`     |
| `admin`              | Administrador do tenant (visão geral)             | `/admin/dashboard`          |
| `semed`              | Equipe da Secretaria de Educação                  | `/semed/dashboard`          |
| `director`           | Diretor de escola (gerencia coordenadores)        | `/school/dashboard`         |
| `vice_director`      | Vice-diretor de escola (mesmo acesso do director) | `/school/dashboard`         |
| `coordinator`        | Coordenador pedagógico (gerencia professores)     | `/school/dashboard`         |
| `professor`          | Professor (envia planejamentos .docx)             | `/professor/dashboard`      |
| `supervisor_edfis`   | Supervisor de Educação Física                     | `/supervisor-edfis/dashboard` |
| `supervisor_monitor` | Supervisor de Monitores                           | `/supervisor-monitor/dashboard` |

### Hierarquia de Permissões
- **Director e Vice-Director** têm acesso idêntico: podem criar/editar/deletar coordenadores e resetar senhas de coordenadores e professores. Diretor e Vice-Diretor são cadastrados pela SEMED (não pela escola), um por escola cada (vice-diretor é opcional).
- **Director, Vice-Director e Coordinator** compartilham acesso às rotas de gestão escolar (turmas, professores, cronogramas, envios).
- **Professor** só acessa seu próprio dashboard e pode enviar documentos `.docx`.
- **SEMED** visualiza métricas consolidadas de todas as escolas da rede.
- **SuperAdmin** gerencia os tenants (municípios) do sistema SaaS.

### Senhas Padrão (Reset)
- Coordenador: `coord123`
- Professor: `professor123`
- Seeder (todos): `senha123`

---

## 6. Rotas Completas (routes/web.php)

### Públicas
| Método | URI        | Ação                      |
|--------|------------|---------------------------|
| GET    | `/`        | Homepage (`welcome.blade.php`) |
| GET    | `/login`   | Tela de login (Breeze)    |
| GET    | `/register`| Tela de registro (Breeze) |

### Autenticadas — Dashboards
| Método | URI                            | Controller@Method               | Role(s)                    |
|--------|--------------------------------|---------------------------------|----------------------------|
| GET    | `/dashboard`                   | DashboardController@index       | Todos (redireciona)        |
| GET    | `/admin/dashboard`             | DashboardController@admin       | admin                      |
| GET    | `/superadmin/dashboard`        | SuperAdminController@index      | superadmin                 |
| GET    | `/semed/dashboard`             | DashboardController@semed       | semed                      |
| GET    | `/school/dashboard`            | DashboardController@school      | director, vice_director, coordinator |
| GET    | `/professor/dashboard`         | DashboardController@professor   | professor                  |
| GET    | `/supervisor-edfis/dashboard`  | DashboardController@supervisorEdfis | supervisor_edfis       |
| GET    | `/supervisor-monitor/dashboard`| DashboardController@supervisorMonitor | supervisor_monitor   |

### Autenticadas — Gestão Escolar (director, coordinator)
| Método | URI                             | Controller@Method                    | Nome da Rota                |
|--------|---------------------------------|--------------------------------------|-----------------------------|
| GET    | `/school/plannings`             | SchoolController@plannings           | school.plannings            |
| GET    | `/school/planning/create`       | SchoolController@createPlanning      | school.planning.create      |
| POST   | `/school/planning`              | SchoolController@storePlanning       | school.planning.store       |
| GET    | `/school/planning/view`         | SchoolController@viewPlanning        | school.planning.view        |
| GET    | `/school/planning/edit`         | SchoolController@editPlanning        | school.planning.edit        |
| PUT    | `/school/planning`              | SchoolController@updatePlanning      | school.planning.update      |
| DELETE | `/school/planning`              | SchoolController@deletePlanning      | school.planning.delete      |
| GET    | `/school/classes`               | SchoolController@classes             | school.classes              |
| POST   | `/school/class`                 | SchoolController@storeClass          | school.class.store          |
| GET    | `/school/class/edit`            | SchoolController@editClass           | school.class.edit           |
| PUT    | `/school/class`                 | SchoolController@updateClass         | school.class.update         |
| DELETE | `/school/class`                 | SchoolController@deleteClass         | school.class.delete         |
| GET    | `/school/professors`            | SchoolController@professors          | school.professors           |
| POST   | `/school/professor`             | SchoolController@storeProfessor      | school.professor.store      |
| GET    | `/school/professor/edit`        | SchoolController@editProfessor       | school.professor.edit       |
| PUT    | `/school/professor`             | SchoolController@updateProfessor     | school.professor.update     |
| DELETE | `/school/professor`             | SchoolController@deleteProfessor     | school.professor.delete     |
| POST   | `/school/professor/reset-password` | SchoolController@resetProfessorPassword | school.professor.reset-password |
| POST   | `/school/document/review`       | SchoolController@reviewDocument      | school.document.review      |
| POST   | `/school/planning/bimester`     | SchoolController@associateToBimester | school.planning.bimester    |
| POST   | `/school/password/change`       | SchoolController@changePassword      | school.password.change      |
| POST   | `/school/photo/upload`          | SchoolController@uploadPhoto         | school.photo.upload         |
| POST   | `/school/uploads/viewed`        | SchoolController@markUploadsAsViewed | school.uploads.viewed       |
| GET    | `/school/reports`               | SchoolController@reports             | school.reports              |

### Autenticadas — Apenas Diretor
| Método | URI                               | Controller@Method                      | Nome da Rota                    |
|--------|-----------------------------------|----------------------------------------|---------------------------------|
| POST   | `/school/coordinator`             | SchoolController@storeCoordinator      | school.coordinator.store        |
| GET    | `/school/coordinator/edit`        | SchoolController@editCoordinator       | school.coordinator.edit         |
| PUT    | `/school/coordinator`             | SchoolController@updateCoordinator     | school.coordinator.update       |
| POST   | `/school/coordinator/reset-password` | SchoolController@resetCoordinatorPassword | school.coordinator.reset-password |
| DELETE | `/school/coordinator`             | SchoolController@deleteCoordinator     | school.coordinator.delete       |

### Autenticadas — Professor
| Método | URI                    | Controller@Method             | Nome da Rota               |
|--------|------------------------|-------------------------------|----------------------------|
| POST   | `/professor/documents` | DocumentController@store      | professor.documents.store  |

### Autenticadas — SuperAdmin (SaaS)
| Método | URI                                    | Controller@Method                          |
|--------|----------------------------------------|--------------------------------------------|
| GET    | `/superadmin/tenants`                  | SuperAdminController@tenants               |
| GET    | `/superadmin/tenants/create`           | SuperAdminController@tenantsCreate         |
| POST   | `/superadmin/tenants`                  | SuperAdminController@tenantsStore          |
| GET    | `/superadmin/tenants/{tenant}/edit`    | SuperAdminController@tenantsEdit           |
| PUT    | `/superadmin/tenants/{tenant}`         | SuperAdminController@tenantsUpdate         |
| PATCH  | `/superadmin/tenants/{tenant}/toggle`  | SuperAdminController@tenantsToggleStatus   |

### IA (IANNE) — Endpoints
| Método | URI             | Controller@Method        |
|--------|-----------------|--------------------------|
| POST   | `/api/rag`      | RAGController@query      |
| GET    | `/api/rag`      | RAGController@history    |

---

## 7. Modelos e Relacionamentos

### Tenant (Inquilino/Município)
- `hasMany` → School, User, Period
- Campos: `name`, `slug`, `is_active`, `ai_enabled`, `max_schools_limit`, `expires_at`

### School (Escola)
- `hasMany` → User, SchoolClass, Period
- `hasOne` → User (`director()`, role='director'), User (`viceDirector()`, role='vice_director')
- Usa trait `BelongsToTenant`
- **Evento `created`:** Auto-cria 30 turmas (1º-5º Ano × A-F)
- Campos: `tenant_id`, `name`, `inep_code`, `address`
- Diretor(a)/Vice-Diretor(a) não são campos da escola — são usuários (role `director`/`vice_director`) vinculados via `school_id`, cadastrados separadamente no painel SEMED

### User (Usuário)
- `belongsTo` → School, SchoolClass (class_id), SchoolClass (monitor_class_id)
- `belongsToMany` → School (via pivot `user_schools`)
- `hasMany` → Document, UserMedal, AiQuery, Notice (sent/received)
- Usa trait `BelongsToTenant`
- Método `dashboardPath()`: retorna o caminho do dashboard baseado na role
- Campos: `tenant_id`, `school_id`, `class_id`, `monitor_class_id`, `name`, `email`, `password`, `role`, `whatsapp`, `is_physical_education`, `is_monitor`, `is_first_grade`, `profile_photo`

### Period (Período/Cronograma)
- `belongsTo` → School (nullable, null = período global da rede)
- `hasMany` → Document
- Usa trait `BelongsToTenant`
- Campos: `tenant_id`, `school_id`, `name`, `description`, `bimester`, `start_date`, `end_date`, `deadline`, `opening_date`, `is_active`, `is_physical_education`

### Document (Planejamento)
- `belongsTo` → User, Period
- Usa trait `BelongsToTenant`
- Campos: `tenant_id`, `user_id`, `period_id`, `title`, `type`, `file_path`, `content_text`, `content_extracted_at`, `status`, `feedback`, `score_base`, `penalty_delay`, `penalty_resubmission`, `score_final`, `rejection_count`, `rejected_at`, `submitted_at`
- Status possíveis: `pending`, `approved`, `rejected`, `adjusted`

### SchoolClass (Turma)
- `belongsTo` → School
- Campos: `school_id`, `name`

### Notice (Aviso Oficial)
- `belongsTo` → User (sender_id), User (recipient_id)
- Usa trait `BelongsToTenant`

### UserMedal (Medalha)
- `belongsTo` → User
- Campos: `user_id`, `type`, `title`, `description`

### AiQuery (Consulta IA)
- `belongsTo` → User
- Campos: `user_id`, `query`, `response`, `context_type`

---

## 8. Multitenancy

O sistema utiliza **multitenancy por coluna** (`tenant_id`). O trait `BelongsToTenant` (em `app/Traits/BelongsToTenant.php`) aplica um scope global que filtra automaticamente todos os registros pelo `tenant_id` do usuário logado.

**Modelos com multitenancy:** User, School, SchoolClass (via School), Period, Document, Notice, AiQuery, UserMedal.

**SuperAdmin** (`tenant_id = null`) não é filtrado pelo scope — ele vê todos os tenants.

---

## 9. Padrões de Design e Convenções

### Estilização
- **Paleta:** Slate-900 (fundo escuro), Indigo-400/500/600 (primária), Emerald/Amber/Rose (status)
- **Fonte:** Inter (Google Fonts), carregada em todos os layouts
- **Cards:** `bg-slate-900/60 border border-slate-800 rounded-xl` com `hover:border-indigo-500/40`
- **Botões primários:** `bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg`
- **Botões danger:** `bg-rose-600 hover:bg-rose-500`
- **Badges de status:** `bg-emerald-100 text-emerald-800` (aprovado), `bg-amber-100 text-amber-800` (ajuste), `bg-rose-100 text-rose-800` (rejeitado)

### Layouts
- **Páginas autenticadas:** Usam `<x-app-layout>` (sidebar escura + topbar branca)
- **Páginas públicas (login/registro):** Usam o layout `guest.blade.php` (fundo escuro, card centralizado)
- **Homepage:** Standalone em `welcome.blade.php` (não usa layout compartilhado)

### Sidebar
- O menu lateral é definido em `sidebar-menu.blade.php` e exibe links diferentes conforme a `role` do usuário logado.
- Usa condicionais Blade `@if(auth()->user()->role === 'director')` para controle de visibilidade.

### Formulários
- Todos os formulários usam `@csrf` e, para PUT/DELETE, `@method('PUT')` / `@method('DELETE')`.
- Muitos formulários utilizam `?id=` na query string ao invés de parâmetros de rota (ex: `/school/planning/edit?id=1`).

### Build de Produção
- Sempre rodar `npm run build` antes do push para gerar os assets em `public/build/`.
- O Tailwind CSS é purgado automaticamente pelo Vite com base nos arquivos em `resources/views/**/*.blade.php`.

---

## 10. Funcionalidades Implementadas

- [x] Sistema de autenticação (login/registro/logout) via Laravel Breeze
- [x] Multitenancy por coluna (trait BelongsToTenant)
- [x] Painel SuperAdmin para gerenciar tenants (CRUD)
- [x] Dashboard SEMED com rankings gamificados (escolas, coordenadores, professores)
- [x] Dashboard Diretor/Coordenador com indicadores de status
- [x] Dashboard Professor com upload de planejamentos
- [x] CRUD completo de cronogramas/períodos de planejamento
- [x] CRUD completo de turmas (com auto-criação de 30 turmas padrão)
- [x] CRUD completo de professores
- [x] CRUD de coordenadores (apenas pelo Diretor)
- [x] Upload de planejamentos (.docx) com extração automática de texto
- [x] Sistema de avaliação de planejamentos (aprovar/rejeitar/ajustar + feedback)
- [x] Gamificação com pontuação de entregas e medalhas
- [x] Integração com WhatsApp (links diretos para envio de mensagens)
- [x] Relatórios otimizados para impressão A4
- [x] Reset de senhas padrão para coordenadores e professores
- [x] Homepage pública corporativa com benefícios do sistema
- [x] Logo da N Circuits Technologies integrado (header, login, footer)
- [x] Página de login redesenhada com estilo dark premium

## 11. Funcionalidades Pendentes (TODO)

- [ ] **Integração global dos widgets de IA (IANNE)** no layout `app.blade.php` — A assistente de IA deve ser acessível em todas as páginas autenticadas, com comportamento contextual baseado na role do usuário.
- [ ] **Dashboards de Supervisores** (Ed. Física e Monitores) — Atualmente são páginas placeholder com textos básicos. Precisam de conteúdo real.
- [ ] **Notificações oficiais da SEMED** — O modelo `Notice` existe mas o CRUD e as views não foram implementados.
- [ ] **Upload de foto de perfil** — A rota existe mas a implementação visual no frontend está incompleta.
- [ ] **Filtros avançados nos relatórios** — Adicionar filtros por bimestre, data e status nos relatórios.

---

## 12. Variáveis de Ambiente Necessárias (.env)

```env
APP_NAME=SGP
APP_ENV=production
APP_KEY=base64:... (gerar com php artisan key:generate)
APP_DEBUG=false
APP_URL=https://frequenciasmart-cloud-133049.hostingersite.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u199671261_smartsheets1
DB_USERNAME=u199671261_smart1
DB_PASSWORD=<configurada no servidor>

# Opcional: Configurações da IA (IANNE usa a Gemini API do Google)
GEMINI_API_KEY=
GEMINI_MODEL=gemini-1.5-flash
GEMINI_MAX_TOKENS=1000
GEMINI_TEMPERATURE=0.3
```

---

## 13. Comandos Úteis

```bash
# Desenvolvimento local
php artisan serve          # Iniciar servidor local
npm run dev                # Vite em modo dev (hot reload)
npm run build              # Compilar assets para produção

# Banco de dados
php artisan migrate        # Executar migrations
php artisan db:seed        # Popular banco com dados de exemplo
php artisan migrate:fresh --seed  # Recriar banco do zero

# Deploy
git add . && git commit -m "msg" && git push origin main

# Cache (produção)
php artisan config:cache   # Cachear configurações
php artisan route:cache    # Cachear rotas
php artisan view:cache     # Cachear views
```

---

## 14. 🔄 PIVÔ ESTRATÉGICO PLANEJADO — Plataforma de Correção (decidido em 01/07/2026)

> **Contexto da decisão:** professores e coordenadores rejeitaram o fluxo atual (envio com deadline + cronogramas obrigatórios). Na prática, coordenadores já recebem os planejamentos digitalmente por e-mail/WhatsApp e já usam IA de forma dispersa para corrigir. O sistema será realinhado a esse comportamento real: o coordenador arrasta os documentos para dentro do SGP, a IA ajuda na correção, e a hierarquia (Diretor/SEMED/Seduc) ganha diagnósticos analíticos em tempo real da rede via RAG.

### O que SAI (desativar, NÃO deletar — fica dormindo atrás de flags)

| Peça | Localização | Ação |
|---|---|---|
| Upload pelo professor | `DocumentController@store`, rota `professor.documents.store`, dashboard do professor | Desativar rota |
| CRUD de cronogramas | `SchoolController` (`plannings`…`deletePlanning`, ~250 linhas) + views `school/planning_*` | Desativar |
| Pontuação punitiva | Cálculo por prazo (`score_base`, `penalty_delay`, `penalty_resubmission`, `rejection_count`) | Ignorar campos (não dropar) |
| Medalhas/gamificação | `UserMedal` | Desativar |
| Login de professor | Role `professor` + dashboard | Professor vira **cadastro** (nome, turma, disciplina), não usuário com login |

### O que FICA intacto

Multi-tenant, hierarquia de papéis (superadmin → seduc → semed → diretor/vice → coordenador → supervisores), escolas, turmas, `DocumentExtractor`, e o trio do RAG (`AIService`, `PromptBuilder`, `RAGController`).

### O que MUDA / NASCE

1. **`Document` reaproveitado:** adicionar `uploaded_by` (coordenador), `professor_id` (referência ao cadastro), `class_id`, `discipline`, rótulo livre de período (ex: "1º bimestre 2026") no lugar de `period_id` obrigatório.
2. **Tela de upload do coordenador (coração novo):** drag-and-drop multi-arquivo. A IA lê o documento e **infere** professor/turma/disciplina/período do cabeçalho, casando com cadastros existentes; coordenador só confirma. Ampliar `DocumentExtractor` para PDF (hoje só .docx).
3. **Correção assistida:** ao confirmar upload, IA gera análise baseada em critérios/rubrica configuráveis pela SEMED (nova tabela por tenant). Coordenador edita e salva. Substitui o `reviewDocument` punitivo.
4. **`ContextBuilder` reescrito nas estatísticas:** sai "enviados/atrasados/taxa de entrega", entra "corrigidos/pendentes, metodologias identificadas, temas por turma". Daqui saem os diagnósticos em tempo real para a hierarquia.

### Fases de implementação

1. **Fase 1 — Upload inteligente** (~3-4 dias): migração do `Document`, professores viram cadastro, tela drag-and-drop com inferência de metadados. Sistema vira o repositório central.
2. **Fase 2 — Correção assistida** (~1-2 dias): rubrica da SEMED + análise automática + edição pelo coordenador. Nasce a devolutiva que compra a adesão.
3. **Fase 3 — Diagnóstico da rede** (~1-2 dias): ajuste do `ContextBuilder`/dashboards para as novas métricas. Nasce o valor para a hierarquia.

### Princípios da decisão (não violar)

- **O upload tem que ser mais simples que colar no ChatGPT** — zero formulário; metadados inferidos pela IA, coordenador apenas confirma.
- **A devolutiva imediata ao coordenador (correção assistida) vem ANTES do dashboard do gestor** — é ela que compra os uploads que alimentam os relatórios.
- **Sem dado de "quem entregou no prazo"** — o sistema passa a mostrar o que foi corrigido, não o que foi entregue. Expectativa alinhada com a SEMED antes do pivô.
- Fluxo antigo permanece no código, desativado, reativável por escola se necessário.
