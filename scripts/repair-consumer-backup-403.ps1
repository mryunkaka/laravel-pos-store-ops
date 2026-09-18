[CmdletBinding()]
param(
    [string] $NginxDir = 'C:\Server\nginx',
    [string] $ServiceName = 'pos3-web',
    [int] $Port = 8082,
    [switch] $WhatIf
)

$ErrorActionPreference = 'Stop'
$configPath = Join-Path $NginxDir 'conf\nginx.conf'
$nginxPath = Join-Path $NginxDir 'nginx.exe'
$prefixPath = $NginxDir
$backupPath = "$configPath.bak-$(Get-Date -Format 'yyyyMMdd-HHmmss')"

$principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $WhatIf -and -not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    throw 'Jalankan PowerShell sebagai Administrator.'
}

foreach ($path in @($configPath)) {
    if (-not (Test-Path -LiteralPath $path)) {
        throw "Path tidak ditemukan: $path"
    }
}

if (-not $WhatIf -and -not (Test-Path -LiteralPath $nginxPath)) {
    throw "Path tidak ditemukan: $nginxPath"
}

function Find-ServerBlock([string] $Text) {
    $matches = [regex]::Matches($Text, '(?m)^[ \t]*server\s*\{')

    foreach ($serverMatch in $matches) {
        $depth = 0
        $quote = [char]0
        $escaped = $false

        for ($index = $serverMatch.Index; $index -lt $Text.Length; $index++) {
            $character = $Text[$index]

            if ($quote -ne [char]0) {
                if ($escaped) {
                    $escaped = $false
                } elseif ($character -eq '\') {
                    $escaped = $true
                } elseif ($character -eq $quote) {
                    $quote = [char]0
                }
                continue
            }

            if ($character -eq '"' -or $character -eq "'") {
                $quote = $character
                continue
            }

            if ($character -eq '{') {
                $depth++
            } elseif ($character -eq '}') {
                $depth--
                if ($depth -eq 0) {
                    $length = $index - $serverMatch.Index + 1
                    $block = $Text.Substring($serverMatch.Index, $length)
                    if ($block -match '(?m)^\s*listen\s+(?:[^\s;]*:)?8082(?:\s+[^;]+)?;') {
                        return [pscustomobject]@{
                            Index = $serverMatch.Index
                            Length = $length
                            Text = $block
                        }
                    }
                    break
                }
            }
        }
    }

    return $null
}

$content = [System.IO.File]::ReadAllText($configPath)
$server = Find-ServerBlock $content
if ($null -eq $server) {
    throw 'Server block listener 8082 tidak ditemukan. Periksa konfigurasi POS3 sebelum mengubah file.'
}

if ($server.Text -notmatch '(?im)^\s*root\s+[^;]*pos3[\\/]public') {
    throw 'Server block port 8082 tidak menunjuk ke folder public POS3. Konfigurasi tidak diubah.'
}

$patterns = @(
    '(?ms)\r?\n[ \t]*location\s+~\*?\s+/\(app\|bootstrap\|config\|database\|resources\|routes\|storage\|tests\|vendor\)/\s*\{\s*deny\s+all\s*;\s*\}\s*',
    '(?ms)\r?\n[ \t]*location\s+(?:\^~\s+|=\s+|~\*?\s+)?\^?/database(?:/[^\s{]*)?\s*\{\s*deny\s+all\s*;\s*\}\s*'
)

$updatedServer = $server.Text
$denyRemoved = $false
foreach ($pattern in $patterns) {
    $nextServer = [regex]::Replace(
        $updatedServer,
        $pattern,
        [System.Text.RegularExpressions.MatchEvaluator] { param($match) "`r`n" }
    )
    if ($nextServer -ne $updatedServer) {
        $denyRemoved = $true
    }
    $updatedServer = $nextServer
}

$tryFilesPattern = '(?m)^([ \t]*)try_files[ \t]+\$uri[ \t]+\$uri/[ \t]+(/index\.php\?\$query_string;)[ \t]*$'
$nextServer = [regex]::Replace(
    $updatedServer,
    $tryFilesPattern,
    [System.Text.RegularExpressions.MatchEvaluator] {
        param($match)
        "$($match.Groups[1].Value)try_files `$uri $($match.Groups[2].Value)"
    }
)
$tryFilesChanged = $nextServer -ne $updatedServer
$updatedServer = $nextServer
$configChanged = $updatedServer -ne $server.Text

if (-not $configChanged) {
    Write-Output 'Tidak ditemukan rule Nginx yang memblokir route /database/backup.'
    if ($WhatIf) {
        Write-Output 'WhatIf aktif. Konfigurasi dan service tidak diubah.'
        exit 0
    }
} else {
    $updated = $content.Substring(0, $server.Index) + $updatedServer + $content.Substring($server.Index + $server.Length)
    if ($WhatIf) {
        if ($denyRemoved) {
            Write-Output 'Rule deny untuk /database/ ditemukan pada server block 8082.'
        }
        if ($tryFilesChanged) {
            Write-Output 'Rule try_files Laravel diperbaiki agar route tidak berhenti pada folder fisik.'
        }
        Write-Output 'WhatIf aktif. Konfigurasi dan service tidak diubah.'
        exit 0
    }

    Copy-Item -LiteralPath $configPath -Destination $backupPath -Force
    $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText($configPath, $updated, $utf8NoBom)
    Write-Output "Backup konfigurasi: $backupPath"
    if ($denyRemoved) {
        Write-Output 'Rule deny untuk /database/ dihapus.'
    }
    if ($tryFilesChanged) {
        Write-Output 'Rule try_files Laravel diperbaiki.'
    }
}

$stdoutPath = [System.IO.Path]::GetTempFileName()
$stderrPath = [System.IO.Path]::GetTempFileName()
try {
    $testProcess = Start-Process -FilePath $nginxPath -ArgumentList @('-t', '-p', $prefixPath, '-c', 'conf\nginx.conf') -Wait -PassThru -WindowStyle Hidden -RedirectStandardOutput $stdoutPath -RedirectStandardError $stderrPath
    $testOutput = @(
        Get-Content -LiteralPath $stdoutPath -ErrorAction SilentlyContinue
        Get-Content -LiteralPath $stderrPath -ErrorAction SilentlyContinue
    )
    $testOutput | ForEach-Object { Write-Output $_ }

    if ($testProcess.ExitCode -ne 0) {
        if (Test-Path -LiteralPath $backupPath) {
            Copy-Item -LiteralPath $backupPath -Destination $configPath -Force
        }
        throw "Validasi Nginx gagal dengan exit code $($testProcess.ExitCode). Konfigurasi dikembalikan."
    }
} finally {
    Remove-Item -LiteralPath $stdoutPath, $stderrPath -Force -ErrorAction SilentlyContinue
}

$service = $null
if ($ServiceName) {
    $service = Get-Service -Name $ServiceName -ErrorAction SilentlyContinue
}

if ($null -ne $service) {
    Restart-Service -Name $ServiceName -Force -ErrorAction Stop
    $deadline = (Get-Date).AddSeconds(30)
    do {
        Start-Sleep -Seconds 1
        $state = (Get-Service -Name $ServiceName).Status
    } while ($state -ne 'Running' -and (Get-Date) -lt $deadline)

    if ($state -ne 'Running') {
        throw "Service $ServiceName tidak berjalan. Status: $state"
    }

    Write-Output "Service $ServiceName berjalan."
} else {
    $nginxProcesses = @(
        Get-CimInstance Win32_Process -Filter "Name = 'nginx.exe'" |
            Where-Object {
                $_.ExecutablePath -and
                [string]::Equals(
                    [System.IO.Path]::GetFullPath($_.ExecutablePath),
                    [System.IO.Path]::GetFullPath($nginxPath),
                    [System.StringComparison]::OrdinalIgnoreCase
                )
            }
    )
    $reloadStdout = [System.IO.Path]::GetTempFileName()
    $reloadStderr = [System.IO.Path]::GetTempFileName()
    try {
        if ($nginxProcesses.Count -gt 0) {
            Write-Output 'Service Nginx tidak terdaftar. Reload proses Nginx aktif.'
            $actionArguments = @('-s', 'reload', '-p', $prefixPath, '-c', 'conf\nginx.conf')
        } else {
            Write-Output 'Service Nginx tidak terdaftar dan proses Nginx tidak aktif. Menjalankan Nginx.'
            $actionArguments = @('-p', $prefixPath, '-c', 'conf\nginx.conf')
        }

        $actionProcess = Start-Process -FilePath $nginxPath -ArgumentList $actionArguments -Wait -PassThru -WindowStyle Hidden -RedirectStandardOutput $reloadStdout -RedirectStandardError $reloadStderr
        Get-Content -LiteralPath $reloadStdout -ErrorAction SilentlyContinue | ForEach-Object { Write-Output $_ }
        Get-Content -LiteralPath $reloadStderr -ErrorAction SilentlyContinue | ForEach-Object { Write-Output $_ }
        if ($actionProcess.ExitCode -ne 0) {
            if ($nginxProcesses.Count -eq 0) {
                throw "Start Nginx gagal dengan exit code $($actionProcess.ExitCode)."
            }

            Write-Output 'Reload ditolak Windows. Restart proses Nginx target.'
            foreach ($nginxProcess in $nginxProcesses) {
                Stop-Process -Id $nginxProcess.ProcessId -Force -ErrorAction Stop
            }

            $deadline = (Get-Date).AddSeconds(15)
            do {
                Start-Sleep -Milliseconds 500
                $remaining = @(
                    Get-CimInstance Win32_Process -Filter "Name = 'nginx.exe'" |
                        Where-Object {
                            $_.ExecutablePath -and
                            [string]::Equals(
                                [System.IO.Path]::GetFullPath($_.ExecutablePath),
                                [System.IO.Path]::GetFullPath($nginxPath),
                                [System.StringComparison]::OrdinalIgnoreCase
                            )
                        }
                )
            } while ($remaining.Count -gt 0 -and (Get-Date) -lt $deadline)

            if ($remaining.Count -gt 0) {
                throw 'Proses Nginx target tidak berhenti. Jalankan PowerShell sebagai Administrator.'
            }

            $startProcess = Start-Process -FilePath $nginxPath -ArgumentList @('-p', $prefixPath, '-c', 'conf\nginx.conf') -WorkingDirectory $prefixPath -PassThru -WindowStyle Hidden
            Start-Sleep -Seconds 2
            if ($startProcess.HasExited -and $startProcess.ExitCode -ne 0) {
                throw "Nginx gagal start setelah restart proses. Exit code $($startProcess.ExitCode)."
            }
            Write-Output 'Nginx berhasil start ulang dengan konfigurasi baru.'
        }
    } finally {
        Remove-Item -LiteralPath $reloadStdout, $reloadStderr -Force -ErrorAction SilentlyContinue
    }
}

Start-Sleep -Seconds 2

try {
    $response = Invoke-WebRequest -Uri "http://localhost:$Port/database/backup" -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    $statusCode = [int] $response.StatusCode
    $body = [string] $response.Content
} catch {
    if ($null -eq $_.Exception.Response) {
        throw
    }

    $statusCode = [int] $_.Exception.Response.StatusCode
    $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
    $body = $reader.ReadToEnd()
    $reader.Dispose()
}

if ($body -match '<center><h1>403 Forbidden</h1></center>') {
    throw 'Nginx masih mengembalikan native 403 untuk /database/backup. Periksa server block port 8082.'
}

Write-Output "HTTP /database/backup: $statusCode"
if ($body -match 'Pengguna tidak memiliki izin') {
    Write-Output 'Request sudah sampai Laravel. Login dengan user yang memiliki permission database.menu.'
} else {
    Write-Output 'Native Nginx 403 tidak lagi terdeteksi.'
}
