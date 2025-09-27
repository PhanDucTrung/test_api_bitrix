<?php
/**
 * Script restore dữ liệu Money field từ backup
 * Sử dụng: php restore_money_field.php backup_file.json
 */

// Kết nối đến Bitrix24
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Cấu hình
$IBLOCK_ID = 92; // ID của workflow
$PROPERTY_ID = 502; // ID của property Money field

// Lấy file backup từ command line argument
$backupFile = isset($argv[1]) ? $argv[1] : '';

if (empty($backupFile)) {
    echo "Sử dụng: php restore_money_field.php backup_file.json\n";
    echo "Ví dụ: php restore_money_field.php money_field_backup_2024-01-15_14-30-00.json\n";
    exit(1);
}

if (!file_exists($backupFile)) {
    echo "File backup không tồn tại: $backupFile\n";
    exit(1);
}

echo "Khôi phục dữ liệu Money field từ backup...\n";
echo "Backup file: $backupFile\n";
echo "IBLOCK_ID: $IBLOCK_ID\n";
echo "PROPERTY_ID: $PROPERTY_ID\n\n";

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

// Khôi phục từng record
foreach ($backupData as $record) {
    $elementId = $record['element_id'];
    $originalValue = $record['property_value'];
    $backupTime = $record['backup_time'];
    
    echo "Khôi phục Element ID: $elementId (backup từ $backupTime)\n";
    echo "  Giá trị gốc: '$originalValue'\n";
    
    // Cập nhật giá trị
    $result = CIBlockElement::SetPropertyValues(
        $elementId,
        $IBLOCK_ID,
        $originalValue,
        $PROPERTY_ID
    );
    
    if ($result) {
        echo "  Status: ✓ Khôi phục thành công\n";
        $restoredCount++;
    } else {
        echo "  Status: ✗ Lỗi khi khôi phục\n";
        $errorCount++;
    }
    echo "\n";
}

echo "Khôi phục hoàn thành!\n";
echo "Đã khôi phục: $restoredCount record\n";
echo "Lỗi: $errorCount record\n";
?>