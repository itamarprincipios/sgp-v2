<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Sistema de Gestão Pedagógica</title>
    <meta name="description" content="Sistema de Gestão Pedagógica para acompanhamento de planejamentos escolares, avaliações e controle da rede municipal de ensino.">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite CSS and JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }

        .hero-bg {
            position: relative;
            overflow: hidden;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(79, 70, 229, 0.08) 0%, transparent 60%),
                        radial-gradient(circle at 70% 60%, rgba(168, 85, 247, 0.06) 0%, transparent 50%);
            animation: pulseGlow 12s ease-in-out infinite alternate;
            pointer-events: none;
        }
        @keyframes pulseGlow {
            0%   { opacity: 0.6; transform: scale(1); }
            100% { opacity: 1;   transform: scale(1.05); }
        }
        .fade-up {
            animation: fadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .fade-up-delay-1 { animation-delay: 0.15s; }
        .fade-up-delay-2 { animation-delay: 0.3s; }
        .fade-up-delay-3 { animation-delay: 0.45s; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="h-full bg-slate-900 text-slate-200 antialiased">

    <!-- ─── Header / Navbar ─── -->
    <header class="sticky top-0 z-50 border-b border-slate-800/60 bg-slate-950/70 backdrop-blur-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits Technologies" class="h-8 w-auto">
                <span class="text-lg font-bold text-indigo-400 tracking-wide flex items-center gap-2">
                    SGP
                    <span class="text-[10px] font-semibold px-2 py-0.5 bg-indigo-500/10 text-indigo-300 rounded border border-indigo-500/20">v2</span>
                </span>
            </a>

            <!-- Nav Actions -->
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/20 transition">
                            Painel de Controle
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white transition">
                            Entrar
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/20 transition">
                                Cadastrar
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- ─── Hero Section ─── -->
    <section class="hero-bg min-h-[82vh] flex items-center py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                <!-- Left: Text -->
                <div class="lg:col-span-7 text-center lg:text-left space-y-6">
                    <!-- Badge -->
                    <div class="fade-up inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold">
                        ✨ Plataforma Completa de Gestão Escolar
                    </div>

                    <!-- Headline -->
                    <h1 class="fade-up fade-up-delay-1 text-4xl sm:text-5xl lg:text-[3.4rem] font-extrabold tracking-tight leading-[1.15] text-white">
                        Monitore o <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">Desempenho Pedagógico</span> da sua Rede Escolar.
                    </h1>

                    <!-- Subtitle -->
                    <p class="fade-up fade-up-delay-2 text-slate-400 text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        Acompanhe em tempo real os planejamentos de professores, organize turmas e coordenadores, realize avaliações com feedbacks integrados e visualize métricas em tempo real.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="fade-up fade-up-delay-3 flex flex-col sm:flex-row gap-3 justify-center lg:justify-start pt-2">
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg shadow-lg shadow-indigo-600/20 text-sm transition text-center">
                            Acessar o SGP
                        </a>
                        <a href="https://wa.me/5595991248941" target="_blank" class="px-6 py-3 border border-slate-700 hover:border-slate-500 hover:bg-slate-800/50 text-slate-300 font-semibold rounded-lg text-sm transition text-center flex items-center justify-center gap-2">
                            📞 (95) 99124-8941
                        </a>
                    </div>
                </div>

                <!-- Right: Dashboard Preview Card -->
                <div class="lg:col-span-5 hidden lg:block fade-up fade-up-delay-2">
                    <div class="relative p-6 bg-slate-950/60 border border-slate-800 rounded-2xl shadow-2xl">
                        <div class="absolute -top-3 -right-3 w-14 h-14 bg-pink-500/10 rounded-2xl blur-xl"></div>
                        <div class="absolute -bottom-4 -left-4 w-20 h-20 bg-indigo-500/10 rounded-2xl blur-2xl"></div>
                        
                        <div class="space-y-4">
                            <!-- Window dots -->
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="ml-auto text-[10px] text-slate-600 font-mono">sgp.dashboard</span>
                            </div>

                            <!-- Stats row -->
                            <div class="space-y-2 pt-2">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Acompanhamento SEMED</p>
                                <div class="h-10 w-full bg-indigo-500/10 border border-indigo-500/20 rounded-lg flex items-center justify-between px-4">
                                    <span class="text-xs font-bold text-indigo-400">Escolas Monitoradas</span>
                                    <span class="text-xs font-extrabold text-white">100% da Rede</span>
                                </div>
                            </div>

                            <!-- Metrics grid -->
                            <div class="space-y-2 pt-2">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Entrega de Planejamentos</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="p-3 bg-slate-800/50 border border-slate-800 rounded-lg text-center">
                                        <div class="text-lg font-extrabold text-emerald-400">92%</div>
                                        <div class="text-[9px] text-slate-500 font-medium">Aprovados</div>
                                    </div>
                                    <div class="p-3 bg-slate-800/50 border border-slate-800 rounded-lg text-center">
                                        <div class="text-lg font-extrabold text-amber-400">5%</div>
                                        <div class="text-[9px] text-slate-500 font-medium">Ajustes</div>
                                    </div>
                                    <div class="p-3 bg-slate-800/50 border border-slate-800 rounded-lg text-center">
                                        <div class="text-lg font-extrabold text-rose-400">3%</div>
                                        <div class="text-[9px] text-slate-500 font-medium">Pendentes</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Logo branding inside card -->
                            <div class="pt-3 border-t border-slate-800/50 flex items-center justify-center gap-2">
                                <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits" class="h-5 w-auto opacity-50">
                                <span class="text-[9px] text-slate-600">N Circuits Technologies</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ─── Benefits / Features Grid ─── -->
    <section class="py-20 border-t border-slate-800/60 bg-slate-950/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center space-y-3 max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Funcionalidades do SGP
                </h2>
                <p class="text-slate-400 text-sm sm:text-base">
                    Desenvolvido sob medida para o controle pedagógico de escolas e da secretaria municipal de educação.
                </p>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Card Component --}}
                @php
                    $features = [
                        ['icon' => '📅', 'title' => 'Cronogramas e Prazos', 'desc' => 'Crie e gerencie cronogramas e prazos limites de submissão de planos de aula na escola ou rede, permitindo visualizações diretas de datas.'],
                        ['icon' => '📄', 'title' => 'Extração Inteligente (DOCX)', 'desc' => 'Extração automatizada de texto com suporte exclusivo para uploads de arquivos do Microsoft Word (.docx), agilizando a leitura diretamente em tela.'],
                        ['icon' => '🏆', 'title' => 'Gamificação e Rankings', 'desc' => 'Incentivo à pontualidade de entregas através de pontuações, atribuição de medalhas de destaque para escolas, coordenadores e professores.'],
                        ['icon' => '💬', 'title' => 'Feedbacks Integrados', 'desc' => 'Atribua status de aprovação, notas de desempenho e envie retornos imediatos integrados ao WhatsApp para professores e coordenadores.'],
                        ['icon' => '📊', 'title' => 'Gestão Centralizada SEMED', 'desc' => 'Painel gerencial para a Secretaria de Educação consolidar o monitoramento de todas as escolas em um único ambiente integrado.'],
                        ['icon' => '🖨️', 'title' => 'Relatórios em A4', 'desc' => 'Exportação rápida de relatórios de envios, pontualidade e controle de pendências otimizados nativamente para impressão em formato folha A4.'],
                    ];
                @endphp

                @foreach($features as $feature)
                <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-xl space-y-3 hover:border-indigo-500/40 hover:shadow-lg hover:shadow-indigo-500/5 transition-all duration-200 group">
                    <div class="w-11 h-11 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-lg flex items-center justify-center rounded-lg group-hover:scale-110 transition">
                        {{ $feature['icon'] }}
                    </div>
                    <h3 class="text-base font-bold text-white">{{ $feature['title'] }}</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach

            </div>
        </div>
    </section>

    <!-- ─── Footer ─── -->
    <footer class="border-t border-slate-800/60 bg-slate-950 py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 mb-10">

                <!-- Company Info -->
                <div class="md:col-span-5 space-y-4">
                    <a href="/" class="flex items-center gap-3">
                        <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits Technologies" class="h-10 w-auto">
                    </a>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                        Somos especializados no desenvolvimento de aplicações web personalizadas, criadas para resolver problemas reais com soluções modernas, seguras e escaláveis.
                    </p>
                    <div class="flex items-center gap-3 pt-1">
                        <a href="https://wa.me/5595991248941" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">
                            🟢 (95) 99124-8941
                        </a>
                    </div>
                </div>

                <!-- Services -->
                <div class="md:col-span-3 space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider border-l-2 border-indigo-500 pl-3">Serviços</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>• Sistemas de gestão online sob medida</li>
                        <li>• Painéis administrativos e dashboards</li>
                        <li>• Desenvolvimento de sites e apps web</li>
                        <li>• Integração com APIs e automações</li>
                    </ul>
                </div>

                <!-- Solutions -->
                <div class="md:col-span-4 space-y-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider border-l-2 border-indigo-500 pl-3">Soluções</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li>• Sistema de Gestão Pedagógica (SGP)</li>
                        <li>• Gestão de Jogos Escolares (Sistema JEM)</li>
                        <li>• Consultoria em Transformação Digital</li>
                        <li>• Hospedagem e suporte especializado</li>
                    </ul>
                </div>

            </div>

            <!-- Copyright -->
            <div class="pt-6 border-t border-slate-800/50 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500">
                <p>&copy; 2026 SGP - Sistema de Gestão Pedagógica. Todos os direitos reservados.</p>
                <p>Desenvolvido por <a href="https://wa.me/5595991248941" target="_blank" class="font-bold text-indigo-400 hover:text-indigo-300 transition">N Circuits Technologies</a></p>
            </div>
        </div>
    </footer>

</body>
</html>
