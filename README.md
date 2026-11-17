# eCard Kotakia — Platform Kad Jemputan Perkahwinan Digital

Laravel 13 · PHP 8.4 · MySQL 8.4 · Nginx · Tailwind CSS 4 · Docker

## Mula (Docker)

```bash
docker compose up -d --build
```

Container `app` akan secara automatik: `composer install` (jika perlu), jana `APP_KEY`, `migrate` dan `db:seed`.

| Servis       | URL                          | Nota                                   |
|--------------|------------------------------|----------------------------------------|
| Aplikasi     | http://localhost:8080        |                                        |
| phpMyAdmin   | http://localhost:8082        | Server `mysql`, user `kotakia` / `secret` (root: `rootsecret`) |
| MySQL (host) | `127.0.0.1:3307`             | untuk HeidiSQL / DBeaver dsb.          |

Port boleh ditukar dalam `.env` (`APP_PORT`, `PMA_PORT`, `DB_FORWARD_PORT`).

**Akaun admin lalai:** `admin@kotakia.test` / `password` (tukar melalui `ADMIN_EMAIL` / `ADMIN_PASSWORD` sebelum seed pertama).

### Aset CSS/JS
Aset telah di-build ke `public/build`. Selepas mengubah view/CSS:

```bash
npm install && npm run build          # jika Node ada di host
docker compose run --rm node          # atau guna container Node
```

### Arahan berguna
```bash
docker compose exec app php artisan test           # jalankan ujian
docker compose exec app php artisan migrate:fresh --seed
docker compose logs -f app
```

## Aliran Sistem

1. **Pelanggan daftar** → pilih **pakej** & **tema** → isi maklumat asas → draf kad dicipta.
2. Lengkapkan butiran: pengantin, tuan rumah, tarikh/lokasi (Google Maps & Waze), ayat jemputan, doa,
   aturcara, orang untuk dihubungi, RSVP, lagu latar, video (YouTube / muat naik), galeri, salam kaut (akaun + QR).
3. **Pratonton** kad bila-bila masa (`/dashboard/kad/{id}/pratonton`).
4. **Bayar & Aktifkan** → gateway (ToyyibPay / simulasi) → kad aktif serta-merta.
5. Halaman urus kad memaparkan **pautan kad + ayat jemputan** sedia salin, butang WhatsApp/Telegram,
   dan penjana **pautan peribadi tetamu** (`/k/slug?to=Nama` → "Kepada: Nama").
6. Tetamu buka kad → RSVP (hadir/tidak, pax, ucapan) → pemilik pantau di dashboard & eksport CSV.

**Admin** (`/admin`): ringkasan hasil, bayaran (+ pengesahan manual), kad (gantung/aktifkan), pelanggan, pakej (CRUD), tema (warna/aktif/premium).

## Pembayaran

`.env`:
```
PAYMENT_GATEWAY=simulasi     # development — halaman bank palsu
PAYMENT_GATEWAY=toyyibpay    # sebenar
TOYYIBPAY_SANDBOX=true       # false untuk production
TOYYIBPAY_SECRET_KEY=...
TOYYIBPAY_CATEGORY_CODE=...
```

- Return URL: `/bayaran/kembali`, Callback: `POST /bayaran/callback` (dikecualikan CSRF).
- Status sentiasa **disahkan semula** melalui API `getBillTransactions` (jumlah turut disemak) — parameter dari browser tidak dipercayai.
- `markAsPaid()` idempotent & dikunci (`lockForUpdate`) — selamat jika return & callback tiba serentak.
- Callback ToyyibPay memerlukan URL awam: guna `APP_URL` domain sebenar atau tunnel (ngrok / cloudflared) semasa ujian sandbox.

Gateway lain (Billplz, Stripe dsb.) boleh ditambah dengan melaksanakan `App\Services\Payment\PaymentGateway`
dan mendaftarkannya dalam `PaymentManager`.

## Struktur Utama

```
app/Models/                 User, Package, Theme, Invitation, GalleryImage, Rsvp, Payment
app/Services/Payment/       PaymentManager, ToyyibPayGateway, SimulatedGateway
app/Http/Controllers/       Home, Auth, PublicInvitation, Payment
  Customer/                 Dashboard, Invitation, Gallery, Rsvp, Profile
  Admin/                    Dashboard, Package, Theme, Invitation, Payment, User
resources/views/invitation/show.blade.php   ← kad jemputan awam (semua tema, CSS variables)
database/seeders/DatabaseSeeder.php         ← 3 pakej + 6 tema (idempotent)
docker/                     Dockerfile PHP-FPM, entrypoint, php.ini, nginx.conf
```

**Tema** dikawal oleh `palette` (JSON) dalam jadual `themes` — warna & fon Google. Tambah tema baharu
cukup dengan menambah baris ke seeder/DB; tiada view baharu diperlukan.

**Fail muat naik** disimpan di `storage/app/public` dan dihidangkan terus oleh Nginx melalui `/storage/`
(tiada symlink diperlukan — serasi Windows). Jika tidak guna Docker, jalankan `php artisan storage:link`.

## Production — senarai semak
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`
- Tukar semua kata laluan DB & admin; buang/had akses phpMyAdmin
- `PAYMENT_GATEWAY=toyyibpay`, `TOYYIBPAY_SANDBOX=false`
- `php artisan config:cache route:cache view:cache`
- Konfigurasi mel (`MAIL_*`) jika mahu tambah e-mel resit / reset kata laluan
