<?php
/**
 * Verificador rápido de arquivos
 * Acesse: http://localhost/virtuavenda/frontend/check-files.php
 */

echo "<h1>🔍 Verificação de Arquivos CSS e JS</h1>";

$files_to_check = [
    './assets/css/style.css' => 'CSS Principal',
    './assets/js/main.js' => 'JavaScript Principal',
    './assets/js/cart.js' => 'JavaScript do Carrinho',
    './favicon.php' => 'Favicon Dinâmico',
    './manifest.json' => 'PWA Manifest',
    './sw.js' => 'Service Worker'
];

echo "<h2>📋 Status dos Arquivos:</h2>";

foreach ($files_to_check as $file => $description) {
    $exists = file_exists($file);
    $size = $exists ? filesize($file) : 0;
    $readable = $exists ? is_readable($file) : false;
    
    $status_icon = $exists ? '✅' : '❌';
    $status_text = $exists ? 'Existe' : 'Não encontrado';
    
    echo "<div style='margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<strong>$description</strong><br>";
    echo "📁 Arquivo: <code>$file</code><br>";
    echo "📊 Status: $status_icon $status_text<br>";
    
    if ($exists) {
        echo "📏 Tamanho: " . number_format($size / 1024, 2) . " KB<br>";
        echo "🔓 Legível: " . ($readable ? '✅ Sim' : '❌ Não') . "<br>";
        
        // Teste de acesso via HTTP
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $file;
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'method' => 'HEAD'
            ]
        ]);
        
        $headers = @get_headers($url, 1, $context);
        $http_accessible = $headers && strpos($headers[0], '200') !== false;
        
        echo "🌐 Acesso HTTP: " . ($http_accessible ? '✅ OK' : '❌ Erro') . "<br>";
        echo "🔗 URL: <a href='$url' target='_blank'>$url</a>";
    }
    
    echo "</div>";
}

// Verificar estrutura de pastas
echo "<h2>📁 Estrutura de Pastas:</h2>";

$folders = ['assets', 'assets/css', 'assets/js', 'assets/images', 'pages', 'config', 'includes'];

foreach ($folders as $folder) {
    $exists = is_dir($folder);
    $writable = $exists ? is_writable($folder) : false;
    
    $status_icon = $exists ? '✅' : '❌';
    echo "<p>$status_icon <strong>$folder/</strong> - ";
    echo $exists ? 'Existe' : 'Não existe';
    if ($exists) {
        echo " | Escrita: " . ($writable ? '✅' : '❌');
    }
    echo "</p>";
}

// Teste rápido do CSS
echo "<h2>🎨 Teste do CSS:</h2>";

if (file_exists('./assets/css/style.css')) {
    echo "<div style='width: 100px; height: 50px; background: linear-gradient(135deg, #3b82f6 0%, #764ba2 100%); border-radius: 8px; margin: 10px 0;'></div>";
    echo "<p>✅ Se você vê um retângulo com gradiente azul/roxo acima, o CSS está funcionando!</p>";
} else {
    echo "<p>❌ CSS não encontrado - criando arquivo básico...</p>";
    
    // Criar CSS básico se não existir
    $basic_css = "/* CSS Básico VirtuaVenda */\nbody { font-family: Arial, sans-serif; margin: 0; padding: 0; }\n.text-primary-600 { color: #2563eb; }\n.bg-primary-600 { background-color: #2563eb; }";
    
    if (!is_dir('./assets')) mkdir('./assets', 0755, true);
    if (!is_dir('./assets/css')) mkdir('./assets/css', 0755, true);
    
    if (file_put_contents('./assets/css/style.css', $basic_css)) {
        echo "<p>✅ CSS básico criado!</p>";
    } else {
        echo "<p>❌ Erro ao criar CSS</p>";
    }
}

echo "<hr>";
echo "<h2>🚀 Próximos Passos:</h2>";
echo "<ul>";
echo "<li>Se algum arquivo estiver faltando, crie-o manualmente</li>";
echo "<li>Verifique as permissões das pastas</li>";
echo "<li>Teste o site novamente</li>";
echo "</ul>";

echo "<p><a href='./'>← Voltar para o site</a></p>";
?>