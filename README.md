HƯỚNG DẪN CÀI ĐẶT VÀ CẤU HÌNH ỨNG DỤNG TÍCH HỢP BITRIX24 OAUTH

BÀI 1 : TRIỂN KHAI CƠ CHẾ OAUTH CỦA BITRIX24 

Mô tả ứng dụng :
Ứng dụng Node.js tích hợp với Bitrix24 thông qua cơ chế OAuth, cung cấp các chức năng:
- Nhận sự kiện Install App.
- Lưu access token và refresh token (có thể dùng file hoặc database).
- refresh token khi hết hạn.
- Gọi API bất kỳ với token đang có.

BÀI 2 : TẠO GIAO DIỆN CƠ BẢN CHO PHÉP HIỂN THỊ THÊM SỬA XOÁ CONTACT VỚI CÁC THÔNG TIN CƠ BẢN 

Mô tả ứng dụng :
Ứng dụng  Node.js tích hợp với Bitrix24 thông qua OAuth 2.0, cung cấp các chức năng:
- Hiển thị danh sách contact với Tên, Địa chỉ, Số điện thoại, Email, Website, Thông tin chi tiết ngân hàng
- Thêm contact
- Sửa contact
- Xoá contact

Yêu cầu hệ thống :
- Nodejs, git, npm 
- Tài khoản Bitrix24 với quyền quản trị viên
- Ngrok hoặc máy chủ có thể truy cập công khai

Bước 1 : Tải và cài đặt Ngrok
- Truy cập https://ngrok.com/download và tải phiên bản phù hợp với hệ điều hành của bạn (Windows, macOS, Linux).
- Giải nén file tải về và khởi động file ngrok trong đó chuyển hướng đến cmd (với windows);
- Chạy lệnh 'ngrok config add-authtoken $YOUR_AUTHTOKEN' với $YOUR_AUTHTOKEN lấy từ trang https://dashboard.ngrok.com khi người dùng đăng nhập
- Chạy lệnh 'ngrok http 3000' để ngrok lắng nghe trên cổng 3000 và lưu ý là cổng backend cũng phải chạy trên cổng 3000
- Mỗi lần chạy ngrok thì ngrok sẽ cung cấp 1 URL khác nhau 'https://vidu.ngrok.io' -> thay thế bằng dẫn thực của ngrok cung cấp -> Lưu lại

Bước 2 :  Đăng nhập Bitrix24
- Đăng nhập vào bitrix24 với quyền quản trị viên
- Tạo một ứng dụng cục bộ -> Nhập đường dẫn xử lí 'https://vidu.ngrok.io/callback' -> Nhập đường dẫn ban đầu 'https://vidu.ngrok.io' ->
  Nhập quyền truy cập crm,log,user,im,... -> Khởi tạo ứng dụng
- Sau khi khởi tạo bitrix sẽ cung cấp cilent_id và cilent_secret -> Lưu lại

Bước 3 : Khởi chạy backend với node.js
- Chạy các lệnh "git clone https://github.com/PhanDucTrung/test_api_bitrix.git" 
- sửa file .env trong "test_api_bitrix" với :
+ NGROK_AUTH_TOKEN= $YOUR_AUTHTOKEN mà ngrok cung cấp
+ CLIENT_ID : client_id mà bitrix cung cấp khi khởi tạo ứng dụng
+ CILENT_SECRET : cilent_secret mà bitrix cung cấp khi khởi tạo ứng dụng
+ REDIRECT_URI : không cần nhập vì sau khi chạy back end sẽ tự ghi đè,
+ BITRIX_DOMAIN : Đường dẫn bitrix của bạn 'yourname.bitrix24.vn'

Bước 4 : quay lại cmd ở bước 3 nhập: "cd test_api_bitrix " >> "npm start"
- cdm sẽ hiện thị localhost mới vd: "https://localmoi.ngrok.io"
- quay lại ứng dụng cục bộ ở bước 2 -> Sửa đường dẫn xử lí 'https://localmoi.ngrok.io/callback' -> sửa đường dẫn ban đầu 'https://localmoi.ngrok.io' -> lưu.

Bước 5 : Truy cập http://localhost:3000/install để cài đặt ứng dụng 

Bước 6 : Truy cập http://localhost:3000/test-api để gọi API bất kì với token đang có

Bước 7 : Khi nhấn reinstall thì ứng dụng cài đặt lại và mọi thay đổi được lưu vào file bitrix-config.json (xong bài 1)
                         
BƯớc 8 : sau khi truy cập http://localhost:3000/install thành công -> "xem contact ngay" để chuyển hứng qua http://localhost:3000/contact 

Bước 9 : thực hiện thêm, sửa, xóa, trên ứng dụng 
                                      
