<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - VirtuaVenda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto text-center px-4">
        <div class="mb-8">
            <i class="fas fa-search text-6xl text-gray-400 mb-4"></i>
            <h1 class="text-6xl font-bold text-gray-800 mb-2">404</h1>
            <h2 class="text-2xl font-semibold text-gray-600 mb-4">Página não encontrada</h2>
            <p class="text-gray-500 mb-8">
                A página que você está procurando não existe ou foi movida.
            </p>
        </div>
        
        <div class="space-y-4">
            <a href="/" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Voltar ao Início
            </a>
            
            <div class="mt-4">
                <a href="/produtos" class="text-blue-600 hover:text-blue-700 mr-4">Ver Produtos</a>
                <a href="/contato" class="text-blue-600 hover:text-blue-700">Contato</a>
            </div>
        </div>
    </div>
</body>
</html>