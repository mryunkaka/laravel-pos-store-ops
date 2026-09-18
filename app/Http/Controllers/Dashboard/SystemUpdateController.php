<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SystemUpdateController extends Controller
{
    private const UPDATE_DIR = 'app/system-update';
    private const LOG_FILE = 'app/system-update/update.log';
    private const PID_FILE = 'app/system-update/update.pid';
    private const DONE_FILE = 'app/system-update/update.done';
    private const SCRIPT_FILE = 'app/system-update/run-update.ps1';

    public function index(): View
    {
        $logPath = storage_path(self::LOG_FILE);

        return view('system-update.index', [
            'isRunning' => $this->isRunning(),
            'log' => File::exists($logPath) ? File::get($logPath) : 'Belum ada update dijalankan.',
            'doneAt' => File::exists(storage_path(self::DONE_FILE)) ? trim(File::get(storage_path(self::DONE_FILE))) : null,
        ]);
    }

    public function run(): RedirectResponse
    {
        $result = $this->startUpdate();

        if (!$result['started']) {
            return redirect()->route('system-update.index')->with('error', 'Update masih berjalan. Tunggu sampai selesai.');
        }

        return redirect()->route('system-update.index')->with('success', 'Update dimulai. Halaman log akan refresh otomatis.');
    }

    public function test(Request $request): Response
    {
        $this->abortUnlessLocal($request);

        $logPath = storage_path(self::LOG_FILE);
        $log = File::exists($logPath) ? e(File::get($logPath)) : 'Belum ada update dijalankan.';
        $status = $this->isRunning() ? 'Sedang berjalan' : 'Siap';
        $doneAt = File::exists(storage_path(self::DONE_FILE)) ? e(trim(File::get(storage_path(self::DONE_FILE)))) : '-';

        return response(<<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="5">
    <title>Update Web POS3</title>
    <style>
        body{font-family:Arial,sans-serif;margin:24px;line-height:1.45;color:#111827}a.button{display:inline-block;background:#2563eb;color:white;padding:10px 14px;border-radius:6px;text-decoration:none}pre{background:#111827;color:#f9fafb;padding:16px;border-radius:8px;white-space:pre-wrap;max-height:520px;overflow:auto}.muted{color:#6b7280}
    </style>
</head>
<body>
    <h1>Update Web POS3</h1>
    <p>Status: <strong>{$status}</strong></p>
    <p>Selesai terakhir: {$doneAt}</p>
    <p><a class="button" href="/update-web.start" onclick="return confirm('Jalankan update web sekarang?')">Jalankan Update Web</a></p>
    <p class="muted">Link darurat jika GUI/sidebar tidak bisa dibuka. Hanya berjalan dari localhost.</p>
    <h2>Log</h2>
    <pre>{$log}</pre>
</body>
</html>
HTML);
    }

    public function startFromLink(Request $request): Response
    {
        $this->abortUnlessLocal($request);

        $result = $this->startUpdate();
        $message = $result['message'];

        return response(<<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="2;url=/update-web.test">
    <title>Update Web POS3</title>
</head>
<body>
    <h1>{$message}</h1>
    <p>Log akan terbuka otomatis.</p>
    <p><a href="/update-web.test">Buka log update</a></p>
</body>
</html>
HTML);
    }

    private function startUpdate(): array
    {
        if ($this->isRunning()) {
            return ['started' => false, 'message' => 'Update masih berjalan. Tunggu sampai selesai.'];
        }

        File::ensureDirectoryExists(storage_path(self::UPDATE_DIR));
        File::put(storage_path(self::LOG_FILE), '[' . now()->format('Y-m-d H:i:s') . "] Mulai update...\r\n");
        File::delete(storage_path(self::DONE_FILE));

        $scriptPath = storage_path(self::SCRIPT_FILE);
        File::put($scriptPath, $this->scriptContent());

        $pidPath = storage_path(self::PID_FILE);
        $logPath = storage_path(self::LOG_FILE);
        $command = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File ' . escapeshellarg($scriptPath)
            . ' -ProjectPath ' . escapeshellarg(base_path())
            . ' -LogPath ' . escapeshellarg($logPath)
            . ' -PidPath ' . escapeshellarg($pidPath)
            . ' -DonePath ' . escapeshellarg(storage_path(self::DONE_FILE));

        pclose(popen('start /B "" ' . $command, 'r'));

        return ['started' => true, 'message' => 'Update dimulai. Halaman log akan refresh otomatis.'];
    }

    private function abortUnlessLocal(Request $request): void
    {
        abort_unless(in_array($request->ip(), ['127.0.0.1', '::1'], true), 403);
    }

    private function isRunning(): bool
    {
        $pidPath = storage_path(self::PID_FILE);

        if (!File::exists($pidPath)) {
            return false;
        }

        $pid = (int) trim(File::get($pidPath));
        if ($pid <= 0) {
            File::delete($pidPath);
            return false;
        }

        exec('tasklist /FI "PID eq ' . $pid . '" /NH', $output);
        $running = collect($output)->contains(fn ($line) => str_contains($line, (string) $pid));

        if (!$running) {
            File::delete($pidPath);
        }

        return $running;
    }

    private function scriptContent(): string
    {
        return <<<'PS1'
param(
    [Parameter(Mandatory=$true)][string]$ProjectPath,
    [Parameter(Mandatory=$true)][string]$LogPath,
    [Parameter(Mandatory=$true)][string]$PidPath,
    [Parameter(Mandatory=$true)][string]$DonePath
)

$ErrorActionPreference = 'Stop'
[System.Diagnostics.Process]::GetCurrentProcess().Id | Set-Content -Path $PidPath -Encoding ASCII

function Write-Log($Message) {
    $time = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    Add-Content -Path $LogPath -Value "[$time] $Message"
}

function Run-Step($Name, $File, [string[]]$Arguments) {
    Write-Log "==> $Name"
    Write-Log ("$File " + ($Arguments -join ' '))
    $process = Start-Process -FilePath $File -ArgumentList $Arguments -WorkingDirectory $ProjectPath -NoNewWindow -Wait -PassThru -RedirectStandardOutput "$LogPath.out" -RedirectStandardError "$LogPath.err"
    if (Test-Path "$LogPath.out") { Get-Content "$LogPath.out" | Add-Content $LogPath; Remove-Item "$LogPath.out" -Force }
    if (Test-Path "$LogPath.err") { Get-Content "$LogPath.err" | Add-Content $LogPath; Remove-Item "$LogPath.err" -Force }
    if ($process.ExitCode -ne 0) { throw "$Name gagal. Exit code $($process.ExitCode)" }
}

try {
    Set-Location $ProjectPath
    Write-Log "Project: $ProjectPath"

    Run-Step 'Git pull' 'git.exe' @('pull', '--ff-only')

    $php = 'C:\Server\php\php.exe'
    if (-not (Test-Path $php)) { $php = 'php.exe' }

    $composerPhar = 'C:\ProgramData\ComposerSetup\bin\composer.phar'
    if (Test-Path $composerPhar) {
        Run-Step 'Composer install' $php @($composerPhar, 'install', '--no-interaction', '--prefer-dist', '--optimize-autoloader', '--ignore-platform-req=php')
    } elseif (Test-Path (Join-Path $ProjectPath 'composer.phar')) {
        Run-Step 'Composer install' $php @('composer.phar', 'install', '--no-interaction', '--prefer-dist', '--optimize-autoloader', '--ignore-platform-req=php')
    } else {
        Run-Step 'Composer install' 'composer.exe' @('install', '--no-interaction', '--prefer-dist', '--optimize-autoloader', '--ignore-platform-req=php')
    }

    Run-Step 'NPM install' 'npm.cmd' @('install')
    Run-Step 'NPM build' 'npm.cmd' @('run', 'build')
    Run-Step 'Laravel optimize clear' $php @('artisan', 'optimize:clear')
    Run-Step 'Laravel migrate' $php @('artisan', 'migrate', '--force')
    Run-Step 'Laravel view cache' $php @('artisan', 'view:cache')

    Write-Log 'Update selesai.'
    (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') | Set-Content -Path $DonePath -Encoding ASCII
}
catch {
    Write-Log ('ERROR: ' + $_.Exception.Message)
    (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') | Set-Content -Path $DonePath -Encoding ASCII
    exit 1
}
finally {
    if (Test-Path $PidPath) { Remove-Item $PidPath -Force }
}
PS1;
    }
}
