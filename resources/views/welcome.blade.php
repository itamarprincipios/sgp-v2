<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Sistema de Gestão Pedagógica</title>
    <meta name="description" content="Plataforma completa de gestão pedagógica para redes municipais de ensino. Controle planejamentos, avaliações, rankings e relatórios em tempo real.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #ffffff; 
            color: #0f172a; 
            overflow-x: hidden; 
        }

        /* ── Animated Light Gradient Orbs ── */
        .orb {
            position: absolute; border-radius: 50%; filter: blur(120px);
            opacity: 0.1; pointer-events: none; will-change: transform;
        }
        .orb-1 { width: 700px; height: 700px; background: #7c3aed; top: -20%; left: -20%; animation: float1 22s ease-in-out infinite; }
        .orb-2 { width: 600px; height: 600px; background: #c084fc; bottom: -10%; right: -10%; animation: float2 26s ease-in-out infinite; }

        @keyframes float1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(40px,20px) scale(1.05); } }
        @keyframes float2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-30px,-15px) scale(1.08); } }

        /* ── Scroll reveal ── */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.6s cubic-bezier(0.16,1,0.3,1), transform 0.6s cubic-bezier(0.16,1,0.3,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.08s; } .delay-2 { transition-delay: 0.16s; }
        .delay-3 { transition-delay: 0.24s; } .delay-4 { transition-delay: 0.32s; }
        .delay-5 { transition-delay: 0.4s; }  .delay-6 { transition-delay: 0.48s; }

        /* ── Hero entrance ── */
        .hero-enter   { animation: heroIn 0.8s cubic-bezier(0.16,1,0.3,1) both; }
        .hero-enter-2 { animation: heroIn 0.8s cubic-bezier(0.16,1,0.3,1) 0.12s both; }
        .hero-enter-3 { animation: heroIn 0.8s cubic-bezier(0.16,1,0.3,1) 0.24s both; }
        .hero-enter-4 { animation: heroIn 0.8s cubic-bezier(0.16,1,0.3,1) 0.36s both; }
        @keyframes heroIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        /* ── Shimmer divider ── */
        .shimmer { height:1px; background:linear-gradient(90deg,transparent,rgba(124,58,237,0.2),transparent); background-size:200% 100%; animation:shimmerMove 3.5s ease-in-out infinite; }
        @keyframes shimmerMove { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

        /* ── Feature card ── */
        .feature-card { transition: transform 0.3s cubic-bezier(0.16,1,0.3,1), box-shadow 0.3s ease, border-color 0.3s ease; }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px -10px rgba(124,58,237,0.08); border-color: rgba(124,58,237,0.2); }

        /* ── Soft Glass/Border effect for light theme ── */
        .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(124,58,237,0.08); }

        /* ── CTA glow ── */
        .btn-glow { position:relative; overflow:hidden; }
        .btn-glow::after { content:''; position:absolute; inset:-50%; background:radial-gradient(circle,rgba(255,255,255,0.2) 0%,transparent 60%); opacity:0; transition:opacity 0.4s; }
        .btn-glow:hover::after { opacity:1; }

        /* ── Dot grid overlay in light theme ── */
        .grid-pattern {
            background-image: radial-gradient(rgba(124,58,237,0.04) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="antialiased grid-pattern">

    <!-- ═══ HEADER ═══ -->
    <header class="fixed top-0 inset-x-0 z-50 transition-all duration-300" id="mainHeader">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits Technologies" class="h-8 w-auto transition-transform duration-300 group-hover:scale-105">
                <span class="text-base font-extrabold text-slate-900 tracking-tight">SGP</span>
            </a>
            <nav class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-glow px-5 py-2 text-xs font-bold rounded-lg bg-purple-600 hover:bg-purple-500 text-white shadow-md shadow-purple-600/10 transition-all duration-200">
                            Painel de Controle
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-purple-600 transition-colors duration-200">Entrar</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-glow px-5 py-2 text-xs font-bold rounded-lg bg-purple-600 hover:bg-purple-500 text-white shadow-md shadow-purple-600/10 transition-all duration-200">Cadastrar</a>
                        @endif
                    @endauth
                @endif
            </nav>
        </div>
    </header>

    <!-- ═══ HERO ═══ -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center py-24">

            <!-- Logo grande e identitário de alta resolução -->
            <div class="hero-enter mb-8">
                <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits Technologies" class="h-32 sm:h-40 w-auto mx-auto drop-shadow-sm">
            </div>

            <h1 class="hero-enter-2 text-4xl sm:text-5xl lg:text-[3.2rem] font-extrabold tracking-tight leading-[1.15] text-slate-900 mb-6">
                Sistema de Gestão<br>
                <span class="bg-gradient-to-r from-purple-600 via-purple-700 to-fuchsia-600 bg-clip-text text-transparent">Pedagógica</span>
            </h1>

            <p class="hero-enter-3 text-slate-600 text-base sm:text-lg max-w-xl mx-auto leading-relaxed mb-10">
                Acompanhe planejamentos, organize turmas, avalie com feedbacks e monitore métricas da rede escolar de forma integrada e totalmente online.
            </p>

            <div class="hero-enter-4 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('login') }}" class="btn-glow group px-8 py-3.5 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl shadow-lg shadow-purple-600/20 text-sm transition-all duration-200 flex items-center justify-center gap-2">
                    Acessar o Sistema
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="https://wa.me/5595991248941" target="_blank" class="group px-8 py-3.5 glass hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2.5">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    (95) 99124-8941
                </a>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 shimmer"></div>
    </section>

    <!-- ═══ STATS ═══ -->
    <section class="relative z-10 border-y border-slate-100 bg-slate-50/50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="reveal">
                    <div class="text-2xl font-extrabold text-slate-900">100%</div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Digital & Online</p>
                </div>
                <div class="reveal delay-1">
                    <div class="text-2xl font-extrabold text-slate-900">8</div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Perfis de Acesso</p>
                </div>
                <div class="reveal delay-2">
                    <div class="text-2xl font-extrabold text-slate-900">A4</div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Relatórios Prontos</p>
                </div>
                <div class="reveal delay-3">
                    <div class="text-2xl font-extrabold text-slate-900">24/7</div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Sempre Disponível</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ FEATURES ═══ -->
    <section class="py-24" id="funcionalidades">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center space-y-3 max-w-xl mx-auto mb-16 reveal">
                <p class="text-purple-600 text-[11px] font-bold uppercase tracking-[0.25em]">Funcionalidades</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Tudo em um só lugar
                </h2>
                <p class="text-slate-500 text-sm">
                    Projetado especificamente para as necessidades pedagógicas diárias das escolas e da secretaria.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $features = [
                        ['icon' => '📅', 'title' => 'Cronogramas e Prazos', 'desc' => 'Defina cronogramas de submissão com datas de abertura, encerramento e prazos limites bimestrais.'],
                        ['icon' => '📄', 'title' => 'Extração Inteligente', 'desc' => 'Aceita uploads de arquivos Word (.docx) com extração automática para visualização simplificada.'],
                        ['icon' => '🏆', 'title' => 'Gamificação e Rankings', 'desc' => 'Estimule a pontualidade com métricas automatizadas e atribuição de medalhas de destaque.'],
                        ['icon' => '💬', 'title' => 'Feedbacks Integrados', 'desc' => 'Envie notificações de aprovações ou solicitações de ajustes diretamente pelo WhatsApp.'],
                        ['icon' => '📊', 'title' => 'Painel SEMED', 'desc' => 'Dashboard completo da secretaria de educação para monitorar os indicadores de toda a rede.'],
                        ['icon' => '🖨️', 'title' => 'Relatórios em A4', 'desc' => 'Gere relatórios bimestrais otimizados para impressão em folha de papel física A4.'],
                    ];
                @endphp

                @foreach($features as $i => $feature)
                <div class="feature-card glass rounded-2xl p-6 space-y-3 reveal delay-{{ $i + 1 }}">
                    <div class="w-10 h-10 bg-purple-50 flex items-center justify-center rounded-xl text-lg">{{ $feature['icon'] }}</div>
                    <h3 class="text-sm font-bold text-slate-900">{{ $feature['title'] }}</h3>
                    <p class="text-slate-500 text-[13px] leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ═══ WHO IT'S FOR ═══ -->
    <section class="py-24 border-t border-slate-100 bg-slate-50/30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center space-y-3 max-w-xl mx-auto mb-16 reveal">
                <p class="text-purple-600 text-[11px] font-bold uppercase tracking-[0.25em]">Acessibilidade</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Um sistema para cada papel
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @php
                    $roles = [
                        ['emoji' => '🏛️', 'title' => 'SEMED', 'desc' => 'Visão completa com gráficos consolidados e métricas da rede.', 'color' => 'purple'],
                        ['emoji' => '🏫', 'title' => 'Diretores', 'desc' => 'Gerenciamento global da escola, turmas e equipes.', 'color' => 'violet'],
                        ['emoji' => '📋', 'title' => 'Coordenadores', 'desc' => 'Avaliação rápida de planos de aula e envio de pareceres.', 'color' => 'fuchsia'],
                        ['emoji' => '👨‍🏫', 'title' => 'Professores', 'desc' => 'Envio prático e rápido de planejamentos por turma.', 'color' => 'pink'],
                    ];
                @endphp

                @foreach($roles as $i => $role)
                <div class="reveal delay-{{ $i + 1 }} text-center glass rounded-2xl p-6 space-y-3 feature-card bg-white">
                    <div class="w-12 h-12 mx-auto bg-purple-50 rounded-2xl flex items-center justify-center text-xl text-purple-600">{{ $role['emoji'] }}</div>
                    <h4 class="text-sm font-bold text-slate-900">{{ $role['title'] }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">{{ $role['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ═══ CTA ═══ -->
    <section class="py-24 border-t border-slate-100 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-purple-50 to-transparent pointer-events-none"></div>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 reveal">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                Pronto para otimizar sua gestão?
            </h2>
            <p class="text-slate-500 text-sm mb-8 max-w-md mx-auto">
                Facilite a rotina de professores e coordenadores com uma gestão de cronogramas ágil e moderna.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('login') }}" class="btn-glow px-8 py-3.5 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl shadow-lg shadow-purple-600/10 text-sm transition-all duration-200">
                    Acessar o Sistema
                </a>
                <a href="https://wa.me/5595991248941" target="_blank" class="px-8 py-3.5 glass hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2 bg-white">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    Falar com Suporte
                </a>
            </div>
        </div>
    </section>

    <!-- ═══ FOOTER ═══ -->
    <footer class="border-t border-slate-100 bg-slate-50 py-14">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 mb-10">
                <div class="md:col-span-5 space-y-4">
                    <a href="/" class="inline-block">
                        <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits Technologies" class="h-12 w-auto">
                    </a>
                    <p class="text-slate-500 text-sm leading-relaxed max-w-sm">
                        Desenvolvimento de aplicações web personalizadas e seguras para a transformação digital de serviços públicos e privados.
                    </p>
                    <a href="https://wa.me/5595991248941" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all duration-200">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        (95) 99124-8941
                    </a>
                </div>

                <div class="md:col-span-3 space-y-4">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-l-2 border-purple-600 pl-3">Serviços</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li>• Sistemas de gestão sob medida</li>
                        <li>• Painéis e dashboards</li>
                        <li>• Sites e aplicações web</li>
                        <li>• Integração com APIs</li>
                    </ul>
                </div>

                <div class="md:col-span-4 space-y-4">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-l-2 border-purple-600 pl-3">Soluções</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li>• Gestão Pedagógica (SGP)</li>
                        <li>• Jogos Escolares (Sistema JEM)</li>
                        <li>• Consultoria Digital</li>
                        <li>• Hospedagem e Suporte</li>
                    </ul>
                </div>
            </div>

            <div class="shimmer mb-6"></div>

            <div class="flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-400">
                <p>&copy; 2026 SGP — Sistema de Gestão Pedagógica.</p>
                <p>Desenvolvido por <a href="https://wa.me/5595991248941" target="_blank" class="font-bold text-purple-600 hover:text-purple-500 transition-colors">N Circuits Technologies</a></p>
            </div>
        </div>
    </footer>

    <!-- ═══ SCRIPTS ═══ -->
    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        const header = document.getElementById('mainHeader');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) {
                header.style.background = 'rgba(255,255,255,0.92)';
                header.style.backdropFilter = 'blur(16px)';
                header.style.borderBottom = '1px solid rgba(0,0,0,0.05)';
            } else {
                header.style.background = 'transparent';
                header.style.backdropFilter = 'none';
                header.style.borderBottom = 'none';
            }
        });
    </script>

</body>
</html>
