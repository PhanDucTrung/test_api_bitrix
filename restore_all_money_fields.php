<?php
/**
 * Script restore dữ liệu Money field từ backup cho TẤT CẢ IBlock
 * Sử dụng: php restore_all_money_fields.php backup_file.json
 */

// Kết nối đến Bitrix24
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Lấy file backup từ command line argument
$backupFile = isset($argv[1]) ? $argv[1] : '';

if (empty($backupFile)) {
    echo "Sử dụng: php restore_all_money_fields.php backup_file.json\n";
    echo "Ví dụ: php restore_all_money_fields.php all_money_fields_backup_2024-01-15_14-30-00.json\n";
    exit(1);
}

if (!file_exists($backupFile)) {
    echo "File backup không tồn tại: $backupFile\n";
    exit(1);
}

echo "Khôi phục dữ liệu Money field cho TẤT CẢ IBlock từ backup...\n";
echo "Backup file: $backupFile\n\n";

// Đọc dữ liệu backup
$jsonData = file_get_contents($backupFile);
$backupData = json_decode($jsonData, true);

if (!$backupData) {
    echo "Lỗi: Không thể đọc file backup\n";
    exit(1);
}

echo "Tìm thấy " . count($backupData) . " record trong backup\n\n";

$restoredCount = 0;
$errorCount = 0;

// Nhóm dữ liệu theo IBlock
$groupedData = array();
foreach ($backupData as $record) {
    $key = $record['iblock_id'] . '_' . $record['property_id'];
    if (!isset($groupedData[$key])) {
        $groupedData[$key] = array(
            'iblock_id' => $record['iblock_id'],
            'iblock_name' => $record['iblock_name'],
            'property_id' => $record['property_id'],
            'property_name' => $record['property_name'],
            'records' => array()
        );
    }
    $groupedData[$key]['records'][] = $record;
}

// Khôi phục từng IBlock
foreach ($groupedData as $group) {
    echo "Khôi phục IBlock: " . $group['iblock_name'] . " - Property: " . $group['property_name'] . "\n";
    echo "Số record: " . count($group['records']) . "\n";
    
    $iblockRestored = 0;
    $iblockErrors = 0;
    
    foreach ($group['records'] as $record) {
        $elementId = $record['element_id'];
        $originalValue = $record['property_value'];
        $backupTime = $record['backup_time'];
        
        echo "  Element ID: $elementId (backup từ $backupTime)\n";
        echo "    Giá trị gốc: '$originalValue'\n";
        
        // Cập nhật giá trị
        $result = CIBlockElement::SetPropertyValues(
            $elementId,
            $group['iblock_id'],
            $originalValue,
            $group['property_id']
        );
        
        if ($result) {
            echo "    Status: ✓ Khôi phục thành công\n";
            $iblockRestored++;
            $restoredCount++;
        } else {
            echo "    Status: ✗ Lỗi khi khôi phục\n";
            $iblockErrors++;
            $errorCount++;
        }
    }
    
    echo "  IBlock " . $group['iblock_name'] . ": Đã khôi phục $iblockRestored, Lỗi $iblockErrors\n\n";
}

echo "================================================\n";
echo "Khôi phục hoàn thành!\n";
echo "Đã khôi phục: $restoredCount record\n";
echo "Lỗi: $errorCount record\n";
echo "================================================\n";
?>