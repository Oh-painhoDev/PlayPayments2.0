<div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
    <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b-2 border-gray-200">
        <h3 class="text-lg font-bold text-gray-900 flex items-center">
            <span class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </span>
            Informações do Template
        </h3>
    </div>

    <div class="p-6 space-y-6">
        <!-- Nome -->
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">
                📝 Nome do Template
                <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" required
                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                placeholder="Ex: Template de Chargeback Padrão">
        </div>

        <!-- Descrição -->
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">
                📄 Descrição (opcional)
            </label>
            <textarea name="description" rows="3"
                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                placeholder="Descreva o propósito deste template...">{{ old('description', $template->description ?? '') }}</textarea>
        </div>

        <!-- Tipo de Disputa e Nível de Risco -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    ⚠️ Tipo de Disputa
                    <span class="text-red-500">*</span>
                </label>
                <select name="dispute_type" required
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <option value="">Selecione um tipo</option>
                    <option value="chargeback" {{ old('dispute_type', $template->dispute_type ?? '') == 'chargeback' ? 'selected' : '' }}>Chargeback</option>
                    <option value="fraud" {{ old('dispute_type', $template->dispute_type ?? '') == 'fraud' ? 'selected' : '' }}>Fraude</option>
                    <option value="unauthorized" {{ old('dispute_type', $template->dispute_type ?? '') == 'unauthorized' ? 'selected' : '' }}>Não Autorizada</option>
                    <option value="not_received" {{ old('dispute_type', $template->dispute_type ?? '') == 'not_received' ? 'selected' : '' }}>Não Recebido</option>
                    <option value="defective" {{ old('dispute_type', $template->dispute_type ?? '') == 'defective' ? 'selected' : '' }}>Defeituoso</option>
                    <option value="other" {{ old('dispute_type', $template->dispute_type ?? '') == 'other' ? 'selected' : '' }}>Outro</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    🎯 Nível de Risco
                    <span class="text-red-500">*</span>
                </label>
                <select name="risk_level" required
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <option value="">Selecione um nível</option>
                    <option value="LOW" {{ old('risk_level', $template->risk_level ?? '') == 'LOW' ? 'selected' : '' }}>Baixo</option>
                    <option value="MED" {{ old('risk_level', $template->risk_level ?? '') == 'MED' ? 'selected' : '' }}>Médio (Bloqueia Saldo)</option>
                    <option value="HIGH" {{ old('risk_level', $template->risk_level ?? '') == 'HIGH' ? 'selected' : '' }}>Alto</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">* Nível MÉDIO bloqueia o saldo automaticamente</p>
            </div>
        </div>

        <!-- Título da Mensagem -->
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">
                📧 Título da Mensagem (opcional)
            </label>
            <input type="text" name="message_title" value="{{ old('message_title', $template->message_title ?? '') }}"
                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                placeholder="Ex: Infração detectada em sua transação">
            <p class="text-xs text-gray-500 mt-1">Será usado como título da notificação enviada ao cliente</p>
        </div>

        <!-- Corpo da Mensagem -->
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">
                💬 Corpo da Mensagem (opcional)
            </label>
            <textarea name="message_body" rows="5"
                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                placeholder="Mensagem detalhada que será enviada ao cliente...">{{ old('message_body', $template->message_body ?? '') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">
                Variáveis disponíveis: {nome}, {valor}, {data}, {id_transacao}
            </p>
        </div>

        <!-- Status Ativo -->
        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" 
                {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}
                class="h-5 w-5 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
            <label for="is_active" class="ml-3 text-sm font-semibold text-gray-700">
                Template ativo (disponível para uso em disparos em massa)
            </label>
        </div>

        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">💡 Dica:</p>
                    <p>Este template poderá ser selecionado ao criar infrações em massa. As configurações de tipo e risco serão aplicadas automaticamente às infrações criadas.</p>
                </div>
            </div>
        </div>
    </div>
</div>
