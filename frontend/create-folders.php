<?php
/**
 * Script para criar a estrutura de pastas do VirtuaVenda
 * Execute uma vez: http://localhost/virtuavenda/frontend/create-folders.php
 */

echo "<h1>🏗️ Criando Estrutura de Pastas</h1>";

// Lista de pastas para criar
$folders = [
    'assets',
    'assets/css',
    'assets/js',
    'assets/images',
    'assets/images/products',
    'assets/icons',
    'ajax',
    'components',
    'pages',
    'config',
    'includes'
];

$created = 0;
$existed = 0;

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        if (mkdir($folder, 0755, true)) {
            echo "<p>✅ Pasta criada: <strong>$folder/</strong></p>";
            $created++;
        } else {
            echo "<p>❌ Erro ao criar: <strong>$folder/</strong></p>";
        }
    } else {
        echo "<p>📁 Já existe: <strong>$folder/</strong></p>";
        $existed++;
    }
}

echo "<hr>";
echo "<h2>📊 Resumo:</h2>";
echo "<p><strong>Pastas criadas:</strong> $created</p>";
echo "<p><strong>Já existiam:</strong> $existed</p>";

// Verificar permissões
echo "<hr>";
echo "<h2>🔐 Verificação de Permissões:</h2>";

foreach ($folders as $folder) {
    if (is_dir($folder)) {
        $perms = substr(sprintf('%o', fileperms($folder)), -4);
        $writable = is_writable($folder) ? '✅ Escrita OK' : '❌ Sem permissão de escrita';
        echo "<p><strong>$folder/:</strong> $perms - $writable</p>";
    }
}

// Criar arquivos .gitkeep para pastas vazias
echo "<hr>";
echo "<h2>📝 Criando arquivos .gitkeep:</h2>";

$empty_folders = [
    'assets/images/products',
    'assets/icons'
];

foreach ($empty_folders as $folder) {
    $gitkeep_file = $folder . '/.gitkeep';
    if (!file_exists($gitkeep_file)) {
        if (file_put_contents($gitkeep_file, '# Pasta mantida no Git')) {
            echo "<p>✅ Criado: <strong>$gitkeep_file</strong></p>";
        } else {
            echo "<p>❌ Erro ao criar: <strong>$gitkeep_file</strong></p>";
        }
    } else {
        echo "<p>📄 Já existe: <strong>$gitkeep_file</strong></p>";
    }
}

echo "<hr>";
echo "<h2>✅ Estrutura de Pastas Criada!</h2>";
echo "<p>Agora você pode:</p>";
echo "<ul>";
echo "<li>Adicionar imagens em <strong>assets/images/products/</strong></li>";
echo "<li>Personalizar CSS em <strong>assets/css/style.css</strong></li>";
echo "<li>Criar páginas PHP na raiz</li>";
echo "<li>Adicionar componentes em <strong>components/</strong></li>";
echo "</ul>";

echo "<p><a href='./'>← Voltar para o site</a></p>";
?>