<?php
/**
 * Script để phát hiện tất cả Money field trong tất cả IBlock
 */

// Kết nối đến Bitrix24
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

echo "Tìm kiếm tất cả Money field trong hệ thống...\n";
echo "==========================================\n\n";

$moneyFields = array();

// Lấy tất cả IBlock
$rsIBlocks = CIBlock::GetList(
    array("ID" => "ASC"),
    array("ACTIVE" => "Y")
);

while ($arIBlock = $rsIBlocks->Fetch()) {
    echo "Kiểm tra IBlock ID: " . $arIBlock['ID'] . " - " . $arIBlock['NAME'] . "\n";
    
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
            
            echo "  ✓ Tìm thấy Money field: " . $arProperty['NAME'] . " (ID: " . $arProperty['ID'] . ")\n";
        }
    }
    echo "\n";
}

echo "==========================================\n";
echo "Tổng kết:\n";
echo "Tìm thấy " . count($moneyFields) . " Money field trong " . count(array_unique(array_column($moneyFields, 'iblock_id'))) . " IBlock\n\n";

if (count($moneyFields) > 0) {
    echo "Chi tiết các Money field:\n";
    echo "----------------------------------------\n";
    foreach ($moneyFields as $field) {
        echo "IBlock: " . $field['iblock_name'] . " (ID: " . $field['iblock_id'] . ")\n";
        echo "  Property: " . $field['property_name'] . " (ID: " . $field['property_id'] . ")\n";
        echo "  Code: " . $field['property_code'] . "\n";
        echo "\n";
    }
    
    // Lưu danh sách vào file
    $jsonData = json_encode($moneyFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents('/workspace/money_fields_list.json', $jsonData);
    echo "Danh sách đã được lưu vào: /workspace/money_fields_list.json\n";
} else {
    echo "Không tìm thấy Money field nào trong hệ thống.\n";
}
?>