<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Sistema de Gestão Pedagógica</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite CSS and JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a; /* Slate 900 */
            color: #f8fafc; /* Slate 50 */
        }
        
        .hero {
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.12) 0%, transparent 70%);
            animation: rotateBg 25s linear infinite;
            pointer-events: none;
        }
        
        @keyframes rotateBg {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .fadeInUp {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="border-b border-slate-800/80 bg-slate-900/40 backdrop-blur-md sticky top-0 z-50 transition duration-150">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-indigo-400 via-indigo-500 to-indigo-600 bg-clip-text text-transparent">
                    SGP
                </span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    Pedagógico
                </span>
            </a>

            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/10 transition">
                            Painel de Controle
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-bold text-slate-300 hover:text-white transition">
                            Entrar
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/10 transition">
                                Cadastrar Escola
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero min-h-[85vh] flex items-center pt-8 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left text content -->
                <div class="space-y-6 lg:col-span-7 text-center lg:text-left fadeInUp">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold">
                        <span>✨</span> Plataforma Completa de Gestão Escolar
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white">
                        Otimize e Monitore o <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">Desempenho Pedagógico</span> da sua Rede.
                    </h1>
                    
                    <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto lg:mx-0 leading-relaxed font-light">
                        Acompanhe em tempo real o envio de planejamentos de aulas de professores, organize turmas e coordenadores, realize avaliações com feedbacks integrados e visualize métricas em tempo real.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-2">
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 text-sm transition text-center">
                            Acessar o SGP
                        </a>
                        <a href="https://wa.me/5595991248941" target="_blank" class="px-6 py-3 border border-slate-700 hover:border-slate-500 hover:bg-slate-800/40 text-slate-300 font-semibold rounded-xl text-sm transition text-center flex items-center justify-center gap-2">
                            <span>💬</span> Falar com Suporte Técnico
                        </a>
                    </div>
                </div>

                <!-- Right visual graphic card -->
                <div class="lg:col-span-5 hidden lg:block fadeInUp" style="animation-delay: 0.2s;">
                    <div class="relative p-8 bg-slate-900/60 border border-slate-800 rounded-3xl backdrop-blur-md shadow-2xl">
                        <div class="absolute -top-3 -right-3 w-12 h-12 bg-pink-500/20 rounded-2xl blur-xl"></div>
                        <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-indigo-500/20 rounded-2xl blur-2xl"></div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            </div>
                            
                            <div class="space-y-2 pt-2">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Acompanhamento SEMED</h4>
                                <div class="h-6 w-3/4 bg-slate-800/60 rounded-lg"></div>
                                <div class="h-12 w-full bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center justify-between px-4">
                                    <span class="text-xs font-bold text-indigo-400">Escolas Monitoradas</span>
                                    <span class="text-xs font-extrabold text-white">100% da Rede</span>
                                </div>
                            </div>

                            <div class="space-y-2.5 pt-4">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Entrega de Planejamentos</h4>
                                <div class="grid grid-cols-3 gap-2.5">
                                    <div class="p-3 bg-slate-800/50 border border-slate-800 rounded-xl text-center">
                                        <div class="text-base font-extrabold text-emerald-400">92%</div>
                                        <div class="text-[9px] text-slate-500">Aprovados</div>
                                    </div>
                                    <div class="p-3 bg-slate-800/50 border border-slate-800 rounded-xl text-center">
                                        <div class="text-base font-extrabold text-amber-400">5%</div>
                                        <div class="text-[9px] text-slate-500">Ajustes</div>
                                    </div>
                                    <div class="p-3 bg-slate-800/50 border border-slate-800 rounded-xl text-center">
                                        <div class="text-base font-extrabold text-rose-400">3%</div>
                                        <div class="text-[9px] text-slate-500">Pendentes</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Features / Benefits Section -->
    <section class="py-20 bg-slate-900/40 border-t border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center space-y-3 max-w-3xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                    Benefícios e Funcionalidades do SGP
                </h2>
                <p class="text-slate-400 text-sm sm:text-base font-light">
                    Desenvolvido sob medida para solucionar as necessidades de controle pedagógico de escolas e da secretaria municipal de educação.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-16">
                
                <!-- Benefit 1 -->
                <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 hover:border-indigo-500/50 hover:shadow-lg transition group">
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xl font-bold flex items-center justify-center rounded-xl group-hover:scale-110 transition">
                        📅
                    </div>
                    <h3 class="text-lg font-bold text-white">Cronogramas e Prazos</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                        Crie e gerencie cronogramas e prazos limites de submissão de planos de aula na escola ou rede, permitindo visualizações diretas de datas.
                    </p>
                </div>

                <!-- Benefit 2 -->
                <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 hover:border-indigo-500/50 hover:shadow-lg transition group">
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xl font-bold flex items-center justify-center rounded-xl group-hover:scale-110 transition">
                        📄
                    </div>
                    <h3 class="text-lg font-bold text-white">Extração Inteligente (DOCX)</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                        Sistema com extração automatizada de texto e suporte exclusivo para uploads de arquivos do Microsoft Word (.docx), agilizando a leitura diretamente em tela.
                    </p>
                </div>

                <!-- Benefit 3 -->
                <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 hover:border-indigo-500/50 hover:shadow-lg transition group">
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xl font-bold flex items-center justify-center rounded-xl group-hover:scale-110 transition">
                        🏆
                    </div>
                    <h3 class="text-lg font-bold text-white">Gamificação e Rankings</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                        Incentivo à pontualidade de entregas através de pontuações de planos e atribuição de medalhas de destaque para escolas, coordenadores e professores.
                    </p>
                </div>

                <!-- Benefit 4 -->
                <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 hover:border-indigo-500/50 hover:shadow-lg transition group">
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xl font-bold flex items-center justify-center rounded-xl group-hover:scale-110 transition">
                        💬
                    </div>
                    <h3 class="text-lg font-bold text-white">Controle de Feedbacks</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                        Avaliação e controle de envios completo: atribua status de aprovação, notas de desempenho e envie retornos imediatos integrados ao WhatsApp.
                    </p>
                </div>

                <!-- Benefit 5 -->
                <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 hover:border-indigo-500/50 hover:shadow-lg transition group">
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xl font-bold flex items-center justify-center rounded-xl group-hover:scale-110 transition">
                        📊
                    </div>
                    <h3 class="text-lg font-bold text-white">Gestão Centralizada SEMED</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                        Painel gerencial para a Secretaria Municipal de Educação consolidar o monitoramento de todas as escolas em um único ambiente integrado.
                    </p>
                </div>

                <!-- Benefit 6 -->
                <div class="p-6 bg-slate-900/60 border border-slate-800 rounded-2xl space-y-4 hover:border-indigo-500/50 hover:shadow-lg transition group">
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xl font-bold flex items-center justify-center rounded-xl group-hover:scale-110 transition">
                        🖨️
                    </div>
                    <h3 class="text-lg font-bold text-white">Relatórios em A4</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">
                        Exportação rápida e facilitada de relatórios de envios, pontualidade e controle de pendências otimizados nativamente para impressão em formato folha A4.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-955 border-t border-slate-800/80 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 mb-12">
                
                <!-- Logo & description -->
                <div class="md:col-span-6 space-y-4">
                    <a href="/" class="flex items-center gap-2">
                        <span class="text-xl font-black bg-gradient-to-r from-indigo-400 via-indigo-500 to-indigo-600 bg-clip-text text-transparent">
                            SGP
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-indigo-500/10 text-indigo-400">
                            v2.0
                        </span>
                    </a>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed font-light max-w-md">
                        O SGP é um sistema moderno de acompanhamento escolar focado na eficiência pedagógica de redes municipais de ensino. Otimizamos o trabalho de professores, diretores e da secretaria de educação.
                    </p>
                    
                    <div class="pt-2">
                        <a href="https://wa.me/5595991248941" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                            <span>🟢</span> Fale Conosco no WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Services list -->
                <div class="md:col-span-3 space-y-4">
                    <h4 class="text-sm font-bold text-white tracking-wider uppercase border-l-2 border-indigo-500 pl-3">Serviços</h4>
                    <ul class="space-y-2 text-xs sm:text-sm text-slate-400 font-light">
                        <li>• Sistemas de gestão online sob medida</li>
                        <li>• Painéis administrativos e dashboards</li>
                        <li>• Desenvolvimento de sites e aplicações web</li>
                        <li>• Integração com APIs e automações</li>
                    </ul>
                </div>

                <!-- Specialized solutions -->
                <div class="md:col-span-3 space-y-4">
                    <h4 class="text-sm font-bold text-white tracking-wider uppercase border-l-2 border-indigo-500 pl-3">Soluções</h4>
                    <ul class="space-y-2 text-xs sm:text-sm text-slate-400 font-light">
                        <li>• Sistema de Gestão Pedagógica (SGP)</li>
                        <li>• Gestão de Jogos Escolares (Sistema JEM)</li>
                        <li>• Consultoria em Transformação Digital</li>
                        <li>• Hospedagem e suporte especializado</li>
                    </ul>
                </div>

            </div>

            <!-- Copyright -->
            <div class="pt-8 border-t border-slate-900/60 text-center flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500">
                <p>&copy; 2026 SGP - Sistema de Gestão Pedagógica. Todos os direitos reservados.</p>
                <p>Desenvolvido por <a href="https://wa.me/5595991248941" target="_blank" class="font-bold text-indigo-400 hover:text-indigo-300">N Circuits Technologies</a></p>
            </div>
        </div>
    </footer>

</body>
</html>
