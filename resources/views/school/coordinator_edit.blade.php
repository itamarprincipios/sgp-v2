<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Editar Coordenador') }}
        </h2>
    </x-slot>

    <div class="max-w-lg mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8">
        <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-6">
            <span class="text-2xl">💼</span>
            <div>
                <h3 class="text-base font-bold text-slate-900">Editar Cadastro</h3>
                <p class="text-xs text-slate-500">Atualize os dados e vínculos do coordenador pedagógico.</p>
            </div>
        </div>

        <form action="{{ route('school.coordinator.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="id" value="{{ $coordinator->id }}">

            @if(count($schools) > 1)
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600">Escola Vinculada</label>
                    <select name="school_id" required class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}" {{ $coordinator->school_id == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="school_id" value="{{ $schools[0]->id ?? '' }}">
            @endif

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-600">Nome Completo</label>
                <input type="text" name="name" required value="{{ $coordinator->name }}" placeholder="Ex: Milza Souza" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-600">E-mail de Login</label>
                <input type="email" name="email" required value="{{ $coordinator->email }}" placeholder="Ex: milza@sgp.com" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-600">WhatsApp</label>
                <input type="text" name="whatsapp" value="{{ $coordinator->whatsapp }}" placeholder="Ex: 5595999999999" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('school.dashboard') }}?tab=coordinators" class="px-4 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 font-semibold rounded-xl text-xs transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow transition cursor-pointer">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
