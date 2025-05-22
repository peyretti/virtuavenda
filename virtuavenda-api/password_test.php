<?php
// Configurações para exibir erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Dados para teste
$stored_hash = '$2y$10$Q8H7NxNrvngs4WFHBb6wweMFB1Ud1pBI9cZ5jgrR8Z7pI3lMg88Ky'; // Hash para "teste123"
$test_password = 'cF2_afea19e5b9c4';

// Mostrar informações
echo "<h2>Teste de Verificação de Senha</h2>";
echo "<p>Senha de teste: $test_password</p>";
echo "<p>Hash armazenado: $stored_hash</p>";

// Testar verificação
$result = password_verify($test_password, $stored_hash);
echo "<p>Resultado: " . ($result ? 'VÁLIDO ✓' : 'INVÁLIDO ✗') . "</p>";

// Verificar configurações do PHP
echo "<h2>Informações do PHP</h2>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";
echo "<p>Algoritmo de hash padrão: " . password_algos()[PASSWORD_DEFAULT] . "</p>";

// Gerar um novo hash para a senha
$new_hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "<h2>Geração de Novo Hash</h2>";
echo "<p>Novo hash gerado: $new_hash</p>";
echo "<p>Validação com novo hash: " . (password_verify($test_password, $new_hash) ? 'VÁLIDO ✓' : 'INVÁLIDO ✗') . "</p>";

// Comparação direta de strings (apenas para diagnóstico)
echo "<h2>Comparação Direta (Para Diagnóstico)</h2>";
echo "<p>Comparação simples: " . ($test_password === 'cF2_afea19e5b9c4' ? 'IGUAL' : 'DIFERENTE') . "</p>";