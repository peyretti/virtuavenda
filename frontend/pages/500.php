<?php
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro interno - VirtuaVenda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto text-center px-4">
        <div class="mb-8">
            <i class="fas fa-exclamation-triangle text-6xl text-red-400 mb-4"></i>
            <h1 class="text-6xl font-bold text-gray-800 mb-2">500</h1>
            <h2 class="text-2xl font-semibold text-gray-600 mb-4">Erro interno do servidor</h2>
            <p class="text-gray-500 mb-8">
                Ocorreu um erro inesperado. Nossa equipe foi notificada e está trabalhando para resolver o problema.
            </p>
        </div>
        
        <div class="space-y-4">
            <button onclick="location.reload()" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-refresh mr-2"></i>
                Tentar Novamente
            </button>
            
            <div class="mt-4">
                <a href="/" class="inline-block bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Voltar ao Início
                </a>
            </div>
            
            <div class="mt-4">
                <a href="/contato" class="text-blue-600 hover:text-blue-700">Reportar Problema</a>
            </div>
        </div>
    </div>
</body>
</html>