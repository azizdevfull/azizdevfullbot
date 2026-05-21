<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),

    /*
     * Commands: owner types /command → bot deletes it and sends the reply text.
     * Key: command name (without slash). Value: reply text.
     */
    'commands' => [
        'hello' => 'Assalomu Alaykum! 👋',
    ],

    'fallback_reply' => 'Xabaringiz qabul qilindi. Tez orada javob beraman! ✅',

    'debounce_seconds' => env('TELEGRAM_DEBOUNCE_SECONDS', 3),

    'ai_instructions' => '
Sen Azizbek Isroilovning shaxsiy Telegram yordamchisisisan.
Azizbek — tajribali Laravel va PHP dasturchisi, shuningdek Telegram bot ishlab chiqaruvchisi.

Vazifang: Azizbek nomidan keluvchi xabarlarga professional va samimiy javob berish.

Muhim qoidalar:
- Foydalanuvchi qaysi tilda yozsa, shu tilda javob ber (o\'zbek, rus yoki ingliz).
- Javoblar qisqa, aniq va samimiy bo\'lsin.
- Agar narx yoki muddatni so\'rasa: "Loyiha talabiga qarab belgilanadi, batafsil gaplashsak bo\'ladi" de.
- Agar texnik savol bo\'lsa: "Azizbek tez orada batafsil javob beradi" de.
- Hech qachon noto\'g\'ri ma\'lumot berma, bilmasang — "Azizbek o\'zi javob beradi" de.
- Emoji ishlatishga ruxsat bor, lekin haddan oshirma.
    ',

    'me_mode_instructions' => '
Sen Azizbek Isroilovsan. Birinchi shaxsda yoz.

Qoidalar:
- Hech qachon o\'zingni uchinchi shaxsda aytma.
- Foydalanuvchi qaysi tilda yozsa, shu tilda javob ber.
- Oddiy, tabiiy, samimiy odam sifatida yoz — texnik emas.
- Javoblar QISQA — xuddi do\'stga Telegram chatda yozganday.
- Salom → "Salom, yaxshimisiz?" Xolos. Ko\'p yozma.
- Suhbatdosh siz desa — siz, sen desa — sen deb javob ber.
- Narx/muddat: "Gaplashsak aniq aytaman."
- Bilmasang: "Hozir eslolmadim" yoki "Keyinroq qarayman."
- Agar texnik savol bo\'lsa — o\'shanda qisqa texnik javob ber.
- Emoji oz, o\'rinli.
- 2-3 jumladan oshirma — agar ko\'proq so\'ralmasin.
    ',

    'media_replies' => [
        'uz' => [
            'siz' => [
                "Hozir shovqinli joyda edim, yozib qo'ysangiz",
                'Ovozni eshita olmadim, matn yozing',
                "Hozir qulay emas, yozib qo'ysangiz javob beraman",
                "Shovqin ko'p atrofda, matni yozsangiz tezroq bo'ladi",
                "Eshita olmadim, qisqacha yozib qo'ysangiz",
                "Hozir eshitish imkoni yo'q, yozib qo'ying",
                "Hozir tinch joy yo'q atrofda, yozing",
                "Band edim, matn yozsangiz tezroq ko'raman",
                "Atrofda shovqin, yozib qo'ysangiz javob beraman",
                'Eshita olmadim hozir, yozib yuboring',
            ],
            'sen' => [
                "Hozir shovqinli joyda edim, yozib qo'ysang",
                'Ovozni eshita olmadim, matn yoz',
                "Hozir qulay emas, yozib qo'ysang javob beraman",
                "Shovqin ko'p atrofda, matni yozsang tezroq bo'ladi",
                "Eshita olmadim, qisqacha yozib qo'ysang",
                "Hozir eshitish imkoni yo'q, yozib qo'y",
                "Hozir tinch joy yo'q atrofda, yoz",
                "Band edim, matn yozsang tezroq ko'raman",
                "Atrofda shovqin, yozib qo'ysang javob beraman",
                'Eshita olmadim hozir, yozib yuborsan',
            ],
        ],
        'kk' => [
            'siz' => [
                'Қазір шулы жерде едім, жазып қойсаңыз',
                'Дауысты ести алмадым, мәтін жазыңыз',
                'Қазір ыңғайлы емес, жазып қойсаңыз жауап берем',
                'Айналада шу көп, мәтінмен жазсаңыз тезірек болады',
                'Ести алмадым, қысқаша жазып қойыңыз',
                'Қазір тыңдау мүмкіндігі жоқ, жазып қойыңыз',
                'Қазір тыныш жер жоқ, жазыңыз',
                'Сәл бос емес едім, мәтін жазсаңыз тезірек көремін',
                'Айналада шулы, жазып қойсаңыз жауап берем',
                'Қазір ести алмадым, жазып жіберіңіз',
            ],
            'sen' => [
                'Қазір шулы жерде едім, жазып қойсаң',
                'Дауысты ести алмадым, мәтін жаз',
                'Қазір ыңғайлы емес, жазып қойсаң жауап берем',
                'Айналада шу көп, мәтінмен жазсаң тезірек болады',
                'Ести алмадым, қысқаша жазып қой',
                'Қазір тыңдау мүмкіндігі жоқ, жазып қой',
                'Қазір тыныш жер жоқ, жаз',
                'Сәл бос емес едім, мәтін жазсаң тезірек көремін',
                'Айналада шулы, жазып қойсаң жауап берем',
                'Қазір ести алмадым, жазып жіберші',
            ],
        ],
        'ru' => [
            'siz' => [
                'Сейчас в шумном месте, напишите текстом',
                'Не смог прослушать, напишите',
                'Сейчас неудобно слушать, напишите — отвечу',
                'Вокруг шумно, текстом будет быстрее',
                'Не расслышал, напишите коротко',
                'Сейчас нет возможности слушать, напишите',
                'Тихого места нет сейчас, напишите текстом',
                'Был занят, напишите текстом — быстрее увижу',
                'Шумно вокруг, напишите — отвечу',
                'Не смог послушать, напишите',
            ],
            'sen' => [
                'Сейчас в шумном месте, напиши текстом',
                'Не смог прослушать, напиши',
                'Сейчас неудобно слушать, напиши — отвечу',
                'Вокруг шумно, текстом будет быстрее',
                'Не расслышал, напиши коротко',
                'Сейчас нет возможности слушать, напиши',
                'Тихого места нет сейчас, напиши текстом',
                'Был занят, напиши текстом — быстрее увижу',
                'Шумно вокруг, напиши — отвечу',
                'Не смог послушать, напиши',
            ],
        ],
    ],
];
