# POS3 - Panduan Instalasi Lokal Windows

Panduan ini untuk memasang POS3 di PC lokal konsumen sampai bisa dibuka dari browser, otomatis jalan saat Windows menyala, dan bisa update lewat Git.

Target folder:

```text
Installer   : C:\Server\Installer
Server      : C:\Server
Project     : D:\Project\Web\pos3
URL         : https://localhost:8443
Nginx       : C:\Server\nginx
PHP         : C:\Server\php
NSSM        : C:\Server\nssm
MySQL       : 127.0.0.1:3306
Database    : point_of_sale
Service     : pos3-web
```

Aturan folder:

```text
C:\Server\Installer    Simpan installer Git, Composer, Node.js, HeidiSQL, VC++ Redistributable, MySQL Server
C:\Server              Simpan hasil extract Nginx, NSSM, PHP, runner, log
D:\Project\Web\pos3   Simpan project Laravel POS3
```

## 1. Download installer

Download dulu semua file ini, simpan di `C:\Server\Installer`.

| Aplikasi | Link resmi |
| --- | --- |
| Git for Windows | https://git-scm.com/download/win |
| PHP 8.5.10 NTS VS17 x64 | https://windows.php.net/download/ |
| Nginx 1.31.6 | https://nginx.org/en/download.html |
| NSSM 2.24 | https://nssm.cc/download |
| Composer Windows | https://getcomposer.org/download/ |
| Node.js LTS | https://nodejs.org/en/download |
| MySQL Community Server | https://dev.mysql.com/downloads/mysql/ |
| HeidiSQL | https://www.heidisql.com/download.php |
| VC++ Redistributable x64 | https://aka.ms/vs/17/release/vc_redist.x64.exe |

Install biasa:

1. Git for Windows.
2. Composer.
3. Node.js LTS.
4. MySQL Server.
5. HeidiSQL.
6. VC++ Redistributable x64.

Extract ZIP:

```text
C:\Server\php
C:\Server\nginx
C:\Server\nssm
```

## 2. Setting MySQL Server

Saat install MySQL:

```text
Type        : Developer Default atau Server Only
Port        : 3306
User        : root
Password    : isi password yang aman
Service     : MySQL80
Startup     : Automatic
```

Buka HeidiSQL, buat session baru:

```text
Network type : MySQL (TCP/IP)
Hostname     : 127.0.0.1
User         : root
Password     : password MySQL
Port         : 3306
```

Buat database:

```sql
CREATE DATABASE point_of_sale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 3. Setting PHP 8.5.10

Copy file:

```text
C:\Server\php\php.ini-development
```

Rename jadi:

```text
C:\Server\php\php.ini
```

Edit `php.ini`, pastikan baris ini aktif:

```ini
extension_dir="ext"
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=zip

memory_limit=512M
upload_max_filesize=64M
post_max_size=64M
max_execution_time=120
date.timezone=Asia/Jakarta
curl.cainfo="C:\\Server\\certs\\cacert.pem"
openssl.cafile="C:\\Server\\certs\\cacert.pem"
```

Buat folder sertifikat dan download CA bundle publik:

```powershell
New-Item -ItemType Directory -Force C:\Server\certs
curl.exe -fsSL https://curl.se/ca/cacert.pem -o C:\Server\certs\cacert.pem
```

File ini wajib ada agar PHP cURL dapat memverifikasi HTTPS `tmp0.cc`. Jangan memakai `verify=false`.

Tambah PHP ke PATH Windows:

```text
C:\Server\php
```

Cek di PowerShell baru:

```powershell
php -v
php -m | findstr /i "pdo_mysql mbstring openssl fileinfo gd zip intl"
```

## 4. Git pull project POS3

Buka PowerShell biasa:

```powershell
mkdir D:\Project\Web
cd D:\Project\Web
git clone https://github.com/mryunkaka/laravel-pos-store-ops.git pos3
cd D:\Project\Web\pos3
```

Kalau folder sudah ada, cukup:

```powershell
cd D:\Project\Web\pos3
git pull --ff-only
```

## 5. Install dependency project

Masuk folder project:

```powershell
cd D:\Project\Web\pos3
composer install --no-interaction --prefer-dist --optimize-autoloader
npm.cmd install
npm.cmd run build
```

Kalau Composer global tidak terbaca, pakai Composer PHAR:

```powershell
php C:\ProgramData\ComposerSetup\bin\composer.phar install --no-interaction --prefer-dist --optimize-autoloader
```

Jika PowerShell menolak `npm` karena `running scripts is disabled`, pakai `npm.cmd` seperti contoh di atas. Jangan pakai `npm` tanpa `.cmd`.

Jika PC memakai PHP 8.5 dan Composer menolak `phpoffice/phpspreadsheet 1.30.1 requires php <8.5`, lakukan `git pull` terbaru dulu. Versi dependency sudah diperbarui agar compatible dengan PHP 8.5.

## 6. Buat file `.env`

Copy:

```powershell
copy .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_NAME="POS3"
APP_ENV=local
APP_DEBUG=false
APP_URL=https://localhost:8443

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=point_of_sale
DB_USERNAME=root
DB_PASSWORD=ISI_PASSWORD_MYSQL

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

Jangan commit `.env`.

## 7. Migrasi database

Untuk PC baru:

```powershell
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize:clear
php artisan view:cache
```

Kalau restore database dari backup SQL, restore dulu lewat HeidiSQL, lalu jalankan:

```powershell
php artisan migrate --force
php artisan optimize:clear
php artisan view:cache
```

## 8. Setting Nginx

Edit file:

```text
C:\Server\nginx\conf\nginx.conf
```

Isi minimal:

```nginx
worker_processes  1;

events {
    worker_connections 1024;
}

http {
    include       mime.types;
    default_type  application/octet-stream;
    server_names_hash_bucket_size 64;
    sendfile      on;
    keepalive_timeout 65;

    server {
        listen 8082;
        server_name localhost;
        return 301 https://$host:8443$request_uri;
    }

    server {
        listen 8443 ssl;
        server_name localhost;
        root D:/Project/Web/pos3/public;
        index index.php index.html;

        ssl_certificate C:/Server/certs/pos3-local.crt.pem;
        ssl_certificate_key C:/Server/certs/pos3-local.key.pem;
        ssl_protocols TLSv1.2 TLSv1.3;
        client_max_body_size 64M;

        location / {
            try_files $uri /index.php?$query_string;
        }

        location ~ \.php$ {
            try_files $uri =404;
            include fastcgi_params;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $document_root;
        }

        location ~ /\. {
            deny all;
        }
    }
}
```

Jangan menambahkan blok `deny` untuk URI `/database/`, `/storage/`, `/config/`, atau folder Laravel lain pada virtual host POS3. `root` sudah menunjuk ke folder `public`; blok tersebut akan ikut memblokir route Laravel `/database/backup` dan menghasilkan `403 Forbidden nginx/1.31.6`. Untuk route Laravel, gunakan `try_files $uri /index.php?$query_string;`, bukan `try_files $uri $uri/ /index.php?$query_string;`, agar URI `/database/backup` tidak berhenti pada resolusi folder fisik.

Test config:

```powershell
cd C:\Server\nginx
.\nginx.exe -t -p C:\Server\nginx -c conf\nginx.conf
```

Harus muncul:

```text
syntax is ok
test is successful
```

### 8.1 HTTPS lokal untuk kamera barcode

URL aplikasi lokal:

```text
https://localhost:8443
```

URL dari perangkat lain satu jaringan:

```text
https://ALAMAT_IP_PC:8443
```

Sertifikat lokal harus memiliki SAN untuk hostname/IP yang dipakai. Contoh sertifikat PC saat ini:

```text
C:\Server\certs\pos3-local.crt.pem
C:\Server\certs\pos3-local.key.pem
SAN: localhost, 127.0.0.1, 10.77.147.173
```

Import hanya file sertifikat publik ke Root store Windows. Jangan membagikan file `*-key.pem`.

```powershell
certutil.exe -user -addstore -f Root C:\Server\certs\pos3-local.crt.pem
```

Restart service Nginx dari PowerShell Administrator setelah konfigurasi berubah:

```powershell
& 'C:\Server\nginx\nginx.exe' -t -p 'C:\Server\nginx' -c 'conf\nginx.conf'
& 'C:\Server\nssm\win64\nssm.exe' restart pos3-web
```

Verifikasi:

```powershell
curl.exe -k -I https://localhost:8443/
netstat.exe -ano | findstr ":8443"
```

Browser membutuhkan secure context untuk `navigator.mediaDevices.getUserMedia`. Kamera fisik tetap perlu izin Camera pada browser/Windows. Jika izin ditolak, buka pengaturan site browser lalu izinkan Camera; aplikasi menampilkan status/error kamera.

#### Scanner barcode HP ke PC kasir

1. Buka POS pada PC kasir melalui `https://localhost:8443/pos`.
2. Klik `HP Scanner`. Aplikasi membuat link scanner sementara dan menyalinnya ke clipboard.
3. Buka link itu pada HP yang berada di jaringan LAN yang sama. Link memakai IP LAN PC agar HP tidak diarahkan ke `localhost`.
4. Trust sertifikat lokal pada HP sebelum memberi izin kamera. Jangan salin private key `*-key.pem`.
5. Klik `Mulai Kamera` satu kali. Setelah barcode terbaca, HP menampilkan status berhasil dan bunyi beep, lalu tetap standby untuk scan berikutnya.
6. PC polling event sementara dan otomatis menambah produk ke cart transaksi aktif. Scanner tidak membuat order atau mengubah database; order tersimpan hanya saat kasir checkout.
7. Cache scanner berlaku 8 jam. Jika link kedaluwarsa, klik `HP Scanner` lagi untuk membuat sesi baru.

Jika HP tidak dapat membuka kamera, gunakan `https://10.77.147.173:8443/pos` pada PC agar link pairing langsung memakai IP LAN, lalu trust sertifikat `pos3-local.crt.pem` pada HP. Tes kamera fisik, audio, barcode nyata, dan koneksi LAN tetap perlu dilakukan pada perangkat target.

### 8.2 Sertifikat publik dengan win-acme

win-acme tidak dapat menerbitkan sertifikat publik untuk `localhost`. Gunakan domain sungguhan, misalnya `pos.example.com`, dengan DNS mengarah ke IP publik server dan TCP `80` dapat dijangkau Let's Encrypt. Untuk domain publik, gunakan port `443` atau pertahankan `8443` dengan URL port eksplisit.

Referensi resmi:

- https://letsencrypt.org/docs/certificates-for-localhost/
- https://www.win-acme.com/reference/cli
- https://www.win-acme.com/reference/plugins/source/manual
- https://www.win-acme.com/reference/plugins/validation/http/filesystem
- https://www.win-acme.com/reference/plugins/store/pemfiles

Buat folder challenge dan tambahkan server HTTP sementara sebelum menjalankan win-acme:

```powershell
New-Item -ItemType Directory -Force C:\Server\acme-challenge
```

```nginx
server {
    listen 80;
    server_name pos.example.com;

    location ^~ /.well-known/acme-challenge/ {
        root C:/Server/acme-challenge;
        default_type text/plain;
        try_files $uri =404;
    }

    location / {
        return 301 https://$host$request_uri;
    }
}
```

Jalankan `wacs.exe` sebagai Administrator. Ganti domain dan email contoh:

```powershell
.\wacs.exe --source manual --host pos.example.com --validation filesystem --webroot C:\Server\acme-challenge --store pemfiles --pemfilespath C:\Server\certs --pemfilesname pos3 --installation none --accepttos --emailaddress admin@example.com
```

File PEM yang dipakai Nginx:

```text
C:\Server\certs\pos3-chain.pem
C:\Server\certs\pos3-key.pem
```

Ubah vhost HTTPS menjadi:

```nginx
ssl_certificate C:/Server/certs/pos3-chain.pem;
ssl_certificate_key C:/Server/certs/pos3-key.pem;
```

Lalu validasi dan restart service. win-acme membuat renewal terjadwal; setiap renewal harus diikuti reload/restart Nginx agar sertifikat baru dibaca.

## 9. Buat runner PowerShell

Buat file:

```text
C:\Server\pos3_runner.ps1
```

Isi:

```powershell
$ErrorActionPreference = 'Stop'

$phpCgi = 'C:\Server\php\php-cgi.exe'
$phpIni = 'C:\Server\php\php.ini'
$nginxDir = 'C:\Server\nginx'
$nginxExe = Join-Path $nginxDir 'nginx.exe'
$logDir = 'C:\Server\logs'

New-Item -ItemType Directory -Force -Path $logDir | Out-Null

function Stop-Children {
    Get-Process php-cgi -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
    Get-Process nginx -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
}

Stop-Children

Start-Process -FilePath $phpCgi -ArgumentList @('-b', '127.0.0.1:9000', '-c', $phpIni) -WindowStyle Hidden -RedirectStandardOutput "$logDir\php-cgi.out.log" -RedirectStandardError "$logDir\php-cgi.err.log"
Start-Sleep -Seconds 2

Start-Process -FilePath $nginxExe -ArgumentList @('-p', $nginxDir, '-c', 'conf\nginx.conf') -WorkingDirectory $nginxDir -WindowStyle Hidden -RedirectStandardOutput "$logDir\nginx.out.log" -RedirectStandardError "$logDir\nginx.err.log"

while ($true) {
    Start-Sleep -Seconds 10

    $phpAlive = Get-Process php-cgi -ErrorAction SilentlyContinue
    $nginxAlive = Get-Process nginx -ErrorAction SilentlyContinue

    if (-not $phpAlive -or -not $nginxAlive) {
        Stop-Children
        throw 'php-cgi atau nginx berhenti.'
    }
}
```

## 10. Install service NSSM

Buka PowerShell **Run as Administrator**.

```powershell
$nssm = 'C:\Server\nssm\win64\nssm.exe'

& $nssm install pos3-web powershell.exe
& $nssm set pos3-web AppParameters '-NoProfile -ExecutionPolicy Bypass -File "C:\Server\pos3_runner.ps1"'
& $nssm set pos3-web AppDirectory 'C:\Server'
& $nssm set pos3-web DisplayName 'POS3 Local Web'
& $nssm set pos3-web Description 'Nginx + PHP-CGI service for POS3 local web'
& $nssm set pos3-web Start SERVICE_AUTO_START
& $nssm set pos3-web AppStdout 'C:\Server\logs\pos3-web.out.log'
& $nssm set pos3-web AppStderr 'C:\Server\logs\pos3-web.err.log'
& $nssm set pos3-web AppRotateFiles 1
& $nssm set pos3-web AppRotateOnline 1
& $nssm set pos3-web AppRotateBytes 1048576
& $nssm set pos3-web AppExit Default Restart
& $nssm start pos3-web
```

Cek service:

```powershell
sc.exe query pos3-web
& $nssm status pos3-web
```

Harus `RUNNING`.

## 11. Test aplikasi

Cek port:

```powershell
netstat -ano | findstr ":8082"
netstat -ano | findstr ":9000"
```

Cek HTTP:

```powershell
curl.exe -I http://localhost:8082
```

Buka browser:

```text
http://localhost:8082
```

Login default jika seed fresh:

```text
Username/email : admin atau admin@example.com
Password       : password
```

Jika login tidak cocok, cek `database/seeders/UserSeeder.php` atau buat user dari database.

## 12. Cara update manual via Git

Di PC konsumen, buka PowerShell:

```powershell
cd D:\Project\Web\pos3
git pull --ff-only
composer install --no-interaction --prefer-dist --optimize-autoloader
npm.cmd install
npm.cmd run build
php artisan optimize:clear
php artisan migrate --force
php artisan view:cache
```

Kalau memakai Composer PHAR:

```powershell
php C:\ProgramData\ComposerSetup\bin\composer.phar install --no-interaction --prefer-dist --optimize-autoloader
```

Restart service jika perlu:

```powershell
Restart-Service pos3-web
```

## 13. Cara update 1 klik dari web

Aplikasi punya halaman GUI:

```text
http://localhost:8082/settings/update-web
```

Jika GUI/sidebar tidak bisa dibuka, pakai link darurat lokal:

```text
http://localhost:8082/update-web.test
```

Klik:

```text
Jalankan Update Web
```

Halaman itu menjalankan:

```text
git pull --ff-only
composer install
npm.cmd install
npm.cmd run build
php artisan optimize:clear
php artisan migrate --force
php artisan view:cache
```

Log muncul di halaman dan tersimpan di:

```text
D:\Project\Web\pos3\storage\app\system-update\update.log
```

Catatan:

1. User Windows service harus punya akses ke folder project.
2. Update web tidak butuh Administrator jika hanya `git pull`, vendor, build, dan migration.
3. Administrator hanya dibutuhkan jika restart service atau ubah konfigurasi Windows/NSSM.

## 14. Perintah servis penting

PowerShell Administrator:

```powershell
Restart-Service pos3-web
Stop-Service pos3-web
Start-Service pos3-web
sc.exe query pos3-web
```

NSSM:

```powershell
C:\Server\nssm\win64\nssm.exe edit pos3-web
C:\Server\nssm\win64\nssm.exe remove pos3-web confirm
```

Aktifkan konfigurasi Nginx manual melalui service POS3:

```powershell
cd C:\Server\nginx
.\nginx.exe -t -p C:\Server\nginx -c conf\nginx.conf
Restart-Service pos3-web
```

Jangan memakai `nginx -s reload` dari PowerShell biasa. Service `pos3-web` menjalankan Nginx dengan konteks yang benar.

## 15. Troubleshooting cepat

### 502 Bad Gateway

Cek PHP-CGI:

```powershell
netstat -ano | findstr ":9000"
Get-Content C:\Server\logs\php-cgi.err.log -Tail 50
Get-Content C:\Server\nginx\logs\error.log -Tail 50
```

Restart:

```powershell
Restart-Service pos3-web
```

### Halaman 500 Laravel

```powershell
cd D:\Project\Web\pos3
php artisan optimize:clear
php artisan view:cache
Get-Content storage\logs\laravel.log -Tail 80
```

### Database error

Cek `.env`:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=point_of_sale
DB_USERNAME=root
DB_PASSWORD=ISI_PASSWORD_MYSQL
```

Cek service MySQL:

```powershell
sc.exe query MySQL80
```

### 403 Forbidden pada `/database/backup`

Jika halaman menampilkan `403 Forbidden nginx/1.31.6`, buka:

```text
C:\Server\nginx\conf\nginx.conf
```

Hapus blok berikut jika masih ada pada server POS3:

```nginx
location ~ /(app|bootstrap|config|database|resources|routes|storage|tests|vendor)/ {
    deny all;
}
```

Pastikan `root` tetap mengarah ke folder `public`, lalu jalankan repair otomatis:

```powershell
cd D:\Project\Web\pos3
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\repair-consumer-backup-403.ps1
```

Jalankan PowerShell sebagai **Administrator**. Script hanya memproses server block Nginx port `8082`, membuat backup `nginx.conf`, menghapus blok deny yang salah, memperbaiki `try_files` Laravel jika masih memakai `$uri $uri/`, menjalankan `nginx -t`, restart service, dan mengecek URL. Script tidak menyentuh database atau file transaksi.

Jika script tidak tersedia, perbaiki manual lalu uji dan restart Nginx:

```powershell
cd C:\Server\nginx
.\nginx.exe -t -p C:\Server\nginx -c conf\nginx.conf
.\nginx.exe -s reload -p C:\Server\nginx -c conf\nginx.conf
```

Setelah itu login dengan user yang memiliki permission `database.menu`. Jangan menghapus middleware permission dari route backup.

### Port 8082 dipakai aplikasi lain

```powershell
netstat -ano | findstr ":8082"
tasklist /FI "PID eq NOMOR_PID"
```

Ganti `listen 8082;` di Nginx jika perlu, lalu restart service.

## 16. Checklist serah terima PC konsumen

1. `pos3-web` status `RUNNING`.
2. `http://localhost:8082` bisa dibuka.
3. Login admin berhasil.
4. POS bisa tambah produk ke keranjang.
5. Database `point_of_sale` ada di MySQL.
6. HeidiSQL bisa konek ke database.
7. `git pull --ff-only` berhasil dari folder project.
8. Halaman `settings/update-web` bisa dibuka oleh admin.
9. Windows restart, aplikasi tetap otomatis hidup.

## 17. Struktur folder penting

```text
D:\Project\Web\pos3                  Project Laravel
D:\Project\Web\pos3\.env             Konfigurasi lokal, jangan upload
D:\Project\Web\pos3\storage\logs     Log Laravel
C:\Server\pos3_runner.ps1                  Runner service
C:\Server\logs                             Log runner/PHP
C:\Server\nginx\logs  Log Nginx
```

## 18. Catatan keamanan

1. Ganti password default admin sebelum dipakai toko.
2. Jangan upload `.env`.
3. Backup database rutin dari menu Backup Database.
4. Simpan password MySQL di tempat aman.
5. Jangan buka port 8082 ke internet tanpa firewall, domain, HTTPS, dan audit keamanan.
