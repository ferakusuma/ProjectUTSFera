# README — Perbandingan Versi Rentan vs Aman

Dokumen ini menjelaskan perbedaan antara _versi rentan_ (vulnerable) dan _versi aman_ (safe) yang disertakan dalam modul latihan keamanan web. Ditulis dalam Bahasa Indonesia untuk keperluan praktikum.

---

## Struktur Project (singkat)
- `dashboard_XSS.php`, `post_vul.php`, `search_vul.php`, ... — versi **rentan** (VULNERABLE)
- `post_safe.php`, `search_safe.php`, ... — versi **aman** (SAFE)
- `artikel_vul.php`, `artikel_safe.php` — demo kerentanan upload file (rentan vs aman)
- `vuln/` — CRUD rentan (list, create, edit, delete) yang menonjolkan SQLi, XSS, dan missing auth
- `safe/` — CRUD aman dengan UUID, token, prepared statements, CSRF
- `config.php` — konfigurasi dan helper (CSRF, DB connection, helper functions)
- `uploads/` — direktori tempat file diupload (jika ada)

> Catatan: Jangan menjalankan versi **rentan** pada lingkungan publik atau server terhubung internet. Gunakan VM/isolated sandbox untuk latihan.

---

## Perbedaan utama: rentan vs aman

### 1. SQL Injection (SQLi)
- **Versi Rentan**
  - Menggunakan string concatenation untuk membangun query SQL (mis. `"... WHERE id = $id"` atau `INSERT ... VALUES ('$title', '$content')`).
  - Tidak ada validasi/escaping parameter.
  - Contoh bahaya: attacker dapat menyisipkan `'; DROP TABLE users; --` untuk memodifikasi query.
- **Versi Aman**
  - Menggunakan *prepared statements* / parameterized queries (`PDO::prepare()` + `execute()`).
  - Menghindari interpolasi langsung variabel ke SQL.
  - Validasi tipe data (cast integer, cek panjang string) sebelum eksekusi.

**Ringkas**: jangan pernah gabungkan input user langsung ke SQL; pakai prepared statements.

---

### 2. Cross-Site Scripting (XSS)
- **Versi Rentan**
  - Menampilkan konten user langsung tanpa escaping (contoh: `echo $comment;`).
  - Stored XSS: komentar disimpan dan dieksekusi di browser pengunjung lain.
- **Versi Aman**
  - Semua output context-aware di-escape (mis. `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).
  - Untuk teks multi-line gunakan `nl2br(htmlspecialchars(...))`.
  - Gunakan Content Security Policy (CSP) tambahan di header jika mungkin.

**Ringkas**: escape output, bukan input — apalagi saat menampilkan HTML/JS.

---

### 3. Kerentanan Upload File (Insecure File Upload)
- **Versi Rentan**
  - Tidak ada validasi ekstensi, MIME, atau ukuran.
  - Menyimpan file dengan nama asli ke folder publik (`uploads/`), memungkinkan upload file PHP dan eksekusi (RCE).
  - Tidak mengacak nama file (risk overwrite / path traversal).
- **Versi Aman**
  - Validasi ekstensi terizinkan (`jpg,jpeg,png,gif,pdf`) dan MIME type (`finfo_file`).
  - Batasi ukuran file (contoh: ≤ 2MB).
  - Simpan nama file acak (mis. `uniqid('upload_')`) dan simpan di folder non-public atau dengan aturan server yang mencegah eksekusi (mis. `uploads/` di luar webroot atau `.htaccess` disallow php execution).
  - Jika perlu tampilkan file melalui script yang memverifikasi akses.

**Ringkas**: validasi ekstensi+MIME+ukuran, acak nama, simpan aman (non-public).

---

### 4. Broken Access Control (IDOR dan sejenisnya)
- **Versi Rentan**
  - Tidak melakukan verifikasi kepemilikan resource (mis. user A dapat mengedit atau menghapus item milik user B dengan akses ID numerik).
  - Mengandalkan parameter client-side saja (mis. `?id=123`) tanpa cek server-side.
- **Versi Aman**
  - Gunakan identifier yang tidak mudah ditebak (UUID) dan/atau token akses.
  - Selalu verifikasi kepemilikan di server: `WHERE uuid = :u AND user_id = :uid`.
  - Terapkan mekanisme otorisasi tambahan (mis. role checks, kemampuan/permission model) dan CSRF protection pada operasi state-changing.

**Ringkas**: otorisasi server-side wajib — jangan percaya input pengguna terkait identitas resource.

---

## Daftar file penting & ringkasan perilaku
- `vuln/list.php`, `vuln/create.php`, `vuln/edit.php`, `vuln/delete.php`  
  Perlihatkan: concatenated SQL (SQLi), output non-escaped (XSS), missing ownership checks (IDOR).
- `safe/` (list, create, edit, view, delete)  
  Perlihatkan: prepared statements, `csrf_token()`/`check_csrf()`, `uuid4()`, `token_generate()` dan token hashing.
- `artikel_vul.php` vs `artikel_safe.php`  
  Demonstrasi insecure upload vs secure upload (validasi ekstensi, MIME, size, random filename).

---

## Cara cepat membedakan saat review kode
1. Cari `->prepare(` atau `prepare(` — biasanya aman bila dipakai benar. Jika `exec()` atau `query()` menerima string yang menyertakan `$_POST`/`$_GET` langsung, waspadai SQLi.
2. Cari `htmlspecialchars`/`ENT_QUOTES` saat menampilkan data user; ketiadaan mencurigakan untuk XSS.
3. Periksa pengecekan kepemilikan resource (user_id vs session user id) sebelum update/delete.
4. Untuk upload: cek adanya pemeriksaan `finfo_file()`, `in_array($ext, $allowed_ext)`, `filesize()` dan penggunaan `uniqid()` atau random name.

---

## Petunjuk praktikum singkat
- Jalankan project di VM atau lingkungan terisolasi (XAMPP/LAMP di VM).
- Gunakan *sample account* yang sudah ada (lihat `config.php` fallback).
- Tes kasus rentan hanya pada lingkungan lokal:  
  - SQLi: masukkan `' OR '1'='1` pada form pencarian.  
  - XSS: kirim komentar `<script>alert('XSS')</script>` pada versi vulnerable.  
  - Upload: coba upload file `.php` pada versi rentan dan akses file tersebut (jika server mengizinkan eksekusi).  
  - Broken Access Control: ubah parameter `id` milik user lain dan coba edit/hapus.

---

## Peringatan & Etika
- Kode rentan hanya untuk pembelajaran — **jangan** deploy ke internet publik.
- Lakukan pengujian hanya di lingkungan yang Anda miliki atau di lingkungan uji yang diizinkan.
- Hargai privasi dan keamanan data orang lain.

---

## Referensi singkat
- OWASP Top 10 — Injection, XSS, Broken Access Control, Insecure File Uploads.  
- PHP.net — PDO, password_hash, finfo_file, htmlspecialchars.  
- Mozilla Developer Network (MDN) — Content Security Policy, safe file handling.

