<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Tmp0Service
{
    public function upload(string $path, string $filename): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return $this->failure('File PDF invoice tidak tersedia.');
        }

        $expiration = (string) config('services.tmp0.expiration', '30d');
        if (! $this->validExpiration($expiration)) {
            return $this->failure('Konfigurasi expiration tmp0.cc tidak valid.');
        }

        $uploadUrl = (string) config('services.tmp0.upload_url');
        $uploadParts = parse_url($uploadUrl);
        if (($uploadParts['scheme'] ?? '') !== 'https'
            || ($uploadParts['host'] ?? '') !== 'tmp0.cc'
            || ($uploadParts['path'] ?? '') !== '/api/v1/upload'
            || isset($uploadParts['query'])
            || isset($uploadParts['fragment'])) {
            return $this->failure('Konfigurasi URL tmp0.cc tidak valid.');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return $this->failure('File PDF invoice tidak dapat dibaca.');
        }
        if (! str_starts_with($contents, '%PDF-')) {
            return $this->failure('File invoice bukan PDF yang valid.');
        }

        try {
            $response = Http::withOptions([
                'verify' => $this->caBundle(),
            ])->timeout((int) config('services.tmp0.timeout', 30))
                ->acceptJson()
                ->attach('file', $contents, $filename, ['Content-Type' => 'application/pdf'])
                ->post((string) config('services.tmp0.upload_url'), [
                    'expires' => $expiration,
                ]);
        } catch (Throwable $exception) {
            Log::warning('tmp0.cc invoice upload failed', [
                'filename' => $filename,
                'error' => $exception->getMessage(),
            ]);

            return $this->failure('Koneksi ke tmp0.cc gagal atau timeout.');
        }

        if (! $response->successful()) {
            Log::warning('tmp0.cc invoice upload returned HTTP error', [
                'filename' => $filename,
                'status' => $response->status(),
            ]);

            return $this->failure('tmp0.cc menolak upload invoice.');
        }

        $payload = $response->json();
        if (! is_array($payload) || data_get($payload, 'success') !== true) {
            return $this->invalidResponse();
        }

        $fileId = trim((string) data_get($payload, 'fileId'));
        $fullUrl = trim((string) data_get($payload, 'fullUrl'));
        $relativeUrl = trim((string) data_get($payload, 'url'));

        if (! preg_match('/^[A-Za-z0-9_-]+$/', $fileId)) {
            return $this->invalidResponse();
        }

        $url = $this->documentUrl($fileId, $fullUrl, $relativeUrl);
        if ($url === null) {
            return $this->invalidResponse();
        }

        $expiresAt = data_get($payload, 'expiresAt');
        $acceptedExpiration = (string) data_get($payload, 'fileInfo.expires', $expiration);
        if (! $this->validExpiration($acceptedExpiration)) {
            $acceptedExpiration = $expiration;
        }

        try {
            $expiresAt = $expiresAt ? Carbon::parse($expiresAt) : $this->expiryFromDuration($acceptedExpiration);
        } catch (Throwable) {
            return $this->invalidResponse();
        }

        Log::info('tmp0.cc invoice upload success', [
            'file_id' => $fileId,
            'url' => $url,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        return [
            'success' => true,
            'file_id' => $fileId,
            'url' => $url,
            'expires_at' => $expiresAt,
            'delete_url' => data_get($payload, 'deleteUrl'),
        ];
    }

    private function documentUrl(string $fileId, string $fullUrl, string $relativeUrl): ?string
    {
        $base = 'https://tmp0.cc';
        $expectedPath = '/d/'.$fileId;

        if ($fullUrl !== '') {
            $parts = parse_url($fullUrl);
            if (($parts['scheme'] ?? '') !== 'https'
                || ($parts['host'] ?? '') !== parse_url($base, PHP_URL_HOST)
                || ($parts['path'] ?? '') !== $expectedPath
                || isset($parts['query'])
                || isset($parts['fragment'])) {
                return null;
            }

            return $fullUrl;
        }

        if ($relativeUrl !== $expectedPath) {
            return null;
        }

        return $base.$expectedPath;
    }

    private function validExpiration(string $expiration): bool
    {
        return in_array($expiration, ['1h', '6h', '12h', '1d', '3d', '7d', '14d', '30d'], true);
    }

    private function expiryFromDuration(string $expiration): Carbon
    {
        if (str_ends_with($expiration, 'h')) {
            return now()->addHours((int) $expiration);
        }

        return now()->addDays((int) $expiration);
    }

    private function invalidResponse(): array
    {
        Log::warning('tmp0.cc invoice upload returned invalid response');

        return $this->failure('Response tmp0.cc tidak valid.');
    }

    private function failure(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }

    private function caBundle(): bool|string
    {
        $path = config('services.tmp0.ca_bundle');

        return is_string($path) && $path !== '' && is_file($path) ? $path : true;
    }
}
