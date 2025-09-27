<?php
/**
 * Script backup dữ liệu Money field trước khi sửa
 */

// Kết nối đến Bitrix24
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Cấu hình
$IBLOCK_ID = 92; // ID của workflow
$PROPERTY_ID = 502; // ID của property Money field
$BACKUP_FILE = '/workspace/money_field_backup_' . date('Y-m-d_H-i-s') . '.json';

echo "Tạo backup dữ liệu Money field...\n";
echo "IBLOCK_ID: $IBLOCK_ID\n";
echo "PROPERTY_ID: $PROPERTY_ID\n";
echo "Backup file: $BACKUP_FILE\n\n";

$backupData = array();

// Lấy tất cả element
$rsElements = CIBlockElement::GetList(
    array("ID" => "ASC"),
    array("IBLOCK_ID" => $IBLOCK_ID),
    false,
    false,
    array("ID", "NAME")
);

$count = 0;
while ($arElement = $rsElements->Fetch()) {
    $count++;
    echo "Backing up Element ID: " . $arElement['ID'] . " - " . $arElement['NAME'] . "\n";
    
    // Lấy giá trị Money field
    $rsProperty = CIBlockElement::GetProperty(
        $IBLOCK_ID,
        $arElement['ID'],
        "sort",
        "asc",
        array("ID" => $PROPERTY_ID)
    );
    
    $value = '';
    while ($arProperty = $rsProperty->Fetch()) {
        $value = $arProperty['VALUE'];
        break;
    }
    
    // Lưu vào backup data
    $backupData[] = array(
        'element_id' => $arElement['ID'],
        'element_name' => $arElement['NAME'],
        'property_value' => $value,
        'backup_time' => date('Y-m-d H:i:s')
    );
}

// Ghi backup file
$jsonData = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($BACKUP_FILE, $jsonData);

echo "\nBackup hoàn thành!\n";
echo "Đã backup $count element\n";
echo "File backup: $BACKUP_FILE\n";
echo "Kích thước file: " . number_format(filesize($BACKUP_FILE)) . " bytes\n";
?>