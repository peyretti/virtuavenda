<?php
/**
 * Arquivo para diagnóstico de problemas
 * Acesse: http://localhost/virtuavenda/frontend/debug.php
 */

// Habilitar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>🔍 Diagnóstico VirtuaVenda</h1>";

// 1. Verificar PHP
echo "<h2>📋 Informações do PHP:</h2>";
echo "<p><strong>Versão PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Diretório atual:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// 2. Verificar arquivos
echo "<h2>📁 Verificação de Arquivos:</h2>";
$files_to_check = [
    'config/api.php',
    'includes/header.php',
    'includes/footer.php',
    'assets/css/style.css',
    'assets/js/main.js',
    'assets/js/cart.js',
    'index.php'
];

foreach ($files_to_check as $file) {
    $status = file_exists($file) ? '✅ Existe' : '❌ Não existe';
    $path = $file_exists ? realpath($file) : 'N/A';
    echo "<p><strong>$file:</strong> $status</p>";
}

// 3. Testar config/api.php
echo "<h2>⚙️ Teste config/api.php:</h2>";
try {
    if (file_exists('config/api.php')) {
        require_once 'config/api.php';
        echo "<p>✅ config/api.php carregado com sucesso</p>";
        
        // Testar função
        if (function_exists('getStoreData')) {
            echo "<p>✅ Função getStoreData() existe</p>";
        } else {
            echo "<p>❌ Função getStoreData() não encontrada</p>";
        }
    } else {
        echo "<p>❌ config/api.php não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar config/api.php: " . $e->getMessage() . "</p>";
}

// 4. Verificar .htaccess
echo "<h2>🔧 Verificação .htaccess:</h2>";
if (file_exists('.htaccess')) {
    echo "<p>✅ .htaccess existe</p>";
    
    // Verificar se mod_rewrite está habilitado
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        if (in_array('mod_rewrite', $modules)) {
            echo "<p>✅ mod_rewrite habilitado</p>";
        } else {
            echo "<p>❌ mod_rewrite não habilitado</p>";
        }
    } else {
        echo "<p>⚠️ Não foi possível verificar mod_rewrite</p>";
    }
} else {
    echo "<p>❌ .htaccess não encontrado</p>";
}

// 5. Verificar permissões
echo "<h2>🔐 Verificação de Permissões:</h2>";
$dirs_to_check = ['.', 'config', 'includes', 'assets', 'assets/css', 'assets/js'];

foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $readable = is_readable($dir) ? '✅' : '❌';
        $writable = is_writable($dir) ? '✅' : '❌';
        echo "<p><strong>$dir/:</strong> Permissões: $perms | Leitura: $readable | Escrita: $writable</p>";
    } else {
        echo "<p><strong>$dir/:</strong> ❌ Diretório não existe</p>";
    }
}

// 6. Teste simples do index.php
echo "<h2>🏠 Teste do index.php:</h2>";
try {
    // Verificar se consegue iniciar sessão
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "<p>✅ Sessão iniciada com sucesso</p>";
    }
    
    // Verificar se consegue carregar o header
    ob_start();
    $pageTitle = "Teste";
    $pageDescription = "Teste";
    $currentPage = "test";
    $storeData = ['nome_loja' => 'Teste'];
    $categories = [];
    $themeConfig = [];
    
    // include 'includes/header.php'; // Comentado para não quebrar o debug
    ob_end_clean();
    
    echo "<p>✅ Variáveis do header definidas com sucesso</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro no teste do index.php: " . $e->getMessage() . "</p>";
}

// 7. Log de erros do PHP
echo "<h2>📋 Log de Erros:</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    echo "<p><strong>Arquivo de log:</strong> $error_log</p>";
    $lines = file($error_log);
    $recent_lines = array_slice($lines, -10); // Últimas 10 linhas
    echo "<pre>" . implode('', $recent_lines) . "</pre>";
} else {
    echo "<p>⚠️ Log de erros não encontrado ou não configurado</p>";
}

// 8. Informações do servidor
echo "<h2>🖥️ Informações do Servidor:</h2>";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</p>";
echo "<p><strong>SERVER_SOFTWARE:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</p>";

echo "<h2>✅ Diagnóstico Concluído</h2>";
echo "<p>Se você encontrou erros acima, eles podem estar causando o problema 500.</p>";
?>