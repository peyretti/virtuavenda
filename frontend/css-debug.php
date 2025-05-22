<?php
/**
 * Debugger completo do CSS para encontrar o problema
 * Execute: http://localhost/virtuavenda/frontend/css-debug.php
 */

echo "<h1>🔍 CSS Debugger Completo</h1>";

// 1. Verificar estrutura de pastas
echo "<h2>📁 1. Estrutura de Pastas:</h2>";

$paths = [
    '.' => 'Pasta raiz',
    './assets' => 'Pasta assets',
    './assets/css' => 'Pasta css',
    './assets/css/style.css' => 'Arquivo CSS'
];

foreach ($paths as $path => $description) {
    $exists = file_exists($path);
    $isDir = is_dir($path);
    $isFile = is_file($path);
    
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<strong>$description:</strong> $path<br>";
    echo "Existe: " . ($exists ? '✅ Sim' : '❌ Não') . "<br>";
    
    if ($exists) {
        echo "Tipo: " . ($isDir ? '📁 Pasta' : ($isFile ? '📄 Arquivo' : '❓ Desconhecido')) . "<br>";
        
        if ($isFile) {
            $size = filesize($path);
            echo "Tamanho: " . number_format($size / 1024, 2) . " KB<br>";
        }
        
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $readable = is_readable($path) ? '✅' : '❌';
        echo "Permissões: $perms | Leitura: $readable<br>";
    }
    echo "</div>";
}

// 2. Testar URLs de acesso
echo "<h2>🌐 2. Teste de URLs:</h2>";

$urls = [
    './assets/css/style.css' => 'CSS Local',
    'assets/css/style.css' => 'CSS Relativo'
];

foreach ($urls as $url => $description) {
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<strong>$description:</strong><br>";
    echo "URL: <a href='$url' target='_blank'>$url</a><br>";
    
    // Testar se o arquivo é acessível via HTTP
    $full_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . ltrim($url, './');
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'HEAD'
        ]
    ]);
    
    $headers = @get_headers($full_url, 1, $context);
    $http_accessible = $headers && strpos($headers[0], '200') !== false;
    
    echo "URL completa: <a href='$full_url' target='_blank'>$full_url</a><br>";
    echo "Acesso HTTP: " . ($http_accessible ? '✅ OK' : '❌ Erro') . "<br>";
    
    if (!$http_accessible && $headers) {
        echo "Status: " . $headers[0] . "<br>";
    }
    echo "</div>";
}

// 3. Analisar o index.php
echo "<h2>📄 3. Análise do index.php:</h2>";

if (file_exists('./index.php')) {
    $index_content = file_get_contents('./index.php');
    
    // Procurar referências ao CSS
    $css_patterns = [
        '/cdn\.tailwindcss\.com/' => 'Tailwind CDN',
        '/assets\/css\/style\.css/' => 'CSS Local',
        '/\.\/assets\/css\/style\.css/' => 'CSS Relativo'
    ];
    
    foreach ($css_patterns as $pattern => $description) {
        $matches = preg_match_all($pattern, $index_content, $results);
        $status = $matches > 0 ? "✅ Encontrado ($matches vezes)" : "❌ Não encontrado";
        echo "<p><strong>$description:</strong> $status</p>";
    }
    
    // Mostrar as linhas que referenciam CSS
    $lines = explode("\n", $index_content);
    echo "<h4>Linhas com referências ao CSS:</h4>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    
    foreach ($lines as $number => $line) {
        if (stripos($line, 'css') !== false || stripos($line, 'tailwind') !== false) {
            echo sprintf("%03d: %s\n", $number + 1, htmlspecialchars($line));
        }
    }
    echo "</pre>";
    
} else {
    echo "<p>❌ index.php não encontrado</p>";
}

// 4. Verificar includes/header.php
echo "<h2>📄 4. Análise do header.php:</h2>";

if (file_exists('./includes/header.php')) {
    $header_content = file_get_contents('./includes/header.php');
    
    // Procurar referências ao CSS
    foreach ($css_patterns as $pattern => $description) {
        $matches = preg_match_all($pattern, $header_content, $results);
        $status = $matches > 0 ? "✅ Encontrado ($matches vezes)" : "❌ Não encontrado";
        echo "<p><strong>$description:</strong> $status</p>";
    }
    
    // Mostrar as linhas que referenciam CSS
    $lines = explode("\n", $header_content);
    echo "<h4>Linhas com referências ao CSS:</h4>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    
    foreach ($lines as $number => $line) {
        if (stripos($line, 'css') !== false || stripos($line, 'tailwind') !== false) {
            echo sprintf("%03d: %s\n", $number + 1, htmlspecialchars($line));
        }
    }
    echo "</pre>";
    
} else {
    echo "<p>❌ includes/header.php não encontrado</p>";
}

// 5. Informações do servidor
echo "<h2>🖥️ 5. Informações do Servidor:</h2>";

$server_info = [
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'Script Name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
    'Request URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'HTTP Host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
    'Current Directory' => __DIR__,
    'Real Path' => realpath('.') ?: 'N/A'
];

foreach ($server_info as $key => $value) {
    echo "<p><strong>$key:</strong> $value</p>";
}

// 6. Testar criação manual do CSS
echo "<h2>🛠️ 6. Criar CSS Manualmente:</h2>";

if (!file_exists('./assets/css/style.css')) {
    echo "<p>⚠️ CSS não existe. Tentando criar...</p>";
    
    // Criar diretórios se não existirem
    if (!is_dir('./assets')) {
        $created = mkdir('./assets', 0755, true);
        echo "<p>" . ($created ? "✅" : "❌") . " Criando pasta assets</p>";
    }
    
    if (!is_dir('./assets/css')) {
        $created = mkdir('./assets/css', 0755, true);
        echo "<p>" . ($created ? "✅" : "❌") . " Criando pasta css</p>";
    }
    
    // CSS mínimo para teste
    $minimal_css = '/* VirtuaVenda - CSS Teste */
body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
.flex { display: flex; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.bg-primary-600 { background-color: #2563eb; }
.text-white { color: white; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.rounded-lg { border-radius: 0.5rem; }
.shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

/* Responsive */
@media (min-width: 768px) {
  .md\\:flex { display: flex; }
  .md\\:hidden { display: none; }
  .md\\:block { display: block; }
  .md\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

/* Grid */
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.gap-6 { gap: 1.5rem; }

/* Gradiente */
.gradient-bg {
    background: linear-gradient(135deg, #3b82f6 0%, #764ba2 100%);
}';
    
    $bytes = file_put_contents('./assets/css/style.css', $minimal_css);
    
    if ($bytes !== false) {
        echo "<div style='padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<p>✅ <strong>CSS criado com sucesso!</strong></p>";
        echo "<p>Tamanho: " . number_format($bytes / 1024, 2) . " KB</p>";
        echo "<p>Localização: ./assets/css/style.css</p>";
        echo "</div>";
    } else {
        echo "<div style='padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<p>❌ <strong>Erro ao criar CSS</strong></p>";
        echo "<p>Verifique as permissões das pastas</p>";
        echo "</div>";
    }
} else {
    $size = filesize('./assets/css/style.css');
    echo "<p>✅ CSS já existe (Tamanho: " . number_format($size / 1024, 2) . " KB)</p>";
}

// 7. Teste final
echo "<h2>🧪 7. Teste Final:</h2>";

echo "<div style='margin: 20px 0;'>";
echo "<h4>Teste de classes CSS:</h4>";
echo "<div class='flex items-center justify-between' style='border: 1px solid #ddd; padding: 10px; border-radius: 5px;'>";
echo "<span>Se este texto está alinhado corretamente com flexbox, o CSS funciona!</span>";
echo "<button class='bg-primary-600 text-white px-4 py-2 rounded-lg'>Botão Teste</button>";
echo "</div>";
echo "</div>";

echo "<hr>";
echo "<h2>🚀 Soluções:</h2>";

echo "<div style='padding: 20px; background: #e3f2fd; border: 1px solid #90caf9; border-radius: 5px;'>";
echo "<h3>Se o CSS ainda não funcionar:</h3>";
echo "<ol>";
echo "<li>Verifique se o arquivo existe em: <code>./assets/css/style.css</code></li>";
echo "<li>Teste a URL direta: <a href='./assets/css/style.css' target='_blank'>./assets/css/style.css</a></li>";
echo "<li>Verifique as permissões das pastas</li>";
echo "<li>Limpe o cache do navegador (Ctrl+F5)</li>";
echo "<li>Se necessário, use caminho absoluto no HTML</li>";
echo "</ol>";
echo "</div>";

echo "<p style='text-align: center; margin-top: 30px;'>";
echo "<a href='./' style='display: inline-block; background: #3b82f6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;'>🏠 Voltar ao Site</a>";
echo "</p>";

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2, h3 { color: #333; }
code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
pre { max-height: 300px; overflow-y: auto; }
</style>";
?>