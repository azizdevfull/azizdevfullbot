<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;
use App\Services\TuyaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartHomeController extends Controller
{
    public function __construct(private readonly TuyaService $tuya) {}

    public function index(): View
    {
        $deviceIds = $this->getDeviceIds();
        $devices = [];

        foreach ($deviceIds as $id) {
            $info = $this->tuya->getDeviceInfo($id);
            $switches = $this->tuya->getSwitches($id);
            $metrics = $this->tuya->getMetrics($id);

            $devices[] = [
                'id' => $id,
                'name' => $info['name'] ?? $id,
                'online' => $info['online'] ?? null,
                'switches' => $switches,
                'metrics' => $metrics,
                'error' => $info === null,
            ];
        }

        return view('admin.smart-home.index', compact('devices'));
    }

    public function addDevice(Request $request): RedirectResponse
    {
        $request->validate(['device_id' => 'required|string|max:100']);

        $deviceId = trim($request->input('device_id'));
        $ids = $this->getDeviceIds();

        if (! in_array($deviceId, $ids, true)) {
            $ids[] = $deviceId;
            $this->saveDeviceIds($ids);
        }

        return back()->with('success', "Qurilma qo'shildi: {$deviceId}");
    }

    public function removeDevice(string $deviceId): RedirectResponse
    {
        $ids = array_values(array_filter($this->getDeviceIds(), fn ($id) => $id !== $deviceId));
        $this->saveDeviceIds($ids);

        return back()->with('success', "Qurilma o'chirildi.");
    }

    public function toggle(Request $request, string $deviceId): JsonResponse
    {
        $switchCode = $request->input('switch_code', 'switch_1');
        $newState = $this->tuya->toggleSwitch($deviceId, $switchCode);

        if ($newState === null) {
            return response()->json(['error' => 'Qurilmaga ulanishda xatolik.'], 500);
        }

        return response()->json(['is_on' => $newState]);
    }

    public function setState(Request $request, string $deviceId): JsonResponse
    {
        $request->validate([
            'value' => 'required|boolean',
            'switch_code' => 'nullable|string',
        ]);

        $success = $this->tuya->sendCommand(
            $deviceId,
            $request->input('switch_code', 'switch_1'),
            (bool) $request->input('value')
        );

        if (! $success) {
            return response()->json(['error' => 'Buyruq yuborishda xatolik.'], 500);
        }

        return response()->json(['is_on' => (bool) $request->input('value')]);
    }

    /** @return string[] */
    public function getDeviceIds(): array
    {
        $raw = BotSetting::get('tuya_device_ids', '[]');

        return json_decode($raw, true) ?? [];
    }

    /** @param string[] $ids */
    private function saveDeviceIds(array $ids): void
    {
        BotSetting::set('tuya_device_ids', json_encode(array_values($ids)));
    }
}
