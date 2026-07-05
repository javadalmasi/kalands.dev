## تنظیمات وب‌سرویس‌ها

در این بخش می‌توانید تنظیمات کش برای وب‌سرویس‌های Autocomplete و Visitor Info را مدیریت کنید.

### وب‌سرویس‌های قابل تنظیم

- **Autocomplete:**وب‌سرویس جستجوی هوشمند سایت
- **Affiliate:**وب‌سرویس ریدایرکت لینک‌های وابسته (/go/*)
- **Product Page:**صفحات محصول (/product/*)
- **Visitor Info:**وب‌سرویس مشخصات بازدیدکننده (IP، موقعیت، مرورگر)

## تنظیمات TTL و Cache-Control

TTL (Time To Live) زمان نگهداری داده‌ها در کش را مشخص می‌کند. مقدار پیش‌فرض برای Autocomplete و Affiliate ۳۱۵۳۶۰۰۰ ثانیه (یک سال) و برای Product Page ۸۶۴۰۰ ثانیه (۲۴ ساعت) است.

### انواع کش

- **Public:**قابل ذخیره در کش عمومی (CDN، پراکسی)
- **Private:**فقط برای مرورگر کاربر قابل ذخیره است

## بهینه‌سازی وب‌سرور LiteSpeed

این بخش تنظیمات مربوط به بهینه‌سازی سرور LiteSpeed را در فایل .htaccess مدیریت می‌کند.

### تنظیمات قابل تغییر

- Cache Lookup و وابستگی‌ها (ESI، Crawler)
- QUIC Enable برای سرعت بالاتر ارتباطات UDP
- SpdyEnabled برای فعال‌سازی HTTP/2 و HTTP/3
- LSPHP Workers و Process Group برای بهینه‌سازی PHP

## پیشرفته

با فعال‌سازی حالت پیشرفته، می‌توانید به صورت جداگانه برای هر هدر (Cache-Control، X-LiteSpeed-Cache، CDN-Cache-Control و Cloudflare-CDN-Cache) مقدار TTL و نوع کش را تعیین کنید.