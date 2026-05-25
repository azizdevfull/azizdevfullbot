# AzizDevFull — Telegram AI Personal Assistant (Digital Twin)

Shaxsiy Telegram akkauntingiz uchun "Raqamli Egizak" (Digital Twin). Siz band bo'lganingizda yoki vaqtingiz bo'lmaganda, AI sizning nomingizdan muloqot qiladi. Asosiy maqsad — suhbatdoshga AI yozayotganini bildirmasdan, sizning uslubingizda suhbatni davom ettirish.

---

## Imkoniyatlar

- **Me Mode (Digital Twin)** — AI sizning shaxsingizga kiradi. "Men..." deb birinchi shaxsda gapiradi, sizning xarakteringiz va suhbat uslubingizni (slang, emoji, ohang) takrorlaydi.
- **Incognito Muloqot** — Suhbatdosh siz yo'qligingizni yoki bot ishlayotganini sezmasligi uchun maxsus kechikishlar (typing status) va tabiiy javoblar algoritmi.
- **AI avtomatik javob** — Gemini AI orqali har qanday mavzuda (shaxsiy, ish, oilaviy) professional yoki norasmiy javoblar.
- **Til aniqlash** — Har bir chat uchun til avtomatik aniqlanadi (o'zbek, rus, ingliz va h.k.).
- **Siz/Sen boshqaruvi** — Yaqin insonlar bilan "sen", boshqalar bilan "siz" orqali muloqot qilish sozlamasi.
- **Learn Mode (Continuous Learning)** — AI sizning qo'lda yozgan javoblaringizni va muloqot kontekstini o'rganib boradi. 
    *   **Manual Tahlil:** Admin paneldagi chat ichida "Tahlil (Learn)" tugmasi orqali ishga tushiriladi.
    *   **is_manual filtri:** AI faqat siz o'zingiz yozgan javoblarni tahlil qiladi (kamida 3 ta xabar bo'lishi kerak).
    *   **Review & Edit:** AI taklif qilgan yangi qoidalarni (nima qo'shildi, nima olib tashlandi) ko'rib chiqishingiz va saqlashdan oldin tahrirlashingiz mumkin.
    *   **Reject & Feedback:** Agar natija yoqmasa, rad etib sababini yozishingiz mumkin (masalan: "ko'proq emoji ishlat"). AI xatosini tushunib, qaytadan tahlil qiladi.
    *   **Persona History:** Har bir muvaffaqiyatli yangilanishdan so'ng personaning eski holati tarixda saqlab qolinadi. Admin paneldagi "Personalar" bo'limida barcha o'zgarishlarni vaqt va chat kesimida ko'rish mumkin.
- **Granular Boshqaruv** — Har bir chat uchun AI javobini va o'rganish rejimini alohida yoqish/o'chirish imkoniyati. Masalan: sevgan insoningiz bilan chatda AIni o'chirib, faqat o'rganishni (Learn Mode) yoqib qo'yish mumkin.
- **Media xabarlar bilan ishlash** — Ovozli xabar yoki video xabar (video note) kelganda "Hozir eshita olmayman/ko'ra olmayman" kabi tabiiy javoblar. GIF, sticker va oddiy rasmlar (caption'siz) emotsiya sifatida qabul qilinib, ularga xalaqit berilmaydi.
- **Rasm va Video Caption** — Agar foydalanuvchi rasm yoki videoga izoh yozib yuborsa, AI o'sha matnni o'qib javob qaytaradi.
- **Debounce & Context** — Ketma-ket kelgan xabarlarni bitta mazmunga birlashtirib, mantiqiy javob qaytarish.
- **Admin Panel** — Chatlar tarixini kuzatish va kerak bo'lganda muloqotga o'zingiz aralashishingiz uchun qulay interfeys.

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

### Production (manual)

```bash
npm run build
php artisan config:cache
php artisan route:cache

# Queue worker (supervisor yoki systemd bilan)
php artisan queue:work --tries=3
```

### Production (avtomatik — CI/CD)

`main` branchga har push bo'lganda GitHub Actions avtomatik deploy qiladi:

1. Testlar va Pint lint o'tadi
2. VPS ga SSH orqali ulanadi
3. `git pull` → `composer install` → `npm run build`
4. `php artisan migrate --force` + cache
5. Queue workers restart

**Kerakli GitHub Secrets** (Settings → Secrets → Actions):

| Secret | Qiymat |
|---|---|
| `SSH_HOST` | VPS IP |
| `SSH_USER` | SSH user (`root`) |
| `SSH_PASSWORD` | SSH paroli |

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
| **Chatlar** | Barcha faol muloqotlar ro'yxati, tarixi va har bir chat uchun AI/Learn toggle boshqaruvi |
| **Chat Tillari** | Har chat uchun aniqlangan til, murojaat shakli, persona va AI/Learn holatini sozlash |
| **Buyruqlar** | `/buyruq → javob` qo'shish va o'chirish |
| **Ish Vaqti** | Soatlarni, timezone va tashqari javobni sozlash |
| **AI Sozlamalari** | Global AI va Learn Mode yoqish/o'chirish, fallback javob, debounce |
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

### Til va murojat boshqaruvi

Botga to'g'ridan (shaxsiy xabarda) yuboriladi:

| Buyruq | Nima qiladi |
|---|---|
| `/langlist` | Barcha chatlar va ularning tillari |
| `/langset {chat_id} kk` | Chat tili qo'lda belgilash (`uz`, `kk`, `ru`, `en`, `tr`, `ar`) |
| `/langreset {chat_id}` | Tilni avtomatik aniqlanishga qaytarish |
| `/address {chat_id} siz\|sen` | Murojat shaklini o'zgartirish |

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
├── Ai/Agents/
│   ├── TelegramAssistant.php           # AI agent (Gemini) — til va siz/sen bilan
│   └── LanguageDetectionAgent.php      # Til aniqlash agenti
├── Http/Controllers/
│   ├── TelegramWebhookController.php   # Webhook handler
│   └── AdminController.php             # Admin panel
├── Jobs/
│   ├── ProcessBusinessMessagesJob.php  # AI javob (queue)
│   └── SendBusinessMessageJob.php      # Media reply — delayed (queue)
├── Services/
│   └── LanguageDetector.php            # Til aniqlash servisi
└── Models/
    ├── BotSetting.php                  # key-value sozlamalar
    ├── BusinessConnection.php          # Telegram Business ulanishlar
    ├── ChatLanguage.php                # Per-chat til va siz/sen
    └── TelegramCommand.php             # Maxsus buyruqlar

config/telegram.php                     # Default sozlamalar + media_replies listlari
```

### Ma'lumotlar bazasi

| Jadval | Maqsadi |
|---|---|
| `business_connections` | Ulangan Telegram Business akkauntlar |
| `personas` | AI uchun muloqot qoliplari (Personalar) |
| `bot_settings` | Admin panel sozlamalari (key-value) |
| `telegram_commands` | Maxsus buyruqlar va javoblar |
| `chat_languages` | Per-chat til kodi, ism, siz/sen shakli |
| `chat_messages` | Muloqotlar tarixi (User va AI xabarlari) |
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
 real-time loglar
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
