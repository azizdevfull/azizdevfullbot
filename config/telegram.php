<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    'auto_reply_text' => env('TELEGRAM_AUTO_REPLY_TEXT', 'Salom! Hozir band man, tez orada javob beraman.'),

    /*
     * Commands: owner types /command → bot deletes it and sends the reply text.
     * Key: command name (without slash). Value: reply text.
     */
    'commands' => [
        'hello' => 'Assalomu Alaykum! 👋',
    ],
];
