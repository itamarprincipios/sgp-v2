<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Sistema de Gestão Pedagógica | N Circuits Technologies</title>
    <meta name="description" content="Plataforma completa de gestão pedagógica para redes municipais de ensino. Controle planejamentos, avaliações, rankings e relatórios em tempo real.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0b1120; color: #e2e8f0; overflow-x: hidden; }

        /* ── Animated Gradient Orbs ── */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            pointer-events: none;
            will-change: transform;
        }
        .orb-1 { width: 600px; height: 600px; background: #6366f1; top: -10%; left: -10%; animation: float1 18s ease-in-out infinite; }
        .orb-2 { width: 500px; height: 500px; background: #a855f7; bottom: -15%; right: -5%; animation: float2 22s ease-in-out infinite; }
        .orb-3 { width: 400px; height: 400px; background: #3b82f6; top: 50%; left: 50%; animation: float3 15s ease-in-out infinite; }

        @keyframes float1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(60px,40px) scale(1.1); } }
        @keyframes float2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-50px,-30px) scale(1.15); } }
        @keyframes float3 { 0%,100% { transform: translate(-50%,-50%) scale(1); } 50% { transform: translate(-45%,-55%) scale(1.08); } }

        /* ── Scroll-triggered reveal ── */
        .reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1);
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── Stagger delays ── */
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }
        .delay-4 { transition-delay: 0.4s; }
        .delay-5 { transition-delay: 0.5s; }
        .delay-6 { transition-delay: 0.6s; }

        /* ── Hero text entrance ── */
        .hero-enter { animation: heroSlide 0.9s cubic-bezier(0.16,1,0.3,1) both; }
        .hero-enter-2 { animation: heroSlide 0.9s cubic-bezier(0.16,1,0.3,1) 0.15s both; }
        .hero-enter-3 { animation: heroSlide 0.9s cubic-bezier(0.16,1,0.3,1) 0.3s both; }
        .hero-enter-4 { animation: heroSlide 0.9s cubic-bezier(0.16,1,0.3,1) 0.45s both; }

        @keyframes heroSlide {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Subtle shimmer line ── */
        .shimmer-line {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

        /* ── Stat counter pulse ── */
        .stat-glow { box-shadow: 0 0 0 0 rgba(99,102,241,0.3); animation: statPulse 2.5s ease-in-out infinite; }
        @keyframes statPulse { 0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,0.2)} 50%{box-shadow:0 0 0 8px rgba(99,102,241,0)} }

        /* ── Grid pattern overlay ── */
        .grid-pattern {
            background-image: radial-gradient(rgba(99,102,241,0.08) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* ── Glass card ── */
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(99, 102, 241, 0.08);
        }

        /* ── Feature card hover lift ── */
        .feature-card {
            transition: transform 0.35s cubic-bezier(0.16,1,0.3,1), box-shadow 0.35s ease, border-color 0.35s ease;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -12px rgba(99,102,241,0.15);
            border-color: rgba(99,102,241,0.3);
        }

        /* ── CTA button glow ── */
        .btn-glow {
            position: relative;
            overflow: hidden;
        }
        .btn-glow::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .btn-glow:hover::after { opacity: 1; }
    </style>
</head>
<body class="antialiased">

    <!-- ════════════════════════════════════════════════ -->
    <!-- HEADER                                          -->
    <!-- ════════════════════════════════════════════════ -->
    <header class="fixed top-0 inset-x-0 z-50 transition-all duration-300" id="mainHeader">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits Technologies" class="h-9 w-auto transition-transform duration-300 group-hover:scale-105">
                <div class="flex items-center gap-2">
                    <span class="text-lg font-extrabold text-white tracking-tight">SGP</span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 bg-indigo-500/15 text-indigo-400 rounded border border-indigo-500/20">v2</span>
                </div>
            </a>

            <nav class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-glow px-5 py-2 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25 transition-all duration-200">
                            Painel de Controle
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors duration-200">
                            Entrar
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-glow px-5 py-2 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25 transition-all duration-200">
                                Cadastrar
                            </a>
                        @endif
                    @endauth
                @endif
            </nav>
        </div>
    </header>

    <!-- ════════════════════════════════════════════════ -->
    <!-- HERO                                            -->
    <!-- ════════════════════════════════════════════════ -->
    <section class="relative min-h-screen flex items-center pt-16 grid-pattern overflow-hidden">
        <!-- Animated orbs -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">

                <!-- Text -->
                <div class="lg:col-span-7 text-center lg:text-left space-y-7">
                    <div class="hero-enter inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass text-indigo-400 text-xs font-semibold tracking-wide">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Plataforma ativa • Gestão Escolar Inteligente
                    </div>

                    <h1 class="hero-enter-2 text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.1]">
                        <span class="text-white">Monitore o </span>
                        <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-purple-400 bg-clip-text text-transparent">Desempenho Pedagógico</span>
                        <span class="text-white"> da sua Rede.</span>
                    </h1>

                    <p class="hero-enter-3 text-slate-400 text-base sm:text-lg max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        Acompanhe planejamentos de professores, organize turmas, realize avaliações com feedbacks integrados e visualize métricas e rankings em tempo real.
                    </p>

                    <div class="hero-enter-4 flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                        <a href="{{ route('login') }}" class="btn-glow group px-7 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-xl shadow-indigo-600/25 text-sm transition-all duration-200 text-center flex items-center justify-center gap-2">
                            Acessar o SGP
                            <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="https://wa.me/5595991248941" target="_blank" class="group px-7 py-3.5 glass hover:bg-slate-800/60 text-slate-300 font-semibold rounded-xl text-sm transition-all duration-200 text-center flex items-center justify-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            (95) 99124-8941
                        </a>
                    </div>
                </div>

                <!-- Dashboard Preview Card -->
                <div class="lg:col-span-5 hidden lg:block hero-enter-4">
                    <div class="relative glass rounded-2xl p-6 shadow-2xl shadow-indigo-950/30">
                        <!-- Glow accents -->
                        <div class="absolute -top-6 -right-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-violet-500/10 rounded-full blur-3xl"></div>

                        <div class="relative space-y-5">
                            <!-- Window chrome -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                                </div>
                                <span class="text-[10px] text-slate-600 font-mono">sgp.dashboard</span>
                            </div>

                            <!-- Network stat -->
                            <div class="p-3.5 bg-indigo-500/8 border border-indigo-500/15 rounded-xl flex items-center justify-between stat-glow">
                                <div>
                                    <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Rede Municipal</p>
                                    <p class="text-sm font-bold text-white mt-0.5">Monitoramento Ativo</p>
                                </div>
                                <div class="w-9 h-9 bg-indigo-500/15 rounded-lg flex items-center justify-center">
                                    <span class="text-indigo-400 text-sm">🏫</span>
                                </div>
                            </div>

                            <!-- Metrics row -->
                            <div class="grid grid-cols-3 gap-2">
                                <div class="p-3 bg-slate-800/40 border border-slate-700/50 rounded-xl text-center">
                                    <div class="text-xl font-black text-emerald-400">92%</div>
                                    <div class="text-[9px] text-slate-500 font-medium mt-0.5">Aprovados</div>
                                </div>
                                <div class="p-3 bg-slate-800/40 border border-slate-700/50 rounded-xl text-center">
                                    <div class="text-xl font-black text-amber-400">5%</div>
                                    <div class="text-[9px] text-slate-500 font-medium mt-0.5">Ajustes</div>
                                </div>
                                <div class="p-3 bg-slate-800/40 border border-slate-700/50 rounded-xl text-center">
                                    <div class="text-xl font-black text-rose-400">3%</div>
                                    <div class="text-[9px] text-slate-500 font-medium mt-0.5">Pendentes</div>
                                </div>
                            </div>

                            <!-- Mini bar chart -->
                            <div>
                                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider mb-2">Entregas por Bimestre</p>
                                <div class="flex items-end gap-1.5 h-16">
                                    <div class="flex-1 bg-indigo-500/30 rounded-t-sm" style="height:45%"></div>
                                    <div class="flex-1 bg-indigo-500/40 rounded-t-sm" style="height:62%"></div>
                                    <div class="flex-1 bg-indigo-500/50 rounded-t-sm" style="height:78%"></div>
                                    <div class="flex-1 bg-indigo-500/70 rounded-t-sm" style="height:92%"></div>
                                    <div class="flex-1 bg-indigo-400 rounded-t-sm" style="height:100%"></div>
                                    <div class="flex-1 bg-violet-400/60 rounded-t-sm" style="height:55%"></div>
                                </div>
                            </div>

                            <!-- Logo watermark -->
                            <div class="pt-3 border-t border-slate-800/50 flex items-center justify-center gap-2 opacity-40">
                                <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits" class="h-4 w-auto">
                                <span class="text-[8px] text-slate-500 font-medium">Powered by N Circuits</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Shimmer divider -->
        <div class="absolute bottom-0 left-0 right-0 shimmer-line"></div>
    </section>

    <!-- ════════════════════════════════════════════════ -->
    <!-- STATS BAR                                       -->
    <!-- ════════════════════════════════════════════════ -->
    <section class="relative z-10 border-y border-slate-800/40 bg-slate-950/80 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="reveal">
                    <div class="text-3xl font-black text-white">100%</div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Digital & Online</p>
                </div>
                <div class="reveal delay-1">
                    <div class="text-3xl font-black text-white">8</div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Perfis de Acesso</p>
                </div>
                <div class="reveal delay-2">
                    <div class="text-3xl font-black text-white">A4</div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Relatórios Impressos</p>
                </div>
                <div class="reveal delay-3">
                    <div class="text-3xl font-black text-white">24/7</div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Disponível Sempre</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════ -->
    <!-- FEATURES GRID                                   -->
    <!-- ════════════════════════════════════════════════ -->
    <section class="relative py-24 grid-pattern" id="funcionalidades">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center space-y-4 max-w-2xl mx-auto mb-16 reveal">
                <p class="text-indigo-400 text-xs font-bold uppercase tracking-[0.2em]">Funcionalidades</p>
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                    Tudo que sua rede precisa em um só lugar
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Cada funcionalidade foi projetada para resolver problemas reais do dia a dia da gestão pedagógica municipal.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                <div class="feature-card glass rounded-2xl p-6 space-y-4 reveal delay-1">
                    <div class="w-11 h-11 bg-indigo-500/10 border border-indigo-500/15 text-lg flex items-center justify-center rounded-xl">📅</div>
                    <h3 class="text-base font-bold text-white">Cronogramas e Prazos</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Crie cronogramas de submissão de planos de aula com datas de abertura, encerramento e prazos limites por bimestre.</p>
                </div>

                <div class="feature-card glass rounded-2xl p-6 space-y-4 reveal delay-2">
                    <div class="w-11 h-11 bg-indigo-500/10 border border-indigo-500/15 text-lg flex items-center justify-center rounded-xl">📄</div>
                    <h3 class="text-base font-bold text-white">Extração Inteligente (DOCX)</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Upload exclusivo de arquivos Word com extração automática de texto para leitura rápida diretamente na tela do sistema.</p>
                </div>

                <div class="feature-card glass rounded-2xl p-6 space-y-4 reveal delay-3">
                    <div class="w-11 h-11 bg-indigo-500/10 border border-indigo-500/15 text-lg flex items-center justify-center rounded-xl">🏆</div>
                    <h3 class="text-base font-bold text-white">Gamificação e Rankings</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Pontuações automáticas por pontualidade, rankings de escolas, coordenadores e professores com medalhas de destaque.</p>
                </div>

                <div class="feature-card glass rounded-2xl p-6 space-y-4 reveal delay-4">
                    <div class="w-11 h-11 bg-indigo-500/10 border border-indigo-500/15 text-lg flex items-center justify-center rounded-xl">💬</div>
                    <h3 class="text-base font-bold text-white">Feedbacks Integrados</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Aprove, rejeite ou solicite ajustes nos planejamentos com envio de retornos automáticos integrados ao WhatsApp.</p>
                </div>

                <div class="feature-card glass rounded-2xl p-6 space-y-4 reveal delay-5">
                    <div class="w-11 h-11 bg-indigo-500/10 border border-indigo-500/15 text-lg flex items-center justify-center rounded-xl">📊</div>
                    <h3 class="text-base font-bold text-white">Painel SEMED Centralizado</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Dashboard gerencial para a Secretaria de Educação consolidar o monitoramento de todas as escolas da rede municipal.</p>
                </div>

                <div class="feature-card glass rounded-2xl p-6 space-y-4 reveal delay-6">
                    <div class="w-11 h-11 bg-indigo-500/10 border border-indigo-500/15 text-lg flex items-center justify-center rounded-xl">🖨️</div>
                    <h3 class="text-base font-bold text-white">Relatórios em A4</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Exportação rápida de relatórios de envios, pontualidade e pendências otimizados nativamente para impressão em folha A4.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════ -->
    <!-- WHO IT'S FOR                                    -->
    <!-- ════════════════════════════════════════════════ -->
    <section class="py-24 border-t border-slate-800/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center space-y-4 max-w-2xl mx-auto mb-16 reveal">
                <p class="text-indigo-400 text-xs font-bold uppercase tracking-[0.2em]">Para quem é</p>
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                    Um sistema para cada papel
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="reveal delay-1 text-center glass rounded-2xl p-6 space-y-3 feature-card">
                    <div class="w-14 h-14 mx-auto bg-violet-500/10 border border-violet-500/15 rounded-2xl flex items-center justify-center text-2xl">🏛️</div>
                    <h4 class="text-sm font-bold text-white">SEMED</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Visão consolidada de toda a rede com rankings, métricas e filtros por bimestre.</p>
                </div>
                <div class="reveal delay-2 text-center glass rounded-2xl p-6 space-y-3 feature-card">
                    <div class="w-14 h-14 mx-auto bg-blue-500/10 border border-blue-500/15 rounded-2xl flex items-center justify-center text-2xl">🏫</div>
                    <h4 class="text-sm font-bold text-white">Diretores</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Gerencie coordenadores, professores, turmas e cronogramas da sua escola.</p>
                </div>
                <div class="reveal delay-3 text-center glass rounded-2xl p-6 space-y-3 feature-card">
                    <div class="w-14 h-14 mx-auto bg-emerald-500/10 border border-emerald-500/15 rounded-2xl flex items-center justify-center text-2xl">📋</div>
                    <h4 class="text-sm font-bold text-white">Coordenadores</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Avalie planos de aula, envie feedbacks e acompanhe pendências dos professores.</p>
                </div>
                <div class="reveal delay-4 text-center glass rounded-2xl p-6 space-y-3 feature-card">
                    <div class="w-14 h-14 mx-auto bg-amber-500/10 border border-amber-500/15 rounded-2xl flex items-center justify-center text-2xl">👨‍🏫</div>
                    <h4 class="text-sm font-bold text-white">Professores</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">Envie seus planejamentos em .docx e acompanhe o status de aprovação.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════ -->
    <!-- CTA FINAL                                       -->
    <!-- ════════════════════════════════════════════════ -->
    <section class="py-24 border-t border-slate-800/40 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-indigo-950/10 to-transparent pointer-events-none"></div>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 reveal">
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-4">
                Pronto para transformar sua rede?
            </h2>
            <p class="text-slate-400 text-sm sm:text-base mb-8 max-w-xl mx-auto">
                Entre em contato com nossa equipe para uma demonstração personalizada ou solicite acesso ao sistema agora mesmo.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('login') }}" class="btn-glow px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-xl shadow-indigo-600/25 text-sm transition-all duration-200">
                    Acessar o SGP
                </a>
                <a href="https://wa.me/5595991248941" target="_blank" class="px-8 py-4 glass hover:bg-slate-800/60 text-slate-300 font-semibold rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    Solicitar Demonstração
                </a>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════ -->
    <!-- FOOTER                                          -->
    <!-- ════════════════════════════════════════════════ -->
    <footer class="border-t border-slate-800/40 bg-slate-950 py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 mb-10">
                <div class="md:col-span-5 space-y-4">
                    <a href="/" class="flex items-center gap-3">
                        <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits Technologies" class="h-10 w-auto">
                    </a>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                        Somos especializados no desenvolvimento de aplicações web personalizadas, criadas para resolver problemas reais com soluções modernas, seguras e escaláveis.
                    </p>
                    <a href="https://wa.me/5595991248941" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all duration-200">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        (95) 99124-8941
                    </a>
                </div>

                <div class="md:col-span-3 space-y-4">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider border-l-2 border-indigo-500 pl-3">Serviços</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>• Sistemas de gestão sob medida</li>
                        <li>• Painéis e dashboards</li>
                        <li>• Sites e aplicações web</li>
                        <li>• Integração com APIs</li>
                    </ul>
                </div>

                <div class="md:col-span-4 space-y-4">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider border-l-2 border-indigo-500 pl-3">Soluções</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>• Gestão Pedagógica (SGP)</li>
                        <li>• Jogos Escolares (Sistema JEM)</li>
                        <li>• Consultoria Digital</li>
                        <li>• Hospedagem e Suporte</li>
                    </ul>
                </div>
            </div>

            <div class="shimmer-line mb-6"></div>

            <div class="flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-600">
                <p>&copy; 2026 SGP — Sistema de Gestão Pedagógica. Todos os direitos reservados.</p>
                <p>Desenvolvido por <a href="https://wa.me/5595991248941" target="_blank" class="font-bold text-indigo-400 hover:text-indigo-300 transition-colors">N Circuits Technologies</a></p>
            </div>
        </div>
    </footer>

    <!-- ════════════════════════════════════════════════ -->
    <!-- SCRIPTS                                         -->
    <!-- ════════════════════════════════════════════════ -->
    <script>
        // Scroll-triggered reveal animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Header background on scroll
        const header = document.getElementById('mainHeader');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) {
                header.style.background = 'rgba(11, 17, 32, 0.85)';
                header.style.backdropFilter = 'blur(16px)';
                header.style.borderBottom = '1px solid rgba(51,65,85,0.3)';
            } else {
                header.style.background = 'transparent';
                header.style.backdropFilter = 'none';
                header.style.borderBottom = 'none';
            }
        });
    </script>

</body>
</html>
