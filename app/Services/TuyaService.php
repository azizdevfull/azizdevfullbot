<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TuyaService
{
    private string $baseUrl;

    private string $clientId;

    private string $accessSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.tuya.base_url', 'https://openapi.tuyaeu.com');
        $this->clientId = config('services.tuya.client_id');
        $this->accessSecret = config('services.tuya.access_secret');
    }

    /** @return array{id:string, name:string, online:bool, category:string}|null */
    public function getDeviceInfo(string $deviceId): ?array
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return null;
        }

        $response = $this->request('GET', "/v1.0/iot-03/devices/{$deviceId}", $token);

        if (! ($response['success'] ?? false)) {
            Log::warning('Tuya getDeviceInfo failed', $response ?? []);

            return null;
        }

        $r = $response['result'] ?? [];

        return [
            'id' => $r['id'] ?? $deviceId,
            'name' => $r['name'] ?? $deviceId,
            'online' => (bool) ($r['online'] ?? false),
            'category' => $r['category'] ?? '',
        ];
    }

    /** @return array<int, array{code:string, value:mixed}>|null */
    public function getDeviceStatus(string $deviceId): ?array
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return null;
        }

        $response = $this->request('GET', "/v1.0/iot-03/devices/{$deviceId}/status", $token);

        if (! ($response['success'] ?? false)) {
            Log::warning('Tuya getDeviceStatus failed', $response ?? []);

            return null;
        }

        return $response['result'] ?? [];
    }

    /** @return array<int, array{code:string, value:bool, label:string}>|null */
    public function getSwitches(string $deviceId): ?array
    {
        $statuses = $this->getDeviceStatus($deviceId);
        if ($statuses === null) {
            return null;
        }

        return collect($statuses)
            ->filter(fn ($s) => str_starts_with($s['code'], 'switch_') && is_bool($s['value']))
            ->map(fn ($s) => [
                'code' => $s['code'],
                'value' => (bool) $s['value'],
                'label' => 'Switch '.ltrim($s['code'], 'switch_'),
            ])
            ->values()
            ->all();
    }

    /** @return array{voltage:float|null, current:float|null, power:float|null, energy:float|null} */
    public function getMetrics(string $deviceId): array
    {
        $statuses = $this->getDeviceStatus($deviceId);
        if ($statuses === null) {
            return ['voltage' => null, 'current' => null, 'power' => null, 'energy' => null];
        }

        $map = collect($statuses)->keyBy('code');

        return [
            'voltage' => isset($map['cur_voltage']) ? round($map['cur_voltage']['value'] / 10, 1) : null,
            'current' => isset($map['cur_current']) ? round($map['cur_current']['value'] / 1000, 3) : null,
            'power' => isset($map['cur_power']) ? round($map['cur_power']['value'] / 10, 1) : null,
            'energy' => isset($map['add_ele']) ? round($map['add_ele']['value'] / 100, 2) : null,
        ];
    }

    public function sendCommand(string $deviceId, string $code, mixed $value): bool
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return false;
        }

        $body = json_encode(['commands' => [['code' => $code, 'value' => $value]]]);
        $response = $this->request('POST', "/v1.0/iot-03/devices/{$deviceId}/commands", $token, $body);

        if (! ($response['success'] ?? false)) {
            Log::warning('Tuya sendCommand failed', $response ?? []);

            return false;
        }

        return (bool) ($response['result'] ?? false);
    }

    public function toggleSwitch(string $deviceId, string $switchCode = 'switch_1'): ?bool
    {
        $statuses = $this->getDeviceStatus($deviceId);
        if ($statuses === null) {
            return null;
        }

        $current = collect($statuses)->firstWhere('code', $switchCode);
        $newValue = ! ($current['value'] ?? false);
        $success = $this->sendCommand($deviceId, $switchCode, $newValue);

        return $success ? $newValue : null;
    }

    public function getSwitchState(string $deviceId, string $switchCode = 'switch_1'): ?bool
    {
        $statuses = $this->getDeviceStatus($deviceId);
        if ($statuses === null) {
            return null;
        }

        $status = collect($statuses)->firstWhere('code', $switchCode);

        return $status ? (bool) $status['value'] : null;
    }

    private function getAccessToken(): ?string
    {
        return Cache::remember('tuya_access_token', 7000, function () {
            $t = (string) (int) (microtime(true) * 1000);
            $path = '/v1.0/token?grant_type=1';
            $sign = $this->buildSign($t, 'GET', $path, '', null);

            $response = Http::withHeaders([
                'client_id' => $this->clientId,
                'sign' => $sign,
                'sign_method' => 'HMAC-SHA256',
                't' => $t,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.$path);

            $data = $response->json();

            if (! ($data['success'] ?? false)) {
                Log::warning('Tuya token fetch failed', $data ?? []);

                return null;
            }

            return $data['result']['access_token'] ?? null;
        });
    }

    /** @return array<string, mixed>|null */
    private function request(string $method, string $path, string $token, string $body = ''): ?array
    {
        $t = (string) (int) (microtime(true) * 1000);
        $sign = $this->buildSign($t, $method, $path, $body, $token);

        $headers = [
            'client_id' => $this->clientId,
            'access_token' => $token,
            'sign' => $sign,
            'sign_method' => 'HMAC-SHA256',
            't' => $t,
            'Content-Type' => 'application/json',
        ];

        $http = Http::withHeaders($headers);

        if ($method === 'POST') {
            return $http->withBody($body, 'application/json')->post($this->baseUrl.$path)->json();
        }

        return $http->get($this->baseUrl.$path)->json();
    }

    private function buildSign(string $t, string $method, string $path, string $body, ?string $token): string
    {
        $contentHash = hash('sha256', $body);
        $stringToSign = implode("\n", [$method, $contentHash, '', $path]);

        $str = $token
            ? $this->clientId.$token.$t.$stringToSign
            : $this->clientId.$t.$stringToSign;

        return strtoupper(hash_hmac('sha256', $str, $this->accessSecret));
    }
}
