<?php

namespace App\Http\Controllers\Admin;

use App\Ai\Agents\TelegramAssistant;
use App\Http\Controllers\Controller;
use App\Models\ChatLanguage;
use App\Models\ChatMessage;
use App\Models\Persona;
use App\Models\PersonaHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LearnController extends Controller
{
    public function analyze(int|string $chatId): RedirectResponse
    {
        $chatLang = ChatLanguage::with('persona')->where('chat_id', $chatId)->first();

        if (! $chatLang || ! $chatLang->persona) {
            return back()->with('error', 'Bu chat uchun persona biriktirilmagan.');
        }

        $persona = $chatLang->persona;

        // Get recent messages for analysis
        $messages = ChatMessage::where('chat_id', $chatId)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        if ($messages->where('is_manual', true)->count() < 3) {
            return back()->with('error', 'Tahlil qilish uchun yetarli manual xabarlar yo\'q (kamida 3 ta kerak).');
        }

        $examples = '';
        foreach ($messages->reverse() as $msg) {
            $role = $msg->role === 'user' ? 'Mijoz' : 'Azizbek';
            $examples .= "{$role}: \"{$msg->content}\"\n";
        }

        $prompt = $this->getAnalysisPrompt($persona, $examples);

        try {
            $assistant = new TelegramAssistant(meModeEnabled: true, language: 'uzbek');
            $response = $assistant->prompt($prompt);

            $result = $this->parseJsonResponse($response->text);

            if (! $result) {
                return back()->with('error', 'AI javobini tahlil qilib bo\'lmadi. Qaytadan urinib ko\'ring.');
            }

            Cache::put("learn_pending_{$chatId}", [
                'persona_id' => $persona->id,
                'chat_id' => $chatId,
                'examples' => $examples,
                'current_instruction' => $persona->prompt_instruction,
                'additions' => $result['additions'] ?? [],
                'removals' => $result['removals'] ?? [],
                'changes' => $result['changes'] ?? [],
                'new_instruction' => $result['new_instruction'] ?? '',
                'history' => [], // For iterative feedback
            ], now()->addHour());

            return redirect()->route('admin.learn.review', $chatId);
        } catch (\Throwable $e) {
            return back()->with('error', 'Xatolik yuz berdi: '.$e->getMessage());
        }
    }

    public function review(int|string $chatId): View|RedirectResponse
    {
        $pending = Cache::get("learn_pending_{$chatId}");

        if (! $pending) {
            return redirect()->route('admin.chats.show', $chatId)->with('error', 'Tahlil natijalari topilmadi.');
        }

        return view('admin.learn.review', [
            'pending' => $pending,
            'chatId' => $chatId,
        ]);
    }

    public function save(Request $request, int|string $chatId): RedirectResponse
    {
        $pending = Cache::get("learn_pending_{$chatId}");

        if (! $pending) {
            return redirect()->route('admin.chats.show', $chatId)->with('error', 'Saqlash uchun ma\'lumotlar topilmadi.');
        }

        $request->validate([
            'new_instruction' => 'required|string',
        ]);

        $persona = Persona::find($pending['persona_id']);
        if ($persona) {
            // Record history
            PersonaHistory::create([
                'persona_id' => $persona->id,
                'old_instruction' => $persona->prompt_instruction,
                'new_instruction' => $request->input('new_instruction'),
                'source_chat_id' => $chatId,
            ]);

            $persona->update([
                'prompt_instruction' => $request->input('new_instruction'),
            ]);
        }

        Cache::forget("learn_pending_{$chatId}");

        return redirect()->route('admin.chats.show', $chatId)->with('success', 'Persona muvaffaqiyatli yangilandi!');
    }

    public function reject(Request $request, int|string $chatId): RedirectResponse
    {
        $pending = Cache::get("learn_pending_{$chatId}");

        if (! $pending) {
            return redirect()->route('admin.chats.show', $chatId)->with('error', 'Ma\'lumotlar topilmadi.');
        }

        $request->validate([
            'reason' => 'required|string',
        ]);

        $reason = $request->input('reason');
        $persona = Persona::find($pending['persona_id']);

        // Add to history for context
        $history = $pending['history'] ?? [];
        $history[] = [
            'proposed' => $pending['new_instruction'],
            'reason' => $reason,
        ];

        $historyContext = '';
        foreach ($history as $index => $h) {
            $n = $index + 1;
            $historyContext .= "Urinish #{$n}:\nTaklif qilingan: {$h['proposed']}\nRad etilish sababi: {$h['reason']}\n\n";
        }

        $prompt = $this->getAnalysisPrompt($persona, $pending['examples'], $historyContext, $reason);

        try {
            $assistant = new TelegramAssistant(meModeEnabled: true, language: 'uzbek');
            $response = $assistant->prompt($prompt);

            $result = $this->parseJsonResponse($response->text);

            if (! $result) {
                return back()->with('error', 'AI javobini tahlil qilib bo\'lmadi.');
            }

            $pending['additions'] = $result['additions'] ?? [];
            $pending['removals'] = $result['removals'] ?? [];
            $pending['changes'] = $result['changes'] ?? [];
            $pending['new_instruction'] = $result['new_instruction'] ?? '';
            $pending['history'] = $history;

            Cache::put("learn_pending_{$chatId}", $pending, now()->addHour());

            return back()->with('success', 'AI xatolarni ko\'rib chiqdi va yangi taklif tayyorladi.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Xatolik: '.$e->getMessage());
        }
    }

    private function getAnalysisPrompt(Persona $persona, string $examples, string $historyContext = '', string $lastReason = ''): string
    {
        $base = "Siz Azizbek Isroilovning shaxsiy AI yordamchisisiz. 
Azizbekning yangi yozishmalarini tahlil qiling va uning '{$persona->name}' personasi uchun yo'riqnomani (system prompt) yangilash bo'yicha taklif bering.

Azizbekning yozishmalari:
{$examples}

Hozirgi yo'riqnoma:
{$persona->prompt_instruction}
";

        if ($historyContext) {
            $base .= "\nAvvalgi urinishlar va rad etilish sabablari:\n{$historyContext}\n";
            $base .= "Oxirgi rad etilish sababi: \"{$lastReason}\". Iltimos, buni inobatga oling va yaxshiroq taklif bering.\n";
        }

        $base .= "\nJavobni FAQAT quyidagi JSON formatida qaytaring:
{
  \"additions\": [\"yangi qo'shilgan qoida 1\", ...],
  \"removals\": [\"olib tashlangan yoki eskirgan qoida 1\", ...],
  \"changes\": [{\"from\": \"eskisi\", \"to\": \"yangisi\"}, ...],
  \"new_instruction\": \"to'liq yangilangan system prompt\"
}";

        return $base;
    }

    private function parseJsonResponse(string $text): ?array
    {
        // Extract JSON if AI wrapped it in markdown
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            return json_decode($matches[0], true);
        }

        return json_decode($text, true);
    }
}
