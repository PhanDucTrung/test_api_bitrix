<?php
/**
 * Script tổng quát để sửa định dạng Money field trong TẤT CẢ IBlock
 */

// Kết nối đến Bitrix24
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Cấu hình
$CURRENCY_CODE = 'VND'; // Mã tiền tệ mặc định
$BACKUP_FILE = '/workspace/all_money_fields_backup_' . date('Y-m-d_H-i-s') . '.json';

echo "Script sửa định dạng Money field cho TẤT CẢ IBlock\n";
echo "================================================\n";
echo "Currency mặc định: $CURRENCY_CODE\n";
echo "Backup file: $BACKUP_FILE\n\n";

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

// Hàm để backup dữ liệu
function backupMoneyFields($moneyFields) {
    global $BACKUP_FILE;
    
    $backupData = array();
    
    foreach ($moneyFields as $field) {
        echo "Backing up IBlock: " . $field['iblock_name'] . " - Property: " . $field['property_name'] . "\n";
        
        // Lấy tất cả element của IBlock này
        $rsElements = CIBlockElement::GetList(
            array("ID" => "ASC"),
            array("IBLOCK_ID" => $field['iblock_id']),
            false,
            false,
            array("ID", "NAME")
        );
        
        while ($arElement = $rsElements->Fetch()) {
            // Lấy giá trị Money field
            $rsProperty = CIBlockElement::GetProperty(
                $field['iblock_id'],
                $arElement['ID'],
                "sort",
                "asc",
                array("ID" => $field['property_id'])
            );
            
            $value = '';
            while ($arProperty = $rsProperty->Fetch()) {
                $value = $arProperty['VALUE'];
                break;
            }
            
            // Lưu vào backup data
            $backupData[] = array(
                'iblock_id' => $field['iblock_id'],
                'iblock_name' => $field['iblock_name'],
                'property_id' => $field['property_id'],
                'property_name' => $field['property_name'],
                'element_id' => $arElement['ID'],
                'element_name' => $arElement['NAME'],
                'property_value' => $value,
                'backup_time' => date('Y-m-d H:i:s')
            );
        }
    }
    
    // Ghi backup file
    $jsonData = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($BACKUP_FILE, $jsonData);
    
    return count($backupData);
}

// Hàm để sửa Money field cho một element
function fixElementMoneyField($iblockId, $elementId, $propertyId, $currency = 'VND') {
    // Lấy giá trị hiện tại của property
    $rsProperty = CIBlockElement::GetProperty(
        $iblockId,
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
            $iblockId,
            $newValue,
            $propertyId
        );
        
        if ($result) {
            return array('success' => true, 'old_value' => $currentValue, 'new_value' => $newValue);
        } else {
            return array('success' => false, 'error' => 'Lỗi khi cập nhật');
        }
    } else {
        return array('success' => true, 'old_value' => $currentValue, 'new_value' => $newValue, 'no_change' => true);
    }
}

// Main execution
echo "Bước 1: Tìm kiếm tất cả Money field...\n";
$moneyFields = getAllMoneyFields();
echo "Tìm thấy " . count($moneyFields) . " Money field trong " . count(array_unique(array_column($moneyFields, 'iblock_id'))) . " IBlock\n\n";

if (count($moneyFields) == 0) {
    echo "Không tìm thấy Money field nào. Kết thúc.\n";
    exit(0);
}

echo "Bước 2: Tạo backup dữ liệu...\n";
$backupCount = backupMoneyFields($moneyFields);
echo "Đã backup $backupCount record\n\n";

echo "Bước 3: Bắt đầu sửa định dạng...\n";
$totalFixed = 0;
$totalErrors = 0;
$totalNoChange = 0;

foreach ($moneyFields as $field) {
    echo "Đang xử lý IBlock: " . $field['iblock_name'] . " - Property: " . $field['property_name'] . "\n";
    
    // Lấy tất cả element của IBlock này
    $rsElements = CIBlockElement::GetList(
        array("ID" => "ASC"),
        array("IBLOCK_ID" => $field['iblock_id']),
        false,
        false,
        array("ID", "NAME")
    );
    
    $iblockFixed = 0;
    $iblockErrors = 0;
    $iblockNoChange = 0;
    
    while ($arElement = $rsElements->Fetch()) {
        $result = fixElementMoneyField($field['iblock_id'], $arElement['ID'], $field['property_id'], $CURRENCY_CODE);
        
        if ($result['success']) {
            if (isset($result['no_change'])) {
                $iblockNoChange++;
                $totalNoChange++;
            } else {
                $iblockFixed++;
                $totalFixed++;
                echo "  Element ID " . $arElement['ID'] . ": Đã sửa từ '" . $result['old_value'] . "' thành '" . $result['new_value'] . "'\n";
            }
        } else {
            $iblockErrors++;
            $totalErrors++;
            echo "  Element ID " . $arElement['ID'] . ": " . $result['error'] . "\n";
        }
    }
    
    echo "  IBlock " . $field['iblock_name'] . ": Đã sửa $iblockFixed, Không thay đổi $iblockNoChange, Lỗi $iblockErrors\n\n";
}

echo "================================================\n";
echo "Hoàn thành!\n";
echo "Tổng kết:\n";
echo "- Đã sửa: $totalFixed record\n";
echo "- Không thay đổi: $totalNoChange record\n";
echo "- Lỗi: $totalErrors record\n";
echo "- Backup file: $BACKUP_FILE\n";
echo "================================================\n";
?>