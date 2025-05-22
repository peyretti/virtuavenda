<?php
/**
 * Gerador de imagens placeholder
 * URL: /placeholder.php?w=400&h=300&text=Produto
 */

// Configurações
$width = isset($_GET['w']) ? (int)$_GET['w'] : 400;
$height = isset($_GET['h']) ? (int)$_GET['h'] : 300;
$text = isset($_GET['text']) ? $_GET['text'] : '';
$bg_color = isset($_GET['bg']) ? $_GET['bg'] : 'f0f0f0';
$text_color = isset($_GET['color']) ? $_GET['color'] : '666666';

// Limitar dimensões
$width = max(50, min(2000, $width));
$height = max(50, min(2000, $height));

// Converter cores hex para RGB
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

// Criar imagem
$image = imagecreate($width, $height);

// Cores
$bg_rgb = hexToRgb($bg_color);
$text_rgb = hexToRgb($text_color);

$background = imagecolorallocate($image, $bg_rgb[0], $bg_rgb[1], $bg_rgb[2]);
$textcolor = imagecolorallocate($image, $text_rgb[0], $text_rgb[1], $text_rgb[2]);

// Texto padrão se não fornecido
if (empty($text)) {
    $text = $width . 'x' . $height;
}

// Calcular posição do texto
$font_size = min($width, $height) / 20;
$font_size = max(8, min(24, $font_size)); // Limitar tamanho da fonte

// Usar fonte padrão do sistema
$font_width = imagefontwidth(3);
$font_height = imagefontheight(3);
$text_width = $font_width * strlen($text);
$text_height = $font_height;

$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

// Adicionar texto
imagestring($image, 3, $x, $y, $text, $textcolor);

// Headers
header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400'); // Cache por 1 dia

// Gerar imagem
imagepng($image);
imagedestroy($image);
?>