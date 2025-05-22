<?php
namespace App\Utils;

class Response {
    /**
     * Envia uma resposta JSON
     * 
     * @param mixed $data Dados a serem convertidos para JSON
     * @param int $statusCode Código de status HTTP
     * @param array $headers Cabeçalhos HTTP adicionais
     */
    public static function json($data, $statusCode = 200, $headers = []) {
        // Define o código de status HTTP
        http_response_code($statusCode);
        
        // Define os cabeçalhos padrão
        header('Content-Type: application/json; charset=utf-8');
        
        // Adiciona cabeçalhos CORS para permitir acesso entre origens
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        // Adiciona cabeçalhos personalizados
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
        
        // Converte os dados para JSON e envia a resposta
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Envia uma resposta de sucesso
     * 
     * @param mixed $data Dados a serem enviados
     * @param int $statusCode Código de status HTTP
     */
    public static function success($data = null, $statusCode = 200) {
        $response = [
            'status' => 'success',
            'data' => $data
        ];
        
        self::json($response, $statusCode);
    }
    
    /**
     * Envia uma resposta de erro
     * 
     * @param string $message Mensagem de erro
     * @param int $statusCode Código de status HTTP
     * @param mixed $errors Erros adicionais
     */
    public static function error($message, $statusCode = 400, $errors = null) {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        self::json($response, $statusCode);
    }
}