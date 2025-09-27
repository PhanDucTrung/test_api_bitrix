# Hướng dẫn sửa định dạng Money field trong Bitrix24

## Vấn đề
Sau khi migrate từ CentOS 7.9 lên Rocky Linux 9, dữ liệu Money field bị mất định dạng currency code. Thay vì `62|VND`, chỉ còn lại `62`.

## Giải pháp
Sử dụng các script PHP để sửa định dạng dữ liệu.

## Các bước thực hiện

### Bước 1: Backup dữ liệu (QUAN TRỌNG)
```bash
cd /workspace
php backup_money_field.php
```
Script này sẽ tạo file backup với tên `money_field_backup_YYYY-MM-DD_HH-mm-ss.json`

### Bước 2: Kiểm tra dữ liệu hiện tại
```bash
php test_money_field.php
```
Script này sẽ hiển thị 10 record đầu để bạn kiểm tra định dạng hiện tại.

### Bước 3: Chạy script sửa (THẬN TRỌNG)
```bash
php fix_money_field.php
```
Script này sẽ:
- Tìm tất cả element trong workflow ID 92
- Kiểm tra Money field (Property ID 502)
- Sửa định dạng từ `62` thành `62|VND`
- Hiển thị kết quả chi tiết

### Bước 4: Kiểm tra kết quả
Sau khi chạy script, kiểm tra lại bằng:
```bash
php test_money_field.php
```

### Bước 5: Khôi phục nếu cần (nếu có lỗi)
```bash
php restore_money_field.php money_field_backup_YYYY-MM-DD_HH-mm-ss.json
```

## Cấu hình script

Trong các file script, bạn có thể thay đổi các thông số:

```php
$IBLOCK_ID = 92;        // ID của workflow
$PROPERTY_ID = 502;     // ID của Money field
$CURRENCY_CODE = 'VND'; // Mã tiền tệ
```

## Lưu ý quan trọng

1. **LUÔN backup trước khi chạy script sửa**
2. **Test trên một vài record trước khi chạy toàn bộ**
3. **Kiểm tra kết quả sau khi chạy**
4. **Giữ file backup để khôi phục nếu cần**

## Cách chạy script

1. Upload các file script lên server Bitrix24
2. Chạy từ command line hoặc qua web interface
3. Kiểm tra log để đảm bảo không có lỗi

## Troubleshooting

Nếu gặp lỗi:
1. Kiểm tra quyền truy cập database
2. Kiểm tra IBLOCK_ID và PROPERTY_ID có đúng không
3. Kiểm tra log lỗi PHP
4. Sử dụng script restore để khôi phục dữ liệu gốc