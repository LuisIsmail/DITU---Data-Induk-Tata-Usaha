# DITU---Data-Induk-Tata-Usaha
Aplikasi pendataan seluruh administrasi sekolah yang dikelola oleh staf Tata Usaha Sekolah.

## Ringkasan aplikasi
Aplikasi ini digunakan untuk mencatat dan mengelola data siswa, data PTK, jadwal pelajaran, bank soal, paket soal, nilai siswa, absensi, profil sekolah, pengaturan sistem, backup, dan import data.

## Arsitektur utama
- Backend: PHP dan MySQL.
- Frontend: halaman PHP server-side dengan JavaScript ringan untuk interaksi.
- Logika utama dan helper ada di `config.php`.
- Layout umum berada di `includes/header.php`.

## Mekanisme utama
- Login menggunakan password hash dan session.
- Pembatasan peran `admin` dan `guru` diterapkan di beberapa halaman dan API.
- Token CSRF disediakan untuk operasi yang mengubah data.
- Sebagian fitur menggunakan API AJAX di folder `api/`.

## Catatan keamanan
- Banyak query SQL dibangun secara manual; lebih aman jika menggunakan prepared statement.
- Upload file perlu validasi lebih ketat terhadap tipe dan ukuran file.
- Rate limit login disimpan di session saja, sehingga tidak kuat terhadap serangan dari banyak sumber.
- `config.php` memakai konfigurasi database default; sebaiknya gunakan akun database dengan hak minimum.

## Saran perbaikan
- Terapkan prepared statement untuk semua query.
- Perkuat validasi dan sanitasi input/output untuk mengurangi risiko XSS.
- Batasi upload file dan hindari menyimpan file yang bisa dieksekusi di folder publik.
- Aktifkan cookie session yang aman jika dijalankan di HTTPS.
