<?php
/**
 * Script test để kiểm tra định dạng Money field trước khi sửa
 */

// Kết nối đến Bitrix24
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Cấu hình
$IBLOCK_ID = 92; // ID của workflow
$PROPERTY_ID = 502; // ID của property Money field

echo "Kiểm tra định dạng Money field hiện tại...\n";
echo "IBLOCK_ID: $IBLOCK_ID\n";
echo "PROPERTY_ID: $PROPERTY_ID\n\n";

// Lấy 10 element đầu để kiểm tra
$rsElements = CIBlockElement::GetList(
    array("ID" => "ASC"),
    array("IBLOCK_ID" => $IBLOCK_ID),
    false,
    array("nTopCount" => 10),
    array("ID", "NAME")
);

echo "Kiểm tra 10 element đầu:\n";
echo "==========================================\n";

while ($arElement = $rsElements->Fetch()) {
    echo "Element ID: " . $arElement['ID'] . " - " . $arElement['NAME'] . "\n";
    
    // Lấy giá trị Money field
    $rsProperty = CIBlockElement::GetProperty(
        $IBLOCK_ID,
        $arElement['ID'],
        "sort",
        "asc",
        array("ID" => $PROPERTY_ID)
    );
    
    while ($arProperty = $rsProperty->Fetch()) {
        $value = $arProperty['VALUE'];
        echo "  Money field value: '$value'\n";
        
        // Kiểm tra định dạng
        if (strpos($value, '|') !== false) {
            echo "  Status: ✓ Định dạng đúng (có currency code)\n";
        } elseif (is_numeric($value)) {
            echo "  Status: ✗ Cần sửa (thiếu currency code)\n";
            echo "  Sẽ được sửa thành: '$value|VND'\n";
        } elseif (empty($value)) {
            echo "  Status: - Giá trị rỗng\n";
        } else {
            echo "  Status: ? Định dạng không xác định\n";
        }
        break;
    }
    echo "\n";
}

echo "==========================================\n";
echo "Kiểm tra hoàn thành!\n";
?>