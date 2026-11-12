<?php

$dir = __DIR__ . '/storage/app/public/products';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$fontBold = 'C:/Windows/Fonts/segoeuib.ttf';
$fontRegular = 'C:/Windows/Fonts/segoeui.ttf';
if (!file_exists($fontBold)) {
    $fontBold = 'C:/Windows/Fonts/arialbd.ttf';
    $fontRegular = 'C:/Windows/Fonts/arial.ttf';
}

$products = [
    [
        'filename' => 'cakra-kembar-1kg.png',
        'sku' => 'TPG-CKR-01KG',
        'category' => 'TEPUNG TERIGU PROTEIN TINGGI',
        'brand' => 'BOGASARI',
        'title' => 'CAKRA KEMBAR',
        'subtitle' => 'PREMIUM FLOUR',
        'weight' => 'Netto: 1 KG (1.000 gr)',
        'note' => 'Cocok Untuk Roti Manis & Donat',
        'bg_start' => [24, 43, 73],    // Deep Bogasari Navy
        'bg_end' => [12, 23, 42],
        'accent' => [245, 158, 11],    // Golden wheat
        'badge_bg' => [217, 119, 6],
        'badge_text' => [255, 255, 255],
        'icon_type' => 'wheat',
    ],
    [
        'filename' => 'segitiga-biru-1kg.png',
        'sku' => 'TPG-SGT-01KG',
        'category' => 'TEPUNG TERIGU SERBAGUNA',
        'brand' => 'BOGASARI',
        'title' => 'SEGITIGA BIRU',
        'subtitle' => 'ALL PURPOSE FLOUR',
        'weight' => 'Netto: 1 KG (1.000 gr)',
        'note' => 'Untuk Aneka Cake, Bolu & Gorengan',
        'bg_start' => [14, 116, 144],   // Cyan / Marine Blue
        'bg_end' => [8, 47, 73],
        'accent' => [56, 189, 248],
        'badge_bg' => [2, 132, 199],
        'badge_text' => [255, 255, 255],
        'icon_type' => 'triangle',
    ],
    [
        'filename' => 'blue-band-master-1kg.png',
        'sku' => 'MGR-BLB-01KG',
        'category' => 'MARGARIN KHUSUS KUE',
        'brand' => 'BLUE BAND',
        'title' => 'MASTER CAKE',
        'subtitle' => 'MARGARINE AROMA BUTTER',
        'weight' => 'Kemasan: 1 KG (1.000 gr)',
        'note' => 'Aroma Butter Khas & Lembut',
        'bg_start' => [29, 78, 216],   // Blue Band Royal Blue
        'bg_end' => [15, 23, 42],
        'accent' => [250, 204, 21],    // Butter Yellow
        'badge_bg' => [234, 179, 8],
        'badge_text' => [15, 23, 42],
        'icon_type' => 'butter',
    ],
    [
        'filename' => 'anchor-butter-227g.png',
        'sku' => 'BTR-ANC-227G',
        'category' => 'PURE NEW ZEALAND BUTTER',
        'brand' => 'ANCHOR',
        'title' => 'UNSALTED BUTTER',
        'subtitle' => 'MENTEGA MURNI ALAMI',
        'weight' => 'Berat Bersih: 227 Gram',
        'note' => 'Simpan Pada Suhu Chiller 2-6 C',
        'bg_start' => [15, 45, 90],    // Anchor Deep Classic Blue
        'bg_end' => [5, 18, 38],
        'accent' => [253, 224, 71],    // Golden Cream
        'badge_bg' => [225, 29, 72],   // Anchor Red accent
        'badge_text' => [255, 255, 255],
        'icon_type' => 'chiller',
    ],
    [
        'filename' => 'colatta-dark-1kg.png',
        'sku' => 'CKT-CLT-DRK1K',
        'category' => 'PREMIUM COMPOUND CHOCOLATE',
        'brand' => 'COLATTA',
        'title' => 'DARK COMPOUND',
        'subtitle' => 'COKELAT BLOK INTENSE',
        'weight' => 'Netto: 1 KG (1.000 gr)',
        'note' => 'Mudah Leleh, Mengkilap & Nyata',
        'bg_start' => [66, 32, 16],    // Rich Chocolate Brown
        'bg_end' => [26, 12, 6],
        'accent' => [245, 158, 11],    // Rich Gold
        'badge_bg' => [180, 83, 9],
        'badge_text' => [255, 255, 255],
        'icon_type' => 'chocolate',
    ],
    [
        'filename' => 'prochiz-gold-2kg.png',
        'sku' => 'KJU-PRC-GLD2K',
        'category' => 'KEJU CHEDDAR OLAHAN BLOCK',
        'brand' => 'PROCHIZ',
        'title' => 'PROCHIZ GOLD',
        'subtitle' => 'CHEDDAR CHEESE 2 KG',
        'weight' => 'Netto: 2.000 Gram (2 KG)',
        'note' => 'Mudah Diparut Panjang & Tahan Bakar',
        'bg_start' => [194, 65, 12],   // Cheddar Orange
        'bg_end' => [67, 20, 7],
        'accent' => [253, 224, 71],    // Warm Cheese Gold
        'badge_bg' => [185, 28, 28],   // Prochiz Red
        'badge_text' => [255, 255, 255],
        'icon_type' => 'cheese',
    ],
    [
        'filename' => 'saf-instant-500g.png',
        'sku' => 'RGI-SAF-GLD50',
        'category' => 'RAGI ROTI INSTAN DUNIA',
        'brand' => 'SAF-INSTANT',
        'title' => 'SAF-INSTANT GOLD',
        'subtitle' => 'INSTANT DRY YEAST',
        'weight' => 'Netto: 500 Gram (Pack)',
        'note' => 'Khusus Adonan Roti Manis',
        'bg_start' => [185, 28, 28],   // Saf-Instant French Red
        'bg_end' => [69, 10, 10],
        'accent' => [250, 204, 21],    // Gold accent
        'badge_bg' => [202, 138, 4],
        'badge_text' => [255, 255, 255],
        'icon_type' => 'yeast',
    ],
    [
        'filename' => 'sp-koepoe-70g.png',
        'sku' => 'EML-KPE-SP70G',
        'category' => 'CAKE EMULSIFIER PENGEMBANG',
        'brand' => 'KOEPOE-KOEPOE',
        'title' => 'SP KOEPOE',
        'subtitle' => 'PENGEMULSI KUE 70 GR',
        'weight' => 'Isi: 70 Gram Botol',
        'note' => 'Bolu Lebih Lembut & Mengembang',
        'bg_start' => [16, 149, 106],  // Koepoe Green
        'bg_end' => [6, 78, 59],
        'accent' => [250, 204, 21],    // Yellow butterfly
        'badge_bg' => [234, 179, 8],
        'badge_text' => [15, 23, 42],
        'icon_type' => 'butterfly',
    ],
    [
        'filename' => 'elmer-glaze-1kg.png',
        'sku' => 'GLZ-ELM-DRK1K',
        'category' => 'DIP GLAZE TOPPING DONAT',
        'brand' => 'ELMER',
        'title' => 'DIP GLAZE DARK',
        'subtitle' => 'CHOCOLATE TOPPING 1 KG',
        'weight' => 'Netto: 1 KG Pail',
        'note' => 'Lumer Basah & Mengkilap Siap Pakai',
        'bg_start' => [88, 28, 135],   // Elmer Purple
        'bg_end' => [46, 16, 101],
        'accent' => [244, 114, 182],   // Pink/Chocolate topping
        'badge_bg' => [126, 34, 206],
        'badge_text' => [255, 255, 255],
        'icon_type' => 'glaze',
    ],
    [
        'filename' => 'dus-brownies-20x10.png',
        'sku' => 'KMS-BRW-20X10',
        'category' => 'KEMASAN KOTAK KUE KRAFT',
        'brand' => 'INDOPACK',
        'title' => 'DUS BROWNIES',
        'subtitle' => 'WINDOW MIKA 20x10x5 CM',
        'weight' => 'Bahan: Kraft Tebal Food Grade',
        'note' => 'Kemasan Eksklusif Jendela Bening',
        'bg_start' => [120, 80, 50],   // Kraft Eco Paper Brown
        'bg_end' => [60, 40, 25],
        'accent' => [253, 230, 138],   // Warm Vanilla
        'badge_bg' => [146, 64, 14],
        'badge_text' => [255, 255, 255],
        'icon_type' => 'box',
    ],
];

function drawRoundedRect($img, $x1, $y1, $x2, $y2, $radius, $color, $fill = true) {
    if ($fill) {
        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }
}

foreach ($products as $p) {
    $width = 600;
    $height = 600;
    $img = imagecreatetruecolor($width, $height);
    imageantialias($img, true);

    // Draw smooth gradient background
    for ($y = 0; $y < $height; $y++) {
        $factor = $y / $height;
        $r = (int)($p['bg_start'][0] * (1 - $factor) + $p['bg_end'][0] * $factor);
        $g = (int)($p['bg_start'][1] * (1 - $factor) + $p['bg_end'][1] * $factor);
        $b = (int)($p['bg_start'][2] * (1 - $factor) + $p['bg_end'][2] * $factor);
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $width, $y, $color);
    }

    // Color definitions
    $white = imagecolorallocate($img, 255, 255, 255);
    $cardBg = imagecolorallocatealpha($img, 255, 255, 255, 110); // Translucent glass card
    $cardBorder = imagecolorallocatealpha($img, 255, 255, 255, 95);
    $accentColor = imagecolorallocate($img, $p['accent'][0], $p['accent'][1], $p['accent'][2]);
    $badgeBg = imagecolorallocate($img, $p['badge_bg'][0], $p['badge_bg'][1], $p['badge_bg'][2]);
    $badgeText = imagecolorallocate($img, $p['badge_text'][0], $p['badge_text'][1], $p['badge_text'][2]);
    $mutedText = imagecolorallocatealpha($img, 255, 255, 255, 30);
    $innerGlow = imagecolorallocatealpha($img, $p['accent'][0], $p['accent'][1], $p['accent'][2], 105);

    // Center Product Showcase Card
    drawRoundedRect($img, 40, 40, 560, 560, 28, $cardBg, true);
    drawRoundedRect($img, 46, 46, 554, 554, 24, $cardBorder, false);

    // Subtle decorative aura behind product name
    imagefilledellipse($img, 300, 290, 360, 260, $innerGlow);

    // Top Brand & Category Pill
    drawRoundedRect($img, 70, 75, 530, 115, 12, $badgeBg, true);
    $catText = $p['brand'] . '  •  ' . $p['category'];
    $box = imagettfbbox(11, 0, $fontBold, $catText);
    $textW = $box[2] - $box[0];
    $textX = (int)(300 - ($textW / 2));
    imagettftext($img, 11, 0, $textX, 100, $badgeText, $fontBold, $catText);

    // SKU Pill
    $skuBox = imagettfbbox(10, 0, $fontBold, $p['sku']);
    $skuW = $skuBox[2] - $skuBox[0];
    $skuX = (int)(300 - ($skuW / 2));
    imagettftext($img, 10, 0, $skuX, 155, $accentColor, $fontBold, $p['sku']);

    // Big Product Title (Split or Single)
    $title = $p['title'];
    $titleBox = imagettfbbox(26, 0, $fontBold, $title);
    $titleW = $titleBox[2] - $titleBox[0];
    $titleX = (int)(300 - ($titleW / 2));
    imagettftext($img, 26, 0, $titleX, 235, $white, $fontBold, $title);

    // Subtitle
    $sub = $p['subtitle'];
    $subBox = imagettfbbox(14, 0, $fontBold, $sub);
    $subW = $subBox[2] - $subBox[0];
    $subX = (int)(300 - ($subW / 2));
    imagettftext($img, 14, 0, $subX, 275, $accentColor, $fontBold, $sub);

    // Visual Graphic Container
    drawRoundedRect($img, 150, 310, 450, 430, 18, imagecolorallocatealpha($img, 0, 0, 0, 95), true);
    
    // Draw decorative product emblem inside container
    $emblemColor = $accentColor;
    // Central icon art
    imagefilledellipse($img, 300, 360, 50, 50, $emblemColor);
    imagefilledellipse($img, 300, 360, 40, 40, imagecolorallocate($img, $p['bg_start'][0], $p['bg_start'][1], $p['bg_start'][2]));
    
    // Star / Quality Seal
    $sealText = "★  BAKING GRADE  ★";
    $sealBox = imagettfbbox(9, 0, $fontBold, $sealText);
    $sealX = (int)(300 - (($sealBox[2] - $sealBox[0]) / 2));
    imagettftext($img, 9, 0, $sealX, 410, $accentColor, $fontBold, $sealText);

    // Net Weight
    $wBox = imagettfbbox(13, 0, $fontBold, $p['weight']);
    $wX = (int)(300 - (($wBox[2] - $wBox[0]) / 2));
    imagettftext($img, 13, 0, $wX, 475, $white, $fontBold, $p['weight']);

    // Note / Recommendation
    $nBox = imagettfbbox(10, 0, $fontRegular, $p['note']);
    $nX = (int)(300 - (($nBox[2] - $nBox[0]) / 2));
    imagettftext($img, 10, 0, $nX, 510, $mutedText, $fontRegular, $p['note']);

    // Official Store Stamp Bottom
    $storeTag = "TOKO SUMBER BERKAH • PETERONGAN JOMBANG";
    $sBox = imagettfbbox(8, 0, $fontBold, $storeTag);
    $sX = (int)(300 - (($sBox[2] - $sBox[0]) / 2));
    imagettftext($img, 8, 0, $sX, 545, imagecolorallocatealpha($img, 255, 255, 255, 60), $fontBold, $storeTag);

    // Save PNG
    $destPath = $dir . '/' . $p['filename'];
    imagepng($img, $destPath, 8);
    imagedestroy($img);
    echo "Generated: {$p['filename']}\n";
}

echo "All 10 product images successfully created!\n";

// Update database directly
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach ($products as $p) {
    \App\Models\Product::where('sku', $p['sku'])->update([
        'image' => 'products/' . $p['filename']
    ]);
    echo "Updated DB SKU: {$p['sku']} -> products/{$p['filename']}\n";
}
echo "Database successfully linked to all product images!\n";
