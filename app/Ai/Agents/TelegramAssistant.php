<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Gemini)]
#[Model('gemini-2.0-flash')]
#[Temperature(0.7)]
class TelegramAssistant implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return config('telegram.ai_instructions');
    }
}
