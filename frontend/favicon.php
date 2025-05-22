<?php
/**
 * Gerador de favicon simples
 * Salve como: assets/images/favicon.php (temporário)
 * Depois renomeie para favicon.ico
 */

// Criar uma imagem 32x32 para favicon
$size = 32;
$image = imagecreate($size, $size);

// Cores
$bg_color = imagecolorallocate($image, 59, 130, 246); // Azul primário
$text_color = imagecolorallocate($image, 255, 255, 255); // Branco

// Desenhar um "V" simples para VirtuaVenda
$points = array(
    6, 8,   // ponto 1
    16, 8,  // ponto 2
    11, 20, // ponto 3
    6, 8    // volta ao ponto 1
);

imagefilledpolygon($image, $points, 3, $text_color);

// Desenhar outro lado do "V"
$points2 = array(
    16, 8,  // ponto 1
    26, 8,  // ponto 2
    21, 20, // ponto 3
    16, 8   // volta ao ponto 1
);

imagefilledpolygon($image, $points2, 3, $text_color);

// Headers para ICO (vai funcionar como PNG na maioria dos browsers)
header('Content-Type: image/png');
header('Cache-Control: public, max-age=2592000'); // Cache por 30 dias

// Gerar imagem
imagepng($image);
imagedestroy($image);
?>