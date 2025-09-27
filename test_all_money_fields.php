<?php
/**
 * Script test để kiểm tra định dạng Money field trong TẤT CẢ IBlock
 */

// Kết nối đến Bitrix24
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

echo "Kiểm tra định dạng Money field trong TẤT CẢ IBlock...\n";
echo "==================================================\n\n";

// Hàm để lấy tất cả Money field
function getAllMoneyFields() {
    $moneyFields = array();
    
    // Lấy tất cả IBlock
    $rsIBlocks = CIBlock::GetList(
        array("ID" => "ASC"),
        array("ACTIVE" => "Y")
    );
    
    while ($arIBlock = $rsIBlocks->Fetch()) {
        // Lấy tất cả property của IBlock này
        $rsProperties = CIBlockProperty::GetList(
            array("SORT" => "ASC"),
            array("IBLOCK_ID" => $arIBlock['ID'])
        );
        
        while ($arProperty = $rsProperties->Fetch()) {
            // Kiểm tra nếu là Money field
            if ($arProperty['PROPERTY_TYPE'] == 'S' && $arProperty['USER_TYPE'] == 'Money') {
                $moneyFields[] = array(
                    'iblock_id' => $arIBlock['ID'],
                    'iblock_name' => $arIBlock['NAME'],
                    'property_id' => $arProperty['ID'],
                    'property_name' => $arProperty['NAME'],
                    'property_code' => $arProperty['CODE']
                );
            }
        }
    }
    
    return $moneyFields;
}

// Lấy tất cả Money field
$moneyFields = getAllMoneyFields();

if (count($moneyFields) == 0) {
    echo "Không tìm thấy Money field nào trong hệ thống.\n";
    exit(0);
}

echo "Tìm thấy " . count($moneyFields) . " Money field trong " . count(array_unique(array_column($moneyFields, 'iblock_id'))) . " IBlock\n\n";

$totalElements = 0;
$totalCorrect = 0;
$totalNeedFix = 0;
$totalEmpty = 0;

foreach ($moneyFields as $field) {
    echo "IBlock: " . $field['iblock_name'] . " (ID: " . $field['iblock_id'] . ")\n";
    echo "Property: " . $field['property_name'] . " (ID: " . $field['property_id'] . ")\n";
    echo "Code: " . $field['property_code'] . "\n";
    echo "----------------------------------------\n";
    
    // Lấy 5 element đầu để kiểm tra
    $rsElements = CIBlockElement::GetList(
        array("ID" => "ASC"),
        array("IBLOCK_ID" => $field['iblock_id']),
        false,
        array("nTopCount" => 5),
        array("ID", "NAME")
    );
    
    $iblockElements = 0;
    $iblockCorrect = 0;
    $iblockNeedFix = 0;
    $iblockEmpty = 0;
    
    while ($arElement = $rsElements->Fetch()) {
        $iblockElements++;
        $totalElements++;
        
        echo "  Element ID: " . $arElement['ID'] . " - " . $arElement['NAME'] . "\n";
        
        // Lấy giá trị Money field
        $rsProperty = CIBlockElement::GetProperty(
            $field['iblock_id'],
            $arElement['ID'],
            "sort",
            "asc",
            array("ID" => $field['property_id'])
        );
        
        while ($arProperty = $rsProperty->Fetch()) {
            $value = $arProperty['VALUE'];
            echo "    Money field value: '$value'\n";
            
            // Kiểm tra định dạng
            if (strpos($value, '|') !== false) {
                echo "    Status: ✓ Định dạng đúng (có currency code)\n";
                $iblockCorrect++;
                $totalCorrect++;
            } elseif (is_numeric($value)) {
                echo "    Status: ✗ Cần sửa (thiếu currency code)\n";
                echo "    Sẽ được sửa thành: '$value|VND'\n";
                $iblockNeedFix++;
                $totalNeedFix++;
            } elseif (empty($value)) {
                echo "    Status: - Giá trị rỗng\n";
                $iblockEmpty++;
                $totalEmpty++;
            } else {
                echo "    Status: ? Định dạng không xác định\n";
            }
            break;
        }
        echo "\n";
    }
    
    echo "  Tổng kết IBlock này: $iblockElements element, $iblockCorrect đúng, $iblockNeedFix cần sửa, $iblockEmpty rỗng\n\n";
}

echo "==================================================\n";
echo "TỔNG KẾT TOÀN HỆ THỐNG:\n";
echo "Tổng số element kiểm tra: $totalElements\n";
echo "Định dạng đúng: $totalCorrect\n";
echo "Cần sửa: $totalNeedFix\n";
echo "Giá trị rỗng: $totalEmpty\n";
echo "==================================================\n";

if ($totalNeedFix > 0) {
    echo "\n⚠️  CÓ $totalNeedFix ELEMENT CẦN SỬA!\n";
    echo "Chạy script fix_all_money_fields.php để sửa tất cả.\n";
} else {
    echo "\n✅ TẤT CẢ MONEY FIELD ĐÃ CÓ ĐỊNH DẠNG ĐÚNG!\n";
}
?>