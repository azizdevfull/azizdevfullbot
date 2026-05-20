<?php

namespace App\Ai\Agents;

use App\Models\BotSetting;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Gemini)]
#[Model('gemini-flash-lite-latest')]
#[Temperature(0.7)]
class TelegramAssistant implements Agent
{
    use Promptable;

    public function __construct(public readonly bool $meModeEnabled = false) {}

    public function instructions(): Stringable|string
    {
        if ($this->meModeEnabled) {
            return BotSetting::get('me_mode_instructions', config('telegram.me_mode_instructions'));
        }

        return BotSetting::get('ai_instructions', config('telegram.ai_instructions'));
    }
}
