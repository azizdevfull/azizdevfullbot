# AzizDevFull — Telegram AI Business Bot

Telegram Business akkauntiga ulangan shaxsiy AI yordamchi. Mijozlar xabar yozganda avtomatik javob beradi. Admin panel orqali boshqariladi.

---

## Imkoniyatlar

- **AI avtomatik javob** — Gemini AI orqali mijoz xabarlariga professional javob
- **Me Mode** — AI o'zingiz bo'lib yozadi (birinchi shaxsda)
- **Ish vaqti** — belgilangan soatlardan tashqarida maxsus xabar yuboradi
- **Debounce** — ketma-ket xabarlarni birlashtiradi, bitta javob beradi
- **Maxsus buyruqlar** — `/buyruq` → avtomatik javob (mijoz ko'rmaydi)
- **Business ulanishlar** — bir nechta Telegram Business ulash va boshqarish
- **Admin panel** — OTP orqali kirish (parolsiz, Telegram orqali)

---

## Texnologiyalar

| Stack | Versiya |
|---|---|
| PHP | 8.4 |
| Laravel | 13 |
| Laravel AI (Gemini) | 0.x |
| Tailwind CSS | 4 |
| MySQL | 8+ |
| Queue | Database driver |

---

## Talablar

- PHP 8.3+
- Composer
- Node.js 20+
- MySQL 8+
- Publick URL (HTTPS) — webhook uchun (ngrok, Cloudflare Tunnel yoki server)
- **Telegram Premium** akkaunt — Business xususiyati uchun
- Google Gemini API kaliti

---

## O'rnatish

### 1. Reponi klonlash

```bash
git clone https://github.com/username/azizdevfull.git
cd azizdevfull
```

### 2. Avtomatik sozlash

```bash
composer run setup
```

Bu buyruq quyidagilarni bajaradi: `composer install` → `.env` yaratish → `key:generate` → `migrate` → `npm install` → `npm run build`

### 3. .env sozlash

```env
APP_NAME="AzizDev Bot"
APP_URL=https://yourdomain.com        # HTTPS bo'lishi shart (webhook uchun)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=azizdevfull
DB_USERNAME=root
DB_PASSWORD=your_password

QUEUE_CONNECTION=database             # Queue shart — o'zgartirmang
CACHE_STORE=database

TELEGRAM_BOT_TOKEN=1234567890:AAF... # BotFather dan
TELEGRAM_WEBHOOK_SECRET=random_secret_string  # Xavfsizlik uchun, ixtiyoriy
TELEGRAM_DEBOUNCE_SECONDS=3          # Xabarlarni birlashtirish vaqti (soniya)

GEMINI_API_KEY=AIza...               # aistudio.google.com dan
```

### 4. Webhook o'rnatish

```bash
php artisan telegram:set-webhook
```

Yoki URL ni qo'lda ko'rsatish:

```bash
php artisan telegram:set-webhook https://yourdomain.com/telegram/webhook
```

Muvaffaqiyatli bo'lsa: `Webhook set: https://yourdomain.com/telegram/webhook`

### 5. Telegram Business ulash

1. Telegram → Settings → Telegram Business → Chatbots
2. Botingizni qo'shing va **"Can Reply to Messages"** ni yoqing
3. Bot webhook ga `business_connection` update keladi — avtomatik saqlanadi

---

## Ishga tushirish

### Development

```bash
composer run dev
```

Bu bir vaqtda ishga tushiradi:
- `php artisan serve` — web server
- `php artisan queue:listen` — queue worker (AI javoblar uchun shart)
- `php artisan pail` — log viewer
- `npm run dev` — Vite HMR

### Production

```bash
npm run build
php artisan config:cache
php artisan route:cache

# Queue worker (supervisor yoki systemd bilan)
php artisan queue:work --tries=3
```

---

## Admin Panel

URL: `https://yourdomain.com/admin`

### Kirish (OTP)

1. Login sahifasida **"Kod yuborish"** tugmasini bosing
2. Botingizga 6 raqamli OTP kodi keladi (5 daqiqa amal qiladi)
3. Kodni kiriting — panel ochiladi

> Agar OTP kelmasa, `ADMIN_TELEGRAM_CHAT_ID` ni `.env` ga qo'shing yoki avval Business ulanishni amalga oshiring.

### Panel imkoniyatlari

| Bo'lim | Nima qiladi |
|---|---|
| **Business Ulanishlar** | Ulangan akkauntlarni ko'rish, yoqish/o'chirish |
| **Buyruqlar** | `/buyruq → javob` qo'shish va o'chirish |
| **Ish Vaqti** | Soatlarni, timezone va tashqari javobni sozlash |
| **AI Sozlamalari** | AI yoqish/o'chirish, fallback javob, debounce |
| **AI Ko'rsatmalar** | System prompt — AI qanday javob berishini sozlash |
| **Me Mode Ko'rsatmalar** | Me Mode uchun alohida system prompt |

---

## Bot Buyruqlari

Bu buyruqlar faqat **siz** (akkaunt egasi) tomonidan yoziladi. Bot xabarni o'chiradi, mijoz ko'rmaydi.

### Standart buyruqlar

| Buyruq | Nima qiladi |
|---|---|
| `/hello` | `Assalomu Alaykum! 👋` yuboradi |
| `/memode` | Me Mode ni yoqadi/o'chiradi (shu chat uchun) |

> Admin paneldan yangi buyruqlar qo'shishingiz mumkin.

### Me Mode

Me Mode yoqilganda AI **siz bo'lib** yozadi — "Azizbek javob beradi" emas, balki "Men..." deb birinchi shaxsda.

**Ikkita usul:**

**1. Per-chat** — faqat bir mijoz bilan suhbatda:
```
(O'sha chat ichida) /memode
```
Bot xabarni o'chiradi. Sizning shaxsiy chatingizga: `Me Mode yoqildi ✅`
24 soatdan keyin avtomatik o'chadi.

**2. Global** — barcha chatlarda:
```
(Botga to'g'ridan) /memode
```
Botga shaxsiy xabar sifatida `/memode` yuboring. Barcha chatlar uchun yonadi, o'chguncha saqlanadi.

**O'chirish:** Yana `/memode` yozing — o'chadi.

---

## Arxitektura

```
Telegram → POST /telegram/webhook
              ↓
    TelegramWebhookController
              ↓
    ┌─────────────────────────┐
    │  business_connection    │ → BusinessConnection saqlash
    │  business_message       │ → handleBusinessMessage()
    │  message (to'g'ridan)   │ → handleDirectMessage() [/memode global]
    └─────────────────────────┘
              ↓
    Egadan kelsa → buyruq tekshir → /memode → toggleMeMode()
    Mijozdan kelsa → debounceAiReply()
              ↓
    Cache (3s debounce) → ProcessBusinessMessagesJob (queue)
              ↓
    ┌─────────────────────────────┐
    │  Ish vaqti tekshir          │
    │  AI yoqiqmi tekshir         │
    │  Me Mode tekshir            │ ← per-chat Cache YO global BotSetting
    │  TelegramAssistant.prompt() │ ← Gemini Flash Lite
    └─────────────────────────────┘
              ↓
    sendMessage() → Telegram Business API
```

### Muhim fayllar

```
app/
├── Ai/Agents/TelegramAssistant.php     # AI agent (Gemini)
├── Http/Controllers/
│   ├── TelegramWebhookController.php   # Webhook handler
│   └── AdminController.php             # Admin panel
├── Jobs/ProcessBusinessMessagesJob.php # AI javob (queue)
└── Models/
    ├── BotSetting.php                  # key-value sozlamalar
    ├── BusinessConnection.php          # Telegram Business ulanishlar
    └── TelegramCommand.php             # Maxsus buyruqlar

config/telegram.php                     # Default sozlamalar
```

### Ma'lumotlar bazasi

| Jadval | Maqsad |
|---|---|
| `business_connections` | Ulangan Telegram Business akkauntlar |
| `bot_settings` | Admin panel sozlamalari (key-value) |
| `telegram_commands` | Maxsus buyruqlar va javoblar |
| `jobs` | Queue worker uchun |
| `cache` | Debounce lock va Me Mode holati |

---

## Muammolar

**Queue ishlamaydi → AI javob bermaydi**
```bash
php artisan queue:work
```

**Webhook xatoligi**
```bash
# Hozirgi webhook holatini tekshirish
curl https://api.telegram.org/bot{TOKEN}/getWebhookInfo
```

**OTP kelmayapti**
```env
ADMIN_TELEGRAM_CHAT_ID=123456789   # Sizning Telegram ID ingiz
```

**Gemini xatoligi**
```bash
php artisan pail   # real-time loglar
# yoki
tail -f storage/logs/telegram.log
```

**Vite manifest xatoligi**
```bash
npm run build
```

---

## Litsenziya

MIT
