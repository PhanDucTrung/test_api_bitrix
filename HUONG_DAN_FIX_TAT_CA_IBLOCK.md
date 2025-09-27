# Hướng dẫn sửa định dạng Money field cho TẤT CẢ IBlock

## Vấn đề
Sau khi migrate từ CentOS 7.9 lên Rocky Linux 9, dữ liệu Money field trong TẤT CẢ IBlock bị mất định dạng currency code.

## Giải pháp
Sử dụng các script PHP để tự động phát hiện và sửa tất cả Money field trong hệ thống.

## Các bước thực hiện

### Bước 1: Phát hiện tất cả Money field
```bash
cd /workspace
php detect_money_fields.php
```
Script này sẽ:
- Tìm tất cả IBlock trong hệ thống
- Phát hiện tất cả Money field
- Lưu danh sách vào file `money_fields_list.json`

### Bước 2: Kiểm tra định dạng hiện tại
```bash
php test_all_money_fields.php
```
Script này sẽ:
- Kiểm tra định dạng của tất cả Money field
- Hiển thị thống kê chi tiết
- Báo cáo số lượng cần sửa

### Bước 3: Backup dữ liệu (QUAN TRỌNG)
```bash
php fix_all_money_fields.php
```
Script này sẽ:
- Tự động backup tất cả dữ liệu Money field
- Sửa định dạng cho tất cả IBlock
- Hiển thị báo cáo chi tiết

### Bước 4: Kiểm tra kết quả
```bash
php test_all_money_fields.php
```
Kiểm tra lại để đảm bảo tất cả đã được sửa.

### Bước 5: Khôi phục nếu cần (nếu có lỗi)
```bash
php restore_all_money_fields.php all_money_fields_backup_YYYY-MM-DD_HH-mm-ss.json
```

## Các file script

1. **`detect_money_fields.php`** - Phát hiện tất cả Money field
2. **`test_all_money_fields.php`** - Kiểm tra định dạng hiện tại
3. **`fix_all_money_fields.php`** - Script chính để sửa tất cả
4. **`restore_all_money_fields.php`** - Khôi phục dữ liệu nếu cần

## Tính năng của script tổng quát

### ✅ Tự động phát hiện
- Tìm tất cả IBlock trong hệ thống
- Phát hiện tất cả Money field
- Không cần cấu hình thủ công

### ✅ Backup an toàn
- Tự động backup trước khi sửa
- Lưu thông tin chi tiết từng record
- Có thể khôi phục hoàn toàn

### ✅ Sửa thông minh
- Chỉ sửa những field cần sửa
- Giữ nguyên field đã đúng định dạng
- Báo cáo chi tiết từng IBlock

### ✅ Báo cáo đầy đủ
- Thống kê tổng quan
- Chi tiết từng IBlock
- Danh sách lỗi (nếu có)

## Cấu hình

Trong file `fix_all_money_fields.php`, bạn có thể thay đổi:

```php
$CURRENCY_CODE = 'VND'; // Mã tiền tệ mặc định
```

## Lưu ý quan trọng

1. **LUÔN chạy test trước khi fix**
2. **Backup được tạo tự động**
3. **Script sẽ xử lý TẤT CẢ IBlock**
4. **Kiểm tra kết quả sau khi chạy**

## Ví dụ kết quả

```
Script sửa định dạng Money field cho TẤT CẢ IBlock
================================================
Currency mặc định: VND
Backup file: /workspace/all_money_fields_backup_2024-01-15_14-30-00.json

Bước 1: Tìm kiếm tất cả Money field...
Tìm thấy 5 Money field trong 3 IBlock

Bước 2: Tạo backup dữ liệu...
Đã backup 150 record

Bước 3: Bắt đầu sửa định dạng...
Đang xử lý IBlock: Workflow 1 - Property: Total Amount
  IBlock Workflow 1: Đã sửa 25, Không thay đổi 5, Lỗi 0

Đang xử lý IBlock: Workflow 2 - Property: Payment Amount  
  IBlock Workflow 2: Đã sửa 30, Không thay đổi 10, Lỗi 0

================================================
Hoàn thành!
Tổng kết:
- Đã sửa: 55 record
- Không thay đổi: 15 record  
- Lỗi: 0 record
- Backup file: /workspace/all_money_fields_backup_2024-01-15_14-30-00.json
================================================
```

## Troubleshooting

Nếu gặp lỗi:
1. Kiểm tra quyền truy cập database
2. Kiểm tra log lỗi PHP
3. Sử dụng script restore để khôi phục
4. Chạy lại script test để kiểm tra

## Ưu điểm của script tổng quát

- **Tự động**: Không cần cấu hình thủ công
- **An toàn**: Backup tự động trước khi sửa
- **Toàn diện**: Xử lý tất cả IBlock
- **Báo cáo**: Thống kê chi tiết
- **Khôi phục**: Có thể rollback nếu cần