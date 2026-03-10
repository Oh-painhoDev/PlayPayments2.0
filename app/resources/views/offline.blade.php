<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PlayPayments - Sem Conexão</title>
    <meta name="theme-color" content="#161616">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #000000; color: #ffffff; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="text-center p-6 bg-[#161616] rounded-2xl border border-white/5 max-w-sm w-full mx-4 shadow-2xl">
        <div class="mb-6 flex justify-center">
            <svg class="w-16 h-16 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold mb-2">Sem Conexão</h1>
        <p class="text-gray-400 text-sm mb-6">Parece que você está offline. Verifique sua conexão com a internet para acessar o PlayPayments.</p>
        <button onclick="window.location.reload()" class="w-full bg-[#D4AF37] hover:bg-[#c4a133] text-black font-bold py-3 px-4 rounded-xl transition-colors shadow-lg">
            Tentar Novamente
        </button>
    </div>
</body>
</html>
