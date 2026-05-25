<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Gemini)]
#[Model('gemini-flash-lite-latest')]
#[Temperature(0)]
class TranscriptionAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Siz audio xabarlarni matnga o\'giruvchi yordamchisiz. Iltimos, audio xabarni aynan matnga aylantiring. Hech qanday tushuntirish yoki qo\'shimcha gap qo\'shmang. Faqat matnni qaytaring.';
    }
}
