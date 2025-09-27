<?php
/**
 * Script để sửa định dạng Money field trong Bitrix24
 * Vấn đề: Dữ liệu Money field không đúng định dạng (thiếu currency code)
 * Giải pháp: Cập nhật tất cả record có định dạng sai thành định dạng đúng
 */

// Kết nối đến Bitrix24
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Cấu hình
$IBLOCK_ID = 92; // ID của workflow (từ URL)
$PROPERTY_ID = 502; // ID của property Money field
$CURRENCY_CODE = 'VND'; // Mã tiền tệ

// Hàm để sửa định dạng Money field
function fixMoneyFieldFormat($value, $currency = 'VND') {
    // Nếu giá trị đã có định dạng đúng (chứa |)
    if (strpos($value, '|') !== false) {
        return $value;
    }
    
    // Nếu giá trị chỉ là số, thêm currency code
    if (is_numeric($value)) {
        return $value . '|' . $currency;
    }
    
    // Nếu giá trị rỗng hoặc null
    if (empty($value)) {
        return '';
    }
    
    // Trường hợp khác, giữ nguyên
    return $value;
}

// Hàm để lấy tất cả element trong workflow
function getAllElements($iblockId) {
    $elements = array();
    
    $rsElements = CIBlockElement::GetList(
        array("ID" => "ASC"),
        array("IBLOCK_ID" => $iblockId),
        false,
        false,
        array("ID", "NAME")
    );
    
    while ($arElement = $rsElements->Fetch()) {
        $elements[] = $arElement;
    }
    
    return $elements;
}

// Hàm để sửa Money field cho một element
function fixElementMoneyField($elementId, $propertyId, $currency = 'VND') {
    // Lấy giá trị hiện tại của property
    $rsProperty = CIBlockElement::GetProperty(
        92, // IBLOCK_ID
        $elementId,
        "sort",
        "asc",
        array("ID" => $propertyId)
    );
    
    $currentValue = '';
    while ($arProperty = $rsProperty->Fetch()) {
        $currentValue = $arProperty['VALUE'];
        break;
    }
    
    // Kiểm tra và sửa định dạng
    $newValue = fixMoneyFieldFormat($currentValue, $currency);
    
    // Nếu giá trị đã thay đổi, cập nhật
    if ($newValue !== $currentValue) {
        $result = CIBlockElement::SetPropertyValues(
            $elementId,
            92, // IBLOCK_ID
            $newValue,
            $propertyId
        );
        
        if ($result) {
            echo "Element ID $elementId: Đã sửa từ '$currentValue' thành '$newValue'\n";
            return true;
        } else {
            echo "Element ID $elementId: Lỗi khi cập nhật\n";
            return false;
        }
    } else {
        echo "Element ID $elementId: Không cần sửa (định dạng đã đúng)\n";
        return true;
    }
}

// Main execution
echo "Bắt đầu sửa định dạng Money field...\n";
echo "IBLOCK_ID: $IBLOCK_ID\n";
echo "PROPERTY_ID: $PROPERTY_ID\n";
echo "CURRENCY: $CURRENCY_CODE\n\n";

// Lấy tất cả element trong workflow
$elements = getAllElements($IBLOCK_ID);
echo "Tìm thấy " . count($elements) . " element trong workflow\n\n";

$fixedCount = 0;
$errorCount = 0;

// Sửa từng element
foreach ($elements as $element) {
    echo "Đang xử lý Element ID: " . $element['ID'] . " - " . $element['NAME'] . "\n";
    
    if (fixElementMoneyField($element['ID'], $PROPERTY_ID, $CURRENCY_CODE)) {
        $fixedCount++;
    } else {
        $errorCount++;
    }
    
    echo "\n";
}

echo "Hoàn thành!\n";
echo "Đã sửa: $fixedCount element\n";
echo "Lỗi: $errorCount element\n";

// Kiểm tra kết quả
echo "\nKiểm tra kết quả...\n";
$testElements = array_slice($elements, 0, 5); // Kiểm tra 5 element đầu
foreach ($testElements as $element) {
    $rsProperty = CIBlockElement::GetProperty(
        $IBLOCK_ID,
        $element['ID'],
        "sort",
        "asc",
        array("ID" => $PROPERTY_ID)
    );
    
    while ($arProperty = $rsProperty->Fetch()) {
        echo "Element ID " . $element['ID'] . ": " . $arProperty['VALUE'] . "\n";
        break;
    }
}
?>