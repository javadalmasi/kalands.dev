# مستند مکانیزم‌های سیستم فعلی (kalands.ir)

> **هدف:** استخراج دقیق مکانیزم‌های فعلی پیش از بازنویسی با **Astro + Bun + PostgreSQL (pgvector)**.
> این فایل توضیح می‌دهد هر مکانیزم چطور کار می‌کند، در کدام URL در دسترس است و چه چیزی نمایش می‌دهد.
>
> **استک فعلی:** Laravel 13 · PHP 8.5 · Livewire 4 · Blade · MySQL · Octane
> **تاریخ استخراج:** ۱۴۰۵/۰۴/۲۳ (2026-07-14)

---

## 🧭 ۰. معماری کلان (مهم‌ترین نکته)

سیستم فعلی یک **پروکسی روی API خارجی** است، نه یک فروشگاه با دیتابیس محصولات محلی:

- داده‌های واقعی محصول (عکس، قیمت، مشخصات، فروشندگان) در دیتابیس محلی **ذخیره نمی‌شوند**.
- در هر بازدید، داده از دو منبع خارجی **Fetch** می‌شود:
  - **دیجی‌کالا:** `89.42.44.25/api/3600/` (کش ۱ ساعت) یا `/api/86400/` (کش ۲۴ ساعت) — با هدر `Host: bws.kalands.ir`
  - **باسلام:** `https://89.42.44.25/api/bs/86400/{version}/` — با همان هدر Host
  - پروکسی‌ها: `ProductController::DigikalaApi($url, $version, $skip, $cache)` و `ProductController::BasalamApi(...)`
- دیتابیس محلی فقط **متادیتا** نگه می‌دارد: `id`, `title`, `store`, `category_id`, `is_active` و لاگ‌ها/کش افیلیت/کامنت‌ها/کاربران.
- کش HTTP (Cache-Control / CDN / Cloudflare / LiteSpeed) به شکل تهاجمی روی پاسخ‌ها ست می‌شود.

**پیامد برای بازنویسی:** منطق «نمایش» عملاً همان mapping فیلدهای پاسخ API به UI است. برای Astro باید تصمیم گرفت که همچنان پروکسی می‌مانیم یا داده را در Postgres مادّی‌سازی (materialize) می‌کنیم.

---

## 🛒 ۱. نمایش محصول (Product Display)

**کنترلر:** `app/Http/Controllers/ProductController.php`
**ویو اصلی:** `resources/views/layouts/product/index.blade.php`

### URLها
| URL | متد | کار |
|-----|-----|-----|
| `/product/{ProductID}` | `ProductRedirect` | ریدایرکت ۳۰۲ به URL کانونیکال با اسلاگ (`/product/{id}/{name}`). ID قدیمی از جدول `product_id_mappings` resolve می‌شود. |
| `/product/{ProductID}/{ProductName}` | `ProductParser` | صفحه کامل محصول را رندر می‌کند. |
| `/product/brand/{BrandName}` | (در `ResultController::brand`) | لیست محصولات یک برند (نه صفحه تک‌محصول). |

### فرمت شناسه محصول
- **دیجی‌کالا:** عدد خام (مثل `12345678`) — در URL دیجی‌کالا `dkp-` prefix دارد.
- **باسلام:** با prefix `XBS-` (مثل `XBS-12345`).

### جریان `ProductParser`
1. `resolveProductId()` → بررسی `product_id_mappings` (نگاشت ID قدیمی→جدید).
2. فراخوانی API:
   - دیجی‌کالا: `DigikalaApi('product/{id}/', 'v2')`
   - باسلام: `BasalamApi('product/{id}', 'api_v1.0')`
3. اگر محصول غیرفعال بود (`is_inactive` / `!is_saleable`) → صفحه «محصولات پیشنهادی» رندر می‌شود.
4. `Product::firstOrCreate()` — رکورد متادیتا در صورت نبود ساخته می‌شود.
5. `ProcessProductCategoriesJob` صف می‌شود (استخراج دسته از breadcrumb → پر کردن `category_id` به‌صورت async).
6. **کامنت‌ها:** روی همه IDهای نگاشته‌شده (`whereIn`)، فقط `status='approved'` و `parent_id IS NULL`، به‌همراه `children.votes` و شمارش لایک/دیس‌لایک.
7. `return view('layouts.product.index', [...])` + هدرهای کش.

### آنچه صفحه محصول نمایش می‌دهد
- **Breadcrumb** (دیجی: `product.breadcrumb[]` · باسلام: زنجیره navigation)
- **گالری تصاویر** (اصلی + بندانگشتی‌ها + مودال) — از `images.main.url[]`
- **عنوان، برند، شناسه، امتیاز**
- **قیمت** (فروش + اصلی + درصد تخفیف)، **گارانتی**، **رنگ‌ها**
- **دکمه خرید/مشاهده** (لینک افیلیت — بخش ۳)
- **مشخصات فنی** (`specifications[]` گروه‌بندی‌شده)
- **لیست فروشندگان** (دیجی: `variants[]` قابل مرتب‌سازی سمت کلاینت · باسلام: vendor + تخمین دیجی)
- **کامنت‌ها** (تب، با فرم و JS challenge)
- **محصولات مرتبط** — کامپوننت Livewire `livewire:product.recommended` که بر اساس دسته لِیزی‌لود می‌شود
- **SEO/schema.org** و مودال اشتراک‌گذاری

### تصاویر و تبدیل CDN — `ImgProfile()`
- تصاویر در render-time از API می‌آیند (کش نمی‌شوند).
- دامنه CDN جایگزین می‌شود و پارامتر OSS اضافه می‌شود:
  `?x-oss-process=image/resize,m_pad,h_{h},w_{w},color_FFFFFF/quality,q_{q}/format,webp`
- سایزها: اصلی `800×800 q90`، بندانگشتی `200×200 q80`.

### مدل‌های مرتبط
- **`products`**: `id`(varchar PK), `title`, `store`(enum digikala|basalam), `category_id`, `is_active`, `api_status`(json), `last_checked_at`, `sitemapped_at`, `indexnow_submitted_at`
- **`product_id_mappings`**: `old_product_id`, `new_product_id`, `store`, `reason`, `is_active` — unique(`old_product_id`,`store`)

---

## 🔎 ۲. جستجو، نتایج، فیلترها و اتوکامپلیت

**کنترلر:** `app/Http/Controllers/ResultController.php` · `AutocompleteController.php`
**ویو:** `resources/views/layouts/result/index.blade.php`

### URLها
| URL | متد | endpoint دیجی‌کالا | توضیح |
|-----|-----|--------------------|-------|
| `/result?q=...` | `query` | `search/?q=...` | جستجوی متنی |
| `/result/{category}` | `category` | `categories/{cat}/search/` | نتایج دسته (prefix `category-` حذف می‌شود) |
| `/result/{category}/{brand}` | `category_brand` | `categories/{cat}/brands/{brand}/search/` | دسته + برند |
| `/product/brand/{brand_name}` | `brand` | `brands/{brand}/` (یا `.../premium/` در ۳۰۱) | صفحه برند |
| `/main/{category}` | `main_category` | `categories/{cat}/search/` | دسته اصلی |
| `/seller/{seller_id}` | `seller` | `sellers/{id}/` | صفحه فروشنده |
| `/api/result/infinite` | `infinite` | (از توکن decode می‌شود) | JSON برای اسکرول بی‌نهایت |
| `/api/services/autocomplete/` | `AutocompleteController@search` | پروکسی `kalands.ir/api/services/autocomplete/` | پیشنهاد جستجو |

### پارامترهای Query
`q`, `page` (۱ تا **حداکثر ۱۰۰**), `sort` (پیش‌فرض ۱), `categories[i]`, `brands[i]`, `colors[i]` (→ `color_palettes[i]`), `list` (پروموشن).
فیلترها **تجمعی** و به‌صورت آرایه ایندکس‌دار در query string ساخته می‌شوند.

### فیلترها
- منبع گزینه‌ها: خود پاسخ API → `data.filters.categories | brands | color_palettes` (هرکدام `id`, `title_fa/title_en`, برای رنگ `hex_code`).
- گزینه‌های مرتب‌سازی: `data.sort_options[]`.
- وضعیت فعال هر فیلتر از روی `request()` بازسازی می‌شود (state در URL نگه‌داری می‌شود، نه سرور).
- برندها/رنگ‌ها قابل جستجوی سمت کلاینت هستند (`result-page.js`).

### اسکرول بی‌نهایت
- توکن **HMAC-SHA256** (با `app.key`) که `path` + query نرمال‌شده را امضا می‌کند (`encodeInfiniteToken`/`decodeInfiniteToken`).
- `IntersectionObserver` (sentinel با `rootMargin` ۳۰۰px + prefetch ۱۲۰۰px).
- پاسخ JSON: `{ html, hasMore, nextPage, isEmpty }`.
- **حذف تکراری‌ها** سمت کلاینت با `data-product-key` (`id:`/`uri:`/`title:`) و سمت سرور.
- `history.replaceState` پارامتر `page` را در URL بروز می‌کند.

### اتوکامپلیت
- `GET /api/services/autocomplete/?q=...` → پروکسی به `kalands.ir` → فقط `data.auto_complete[]` برگردانده می‌شود.
- کش تا ۱ سال اگر نتیجه غیرخالی باشد.

### محصولات پیشنهادی / مرتبط
- در صفحه محصول از طریق Livewire `product.recommended` بر اساس **دسته** لود می‌شود (نه vector).

### جستجوی برداری (pgvector فعلی؟)
- `CategoryVectorService` **فقط برای نگاشت دسته‌ها در پنل ادمین** استفاده می‌شود، نه جستجوی کاربر.
- حالت local: n-gram ۳ کاراکتری؛ حالت external: POST به API embedding (مثل `gemma-4`). شباهت: cosine، آستانه ذخیره‌شده ۰.۶.

---

## 🔗 ۳. سیستم لینک‌سازی افیلیت

**کنترلر:** `app/Http/Controllers/AffiliateRedirectController.php`
**مدل‌ها:** `AffiliateLink`, `AffiliateDailyStat`

### URLها
| URL | متد | کار |
|-----|-----|-----|
| `/go/{slug}` | `redirect` | resolve اسلاگ → ریدایرکت ۳۰۲ به لینک افیلیت |
| `/api/bslm/{productId}` | `fetchAndRedirect` | ⚠️ **متد در کنترلر وجود ندارد — روت شکسته است** |

### فرمت اسلاگ
- باسلام: `b{productId}` — regex `/^b([0-9a-z]+)$/`
- دیجی‌کالا: `d{productId}` — regex `/^d([0-9a-z]+)$/`
- جستجوی دیجی: `ds_{base64(query)}`

### ساخت لینک مقصد
- **دیجی‌کالا (dgkl.io):** `https://dgkl.io/api/v1/Click/b/4dJ4L?b64={base64(productUrl)}`
  - آدرس محصول: `https://www.digikala.com/product/dkp-{id}/`
- **باسلام (bslm.ir):** فراخوانی `POST https://api-affiliate.basalam.com/api/v1/tracking/links`
  - payload: `merchant_id`, `reference_type=PRODUCT`, `reference_id`, `title`, `utm_campaign`
  - `short_url` بازگشتی در جدول `affiliate_links` کش می‌شود (prefix `https://a.bslm.ir/api/v1/tracking/click/`).

### جریان `/go/{slug}` (باسلام)
1. کش DB: `AffiliateLink::where(product_id, store=basalam)`.
   - status=`error` → 404؛ status=`active` → استفاده از لینک کش‌شده.
2. اگر نبود → فراخوانی API باسلام با هدر auth (کوکی `access_token` از تنظیمات).
3. ذخیره نتیجه در `affiliate_links` (`active`/`error`).
4. ریدایرکت ۳۰۲ (`redirect()->away()`) با هدرهای `X-Robots-Tag: noindex,nofollow`, `Referrer-Policy: no-referrer`, و کش قابل‌پیکربندی.

### مدل‌ها
- **`affiliate_links`**: `id`, `store`(20), `product_id`(120), `slug`, `link`(text), `click_count`, `status`(active|error|disabled)
- **`affiliate_daily_stats`**: `id`, `date`, `store`, `clicks` — unique(`date`,`store`)

### تنظیمات افیلیت (کلید `affiliate.basalam` در SettingsRepository)
`merchant_id`, `access_token` (حساس/رمزنگاری‌شده), `url_prefix`, `cache_ttl_minutes`.
پنل ادمین: مشاهده/جستجو/سورت لینک‌ها، toggle وضعیت، حذف، export/import (لینک‌ها + آمار به‌صورت JSON).

### ⚠️ خلأهای فعلی (برای بازنویسی مهم)
1. **شمارش کلیک پیاده‌سازی نشده** — `click_count` و `daily_stats` هرگز از روی کلیک واقعی افزایش نمی‌یابند (فقط import دستی).
2. روت `/api/bslm/{productId}` به متد ناموجود اشاره دارد.
3. ردیابی conversion/خرید وجود ندارد.

---

## 🏠 ۴. صفحه خانه، دسته‌بندی‌ها، فروشندگان، اسلایدرها

**کنترلر:** `app/Http/Controllers/HomeController.php` · **ویو:** `resources/views/layouts/home/index.blade.php`

### صفحه خانه (`/`)
- کش با کلید `home` به‌مدت ۱ ساعت.
- **اسلایدر:** `SliderStorage->loadByModule('home_main_banners')`.
- **بنر دسته‌ها:** `HomeCategoryBannerStorage->load()`.
- **محصولات (از API دیجی):**
  - `offers` ← `incredible-offers/products/?page=1`
  - `selling_stock` ← `search/?has_selling_stock=1&pageno=1&sortby=22`
- ترتیب بخش‌های صفحه: بنر اصلی → اسلایدر محصولات (offers) → آیکون دسته‌ها (بالا) → محصولات (selling_stock) → بنرهای دسته → آیکون دسته‌ها (پایین).

### دسته‌بندی‌ها
- **`categories`**: `id`, `parent_id`(self)، `title`, `name_en`, `store`(digikala|basalam|snappshop), `product_count`, `external_id`, `vector`(json), `vector_source`, `vector_model`.
- سلسله‌مراتب self-referential با عمق نامحدود (`parent()`/`children()`).
- **`category_mappings`**: `digikala_category_id`, `source_category_id`, `confidence`, `is_manual` — نگاشت بین فروشگاه‌ها.
- `CategoryService`: `findOrCreateFromBreadcrumb()`, `updateProductCounts()`, `getTree()`, `importSnappShop()`.
- اسلاگ اسنپ‌شاپ: باید `/category/{name_en}` (دقیقاً ۲ سگمنت) باشد.

### فروشندگان
- **مدل Seller وجود ندارد.** صفحه فروشنده کاملاً API-driven است (`sellers/{id}/`) بدون کش DB. محصولات هیچ FK فروشنده ندارند.

### اسلایدر و بنرها
- **`sliders`**: `id`, `module_name`, `title`, `status`, `config_json` (شامل effect/autoplay/loop/breakpoints/payload_url و کانفیگ جدا برای desktop/mobile).
- **`slider_items`**: `image`, `title`, `subtitle`, `button_text`, `button_link`, `sort_order`, `is_active`, `slide_type`, `meta_json`(device: desktop|mobile).
- `HomeCategoryBannerStorage` (کلید `home.category_banners`): بخش‌های `banners` (حداکثر ۲)، `categories_top`، `categories_bottom` با `view_type` (grid|slider).
- `HomeItemsPayloadStorage`: کل کانفیگ را در فایل استاتیک `public/assets/home-items/homeitems-{rand}.json` منتشر می‌کند (Swiper.js مستقیم می‌خواند).

### مگامنو
- کلید تنظیمات `megamenu.config` (JSON سلسله‌مراتبی). ادمین: ویرایشگر منو + تست لینک شکسته + تنظیمات. فرانت از ترکیب درخت دسته + گروه‌های سفارشی ساخته می‌شود (`menu.js`, `menuData.js`).

---

## 👤 ۵. کاربران و مهاجرت داده (تنها بخشی که مهاجرت می‌شود)

**مدل:** `app/Models/User.php`

فیلدهای موجود (fillable): `first_name`, `last_name`, `email`, `phone`, `password_hash`, `password_salt`, ...
Casts: `email_verified_at`, `phone_verified_at`. اکسسور `name` = `first_name + last_name`.

### دامنه مهاجرت به سیستم جدید
فقط این فیلدها منتقل می‌شوند:
- ✅ `first_name` (نام)
- ✅ `last_name` (نام خانوادگی)
- ✅ `phone` (شماره موبایل)
- ✅ `email` (ایمیل)
- ❌ رمز عبور منتقل **نمی‌شود** → برای همه کاربران **ریست اجباری** (سیستم فعلی `password_hash` + `password_salt` سفارشی دارد که در استک جدید بازطراحی می‌شود، مثلاً argon2/bcrypt استاندارد).

هیچ داده دیگری (بوکمارک، لایک، کامنت، تیکت، آنالیتیکس، افیلیت) مهاجرت نمی‌شود.

---

## 📋 ۶. جمع‌بندی برای بازنویسی (Astro + Bun + Postgres/pgvector)

| مکانیزم | وضعیت فعلی | تصمیم لازم برای سیستم جدید |
|---------|-----------|---------------------------|
| داده محصول | پروکسی زنده روی API خارجی، بدون ذخیره‌سازی | آیا Postgres را مادّی‌سازی کنیم یا پروکسی بمانیم؟ |
| جستجو/فیلتر | کاملاً از API دیجی‌کالا | با pgvector می‌توان جستجوی معنایی محلی ساخت |
| اتوکامپلیت | پروکسی به kalands.ir | جایگزینی با ایندکس محلی |
| افیلیت | کش DB + dgkl.io/bslm.ir، بدون ردیابی کلیک | پیاده‌سازی درست شمارش کلیک/آمار |
| دسته‌ها | self-referential + نگاشت + vector | مستقیماً به pgvector منتقل شود |
| فروشنده | فقط API، بدون مدل | تصمیم برای مدل محلی |
| اسلایدر/خانه | تنظیمات JSON + payload استاتیک | معادل در Astro (content collections؟) |
| کاربران | hash/salt سفارشی | فقط name/phone/email مهاجرت + ریست رمز |

---

**پرسش باز برای گام بعد:** آیا در سیستم جدید همچنان روی API خارجی دیجی‌کالا/باسلام تکیه می‌کنیم (مدل پروکسی)، یا محصولات را در Postgres مادّی‌سازی می‌کنیم و pgvector را برای جستجو/پیشنهاد به کار می‌گیریم؟ این تصمیم شکل کل بازنویسی را مشخص می‌کند.
