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
#[Model('gemini-flash-lite-latest')]
#[Temperature(0.0)]
class LanguageDetectionAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are a language detection tool. Analyze the given text and identify its language.

Respond ONLY with a valid JSON object (no markdown, no code blocks), exactly like this:
{"code":"uz","name":"O'zbek"}

Supported language codes and names:
- uz → O'zbek
- kk → Qazaq
- ru → Русский
- en → English
- tr → Türkçe
- ar → العربية
- other → Other

Rules:
- If text mixes languages, pick the dominant one.
- If uncertain, default to uz.
- Never add any extra text, only the JSON.
INSTRUCTIONS;
    }
}
