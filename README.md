# NTH Notify - WordPress Notification Plugin

[English](#english) | [Tiếng Việt](#tieng-viet)

---

<a name="tieng-viet"></a>

## 🇻🇳 Tiếng Việt

### 📖 Giới thiệu

**NTH Notify** là plugin WordPress giúp bạn nhận thông báo tức thì về đơn hàng WooCommerce qua Telegram và Zalo. Plugin hỗ trợ nhiều kênh thông báo, cho phép tùy chỉnh trạng thái đơn hàng cần nhận thông báo, và dễ dàng quản lý nhiều Chat ID. Với giao diện hiện đại và tính năng test kết nối trực tiếp, NTH Notify giúp bạn không bỏ lỡ bất kỳ đơn hàng quan trọng nào.

### ⚙️ Yêu cầu hệ thống

- WordPress 5.8 trở lên
- PHP 7.4 trở lên
- WooCommerce (để sử dụng tính năng thông báo đơn hàng)

### 📥 Cài đặt

1. Tải file `nth-notify.zip` từ [Releases](../../releases)
2. Vào **WordPress Admin** → **Plugins** → **Add New** → **Upload Plugin**
3. Chọn file ZIP đã tải và click **Install Now**
4. Click **Activate** để kích hoạt plugin

### 🚀 Cấu hình Telegram

#### Bước 1: Bật Telegram Notifications

1. Vào **Settings** → **NTH Notify** → **General** tab
2. Tích chọn ✅ **Bật thông báo cho Telegram**
3. Chọn các trạng thái đơn hàng muốn nhận thông báo (ví dụ: Processing, Completed)
4. Click **Lưu thay đổi**

#### Bước 2: Tạo Telegram Bot và lấy Bot Token

1. Mở ứng dụng **Telegram** trên điện thoại hoặc máy tính
2. Tìm kiếm và chat với **@BotFather**
3. Gửi lệnh: `/newbot`
4. Nhập tên bot của bạn (ví dụ: `My Store Notifications`)
5. Nhập username cho bot (phải kết thúc bằng `bot`, ví dụ: `mystore_notify_bot`)
6. **BotFather** sẽ trả về Bot Token có dạng: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`
7. Copy và lưu Bot Token này

#### Bước 3: Lấy Chat ID

**Tìm User Chat ID**

1. Mở Telegram và tìm bot [@JsonDumpBot](https://t.me/JsonDumpBot)
2. Click **Start** hoặc gửi bất kỳ tin nhắn nào cho bot
3. Bạn sẽ tìm thấy id của mình trong phần `"id: 123456789` trong tin nhắn trả về
4. Copy Chat ID này

**Tìm Channel/Group Chat ID**

1. Forward bất kỳ tin nhắn nào từ channel/group của bạn đến bot [@JsonDumpBot](https://t.me/JsonDumpBot)
2. Từ tin nhắn trả về, tìm đến phần:
    ```json
    "forward_origin": {
       "type": "channel", // or "group"
       "chat": {
         "id": -123456789,
    	...
       },
    ```
3. Copy Chat ID (lưu ý: Chat ID của channel/group thường là số âm, ví dụ: `-123456789`)

#### Bước 4: Cấu hình trong Plugin

1. Vào **Settings** → **NTH Notify** → **Telegram** tab
2. Dán **Bot Token** vào trường **Bot Token**
3. Dán **Chat ID** vào trường **Chat ID #1**
4. Click **Kiểm tra** để kiểm tra kết nối
    - ✅ Nếu thành công, bạn sẽ nhận được tin nhắn test từ bot
    - ❌ Nếu thất bại, kiểm tra lại Bot Token và Chat ID
5. Click **Lưu thay đổi**

#### Bước 5: Thêm nhiều Chat ID (Tùy chọn)

1. Ở **Telegram** tab, click nút **+ Thêm Chat ID**
2. Nhập Chat ID thứ 2 vào trường mới xuất hiện
3. Lặp lại để thêm nhiều Chat ID khác
4. Click **Lưu thay đổi**

> 💡 **Mẹo**: Mỗi Chat ID có thể là cá nhân hoặc group khác nhau. Tất cả sẽ nhận thông báo khi có đơn hàng mới.

---

### 🔵 Cấu hình Zalo

#### Bước 1: Bật Zalo Notifications

1. Vào **Settings** → **NTH Notify** → **General** tab
2. Tích chọn ✅ **Bật thông báo cho Zalo**
3. Chọn các trạng thái đơn hàng muốn nhận thông báo
4. Click **Lưu thay đổi**

#### Bước 2: Tạo Zalo Bot và lấy Bot Token

1. Truy cập: **https://bot.zapps.me/docs/create-bot/**
2. Làm theo hướng dẫn để tạo Zalo Bot:
    - Đăng nhập bằng tài khoản Zalo của bạn
    - Tạo ứng dụng mới (Zalo Mini App)
    - Kích hoạt Bot API trong cài đặt ứng dụng
    - Copy **Bot Access Token** từ trang quản lý
3. Lưu lại Bot Token

> ⚠️ **Lưu ý**: Bot Token của Zalo thường dài hơn và có format khác với Telegram. Đảm bảo copy toàn bộ token.

#### Bước 3: Lấy Chat ID từ Zalo

**Sử dụng tính năng "Tìm Chat ID"**

1. Vào **Settings** → **NTH Notify** → **Zalo** tab
2. Dán **Bot Token** vào trường **Bot Token**
3. Click **Lưu thay đổi** (lưu token trước)
4. Click nút **Tìm Chat ID**
5. Mở ứng dụng **Zalo** và chat với bot của bạn (tìm theo tên bot)
6. Gửi **bất kỳ tin nhắn nào** cho bot
7. Plugin sẽ tự động lấy Chat ID và điền vào trường **Chat ID #1**
8. Click **Lưu thay đổi**

#### Bước 4: Kiểm tra kết nối

1. Ở **Zalo** tab, sau khi điền Bot Token và Chat ID
2. Click **Kiểm tra**
    - ✅ Thành công: Bạn sẽ nhận tin nhắn test trên Zalo
    - ❌ Thất bại: Kiểm tra lại Bot Token, Chat ID và đảm bảo bot đã được approve
3. Click **Lưu thay đổi** nếu chưa lưu

#### Bước 5: Thêm nhiều Chat ID (Tùy chọn)

1. Ở **Zalo** tab, click nút **+ Thêm Chat ID**
2. Click nút **Tìm Chat ID**
3. Gửi một tin nhắn đến bot từ tài khoản Zalo khác để lấy Chat ID thứ 2
4. Plugin sẽ tự động điền Chat ID vào trường mới
5. Click **Lưu thay đổi**

> 💡 **Mẹo**: Zalo Chat ID thường là chuỗi alphanumeric dài. Đảm bảo không có khoảng trắng thừa khi paste.

---

### 🎯 Chọn trạng thái đơn hàng nhận thông báo

1. Vào **Settings** → **NTH Notify** → **General** tab
2. Ở phần **Order Status Notifications**, tích chọn các trạng thái muốn nhận thông báo:
    - 🕐 **Pending** - Đơn hàng đang chờ xử lý
    - 🛒 **Processing** - Đơn hàng đang được xử lý
    - ⏸️ **On Hold** - Đơn hàng tạm giữ
    - ✅ **Completed** - Đơn hàng hoàn thành
    - ❌ **Cancelled** - Đơn hàng đã hủy
    - 💸 **Refunded** - Đơn hàng đã hoàn tiền
    - ⚠️ **Failed** - Đơn hàng thất bại
    - 📝 **Draft** - Đơn hàng nháp
3. Click **Lưu thay đổi**

### ❓ Câu hỏi thường gặp (FAQ)

**Q: Tôi có thể nhận thông báo cả Telegram và Zalo cùng lúc không?**  
A: Có! Bật cả hai options trong **General** tab và cấu hình riêng cho mỗi kênh.

**Q: Số lượng Chat ID tối đa là bao nhiêu?**  
A: Không giới hạn. Bạn có thể thêm bao nhiêu Chat ID tùy thích.

**Q: Tin nhắn test thành công nhưng không nhận được thông báo đơn hàng thật?**  
A: Kiểm tra:

- Đã bật thông báo ở **General** tab chưa?
- Đã chọn đúng trạng thái đơn hàng chưa?
- Đơn hàng có chuyển sang trạng thái đã chọn không?
- Enable **WP_DEBUG** và check file `wp-content/debug.log`

**Q: Làm sao biết plugin đang hoạt động?**  
A: Sử dụng nút **Test Connection** để gửi tin nhắn thử. Nếu nhận được thì plugin hoạt động bình thường.

**Q: Có thể tùy chỉnh nội dung tin nhắn không?**  
A: Hiện tại plugin có format cố định. Để tùy chỉnh, có thể dùng filter:

```php
add_filter( 'nth_notifications_new_order_message', function( $message, $order, $format_type ) {
    // Tùy chỉnh $message ở đây
    return $message;
}, 10, 3 );
```

### 🐛 Debug & Troubleshooting

Nếu gặp vấn đề, bật debug mode:

1. Mở file `wp-config.php`
2. Thêm hoặc sửa:
    ```php
    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_LOG', true );
    define( 'WP_DEBUG_DISPLAY', false );
    ```
3. Tạo đơn hàng test
4. Check log ở `wp-content/debug.log`
5. Tìm các dòng log bắt đầu với `NTH Notify -`

### 📞 Hỗ trợ

- **Issues**: [GitHub Issues](../../issues)
- **Email**: thienhungdev@gmail.com
- **Website**: https://wptop.net/

### 📜 License

GPL v2 or later - Xem file [LICENSE](LICENSE)

---

<a name="english"></a>

## 🇬🇧 English

### 📖 Introduction

**NTH Notify** is a WordPress plugin that helps you receive instant WooCommerce order notifications via Telegram and Zalo. The plugin supports multiple notification channels, allows customization of order statuses to receive notifications for, and easily manages multiple Chat IDs. With a modern interface and direct connection testing feature, NTH Notify ensures you never miss any important orders.

### ⚙️ System Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce (to use order notification features)

### 📥 Installation

1. Download `nth-notify.zip` file from [Releases](../../releases)
2. Go to **WordPress Admin** → **Plugins** → **Add New** → **Upload Plugin**
3. Select the downloaded ZIP file and click **Install Now**
4. Click **Activate** to activate the plugin

### 🚀 Telegram Configuration

#### Step 1: Enable Telegram Notifications

1. Go to **Settings** → **NTH Notify** → **General** tab
2. Check ✅ **Enable notifications for Telegram**
3. Select order statuses you want to receive notifications for (e.g., Processing, Completed)
4. Click **Save Changes**

#### Step 2: Create Telegram Bot and Get Bot Token

1. Open **Telegram** app on your phone or computer
2. Search and chat with **@BotFather**
3. Send command: `/newbot`
4. Enter your bot name (e.g., `My Store Notifications`)
5. Enter bot username (must end with `bot`, e.g., `mystore_notify_bot`)
6. **BotFather** will return a Bot Token like: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`
7. Copy and save this Bot Token

#### Step 3: Get Chat ID

**Find User Chat ID**

1. Open Telegram and find the bot [@JsonDumpBot](https://t.me/JsonDumpBot)
2. Click **Start** or send any message to the bot
3. You will find your id in the `"id: 123456789` section in the returned message
4. Copy this Chat ID

**Find Channel/Group Chat ID**

1. Forward any message from your channel/group to the bot [@JsonDumpBot](https://t.me/JsonDumpBot)
2. From the returned message, navigate to:
    ```json
    "forward_origin": {
       "type": "channel", // or "group"
       "chat": {
         "id": -123456789,
    	...
       },
    ```
3. Copy the Chat ID (note: Channel/Group Chat ID is usually negative, e.g., `-123456789`)

#### Step 4: Configure in Plugin

1. Go to **Settings** → **NTH Notify** → **Telegram** tab
2. Paste **Bot Token** into the **Bot Token** field
3. Paste **Chat ID** into the **Chat ID #1** field
4. Click **Test** to verify the connection
    - ✅ If successful, you'll receive a test message from the bot
    - ❌ If failed, double-check Bot Token and Chat ID
5. Click **Save Changes**

#### Step 5: Add Multiple Chat IDs (Optional)

1. In **Telegram** tab, click the **+ Add Chat ID** button
2. Enter the second Chat ID in the newly appeared field
3. Repeat to add more Chat IDs
4. Click **Save Changes**

> 💡 **Tip**: Each Chat ID can be a different person or group. All will receive notifications when there's a new order.

---

### 🔵 Zalo Configuration

#### Step 1: Enable Zalo Notifications

1. Go to **Settings** → **NTH Notify** → **General** tab
2. Check ✅ **Enable notifications for Zalo**
3. Select order statuses you want to receive notifications for
4. Click **Save Changes**

#### Step 2: Create Zalo Bot and Get Bot Token

1. Visit: **https://bot.zapps.me/docs/create-bot/**
2. Follow the instructions to create Zalo Bot:
    - Login with your Zalo account
    - Create new application (Zalo Mini App)
    - Enable Bot API in app settings
    - Copy **Bot Access Token** from management page
3. Save the Bot Token

> ⚠️ **Note**: Zalo Bot Token is usually longer and has different format than Telegram. Make sure to copy the entire token.

#### Step 3: Get Chat ID from Zalo

**Use "Find Chat ID" Feature**

1. Go to **Settings** → **NTH Notify** → **Zalo** tab
2. Paste **Bot Token** into the **Bot Token** field
3. Click **Save Changes** (save token first)
4. Click the **Find Chat ID** button
5. Open **Zalo** app and chat with your bot (search by bot name)
6. Send **any message** to the bot
7. Plugin will automatically fetch Chat ID and fill in the **Chat ID #1** field
8. Click **Save Changes**

#### Step 4: Test Connection

1. In **Zalo** tab, after entering Bot Token and Chat ID
2. Click **Test**
    - ✅ Success: You'll receive a test message on Zalo
    - ❌ Failed: Check Bot Token, Chat ID and ensure bot is approved
3. Click **Save Changes** if not saved yet

#### Step 5: Add Multiple Chat IDs (Optional)

1. In **Zalo** tab, click the **+ Add Chat ID** button
2. Click the **Find Chat ID** button
3. Send a message to the bot from another Zalo account to get the second Chat ID
4. Plugin will automatically fill in the Chat ID in the new field
5. Click **Save Changes**

> 💡 **Tip**: Zalo Chat ID is usually a long alphanumeric string. Make sure there are no extra spaces when pasting.

---

### 🎯 Select Order Status for Notifications

1. Go to **Settings** → **NTH Notify** → **General** tab
2. In **Order Status Notifications** section, check the statuses you want to receive notifications for:
    - 🕐 **Pending** - Order awaiting processing
    - 🛒 **Processing** - Order being processed
    - ⏸️ **On Hold** - Order on hold
    - ✅ **Completed** - Order completed
    - ❌ **Cancelled** - Order cancelled
    - 💸 **Refunded** - Order refunded
    - ⚠️ **Failed** - Order failed
    - 📝 **Draft** - Draft order
3. Click **Save Changes**

### ❓ Frequently Asked Questions (FAQ)

**Q: Can I receive notifications on both Telegram and Zalo simultaneously?**  
A: Yes! Enable both options in **General** tab and configure each channel separately.

**Q: What's the maximum number of Chat IDs?**  
A: Unlimited. You can add as many Chat IDs as you like.

**Q: Test message works but not receiving real order notifications?**  
A: Check:

- Have you enabled notifications in **General** tab?
- Have you selected the correct order statuses?
- Is the order transitioning to the selected status?
- Enable **WP_DEBUG** and check the `wp-content/debug.log` file

**Q: How do I know if the plugin is working?**  
A: Use the **Test Connection** button to send a test message. If received, the plugin is working normally.

**Q: Can I customize message content?**  
A: Currently the plugin has a fixed format. For customization, you can use a filter:

```php
add_filter( 'nth_notifications_new_order_message', function( $message, $order, $format_type ) {
    // Customize $message here
    return $message;
}, 10, 3 );
```

### 🐛 Debug & Troubleshooting

If you encounter issues, enable debug mode:

1. Open the `wp-config.php` file
2. Add or modify:
    ```php
    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_LOG', true );
    define( 'WP_DEBUG_DISPLAY', false );
    ```
3. Create a test order
4. Check the log at `wp-content/debug.log`
5. Look for log lines starting with `NTH Notify -`

### 📞 Support

- **Issues**: [GitHub Issues](../../issues)
- **Email**: thienhungdev@gmail.com
- **Website**: https://wptop.net/

### 📜 License

GPL v2 or later - See [LICENSE](LICENSE) file

---

Made with ❤️ by [Hung Nth](https://wptop.net)
