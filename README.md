# Sistem Data Produk UMKM (Tipe C)

Aplikasi CRUD produk UMKM berbasis Laravel, PHP, MySQL, dan PDO.
Dibuat oleh Nikita Arzetty Siregar (2401020030).

## Fitur
- Daftar, tambah, ubah, dan hapus produk
- Pencarian pada kode_produk, nama_produk, dan sku
- Filter berdasarkan kategori
- Validasi server-side, token CSRF, dan pencegahan data duplikat
- Semua query memakai prepared statement
- Tanpa CSS dan JavaScript

## Struktur data
- kategori_produk (id, nama_kategori)
- produk (id, kode_produk, nama_produk, sku, stok,
  kategori_produk_id, created_at, updated_at)
- Relasi satu kategori memiliki banyak produk

## Cara menjalankan
1. Clone repository, lalu `composer install`
2. Salin `.env.example` menjadi `.env`, lalu `php artisan key:generate`
3. Jalankan `database/sql/struktur_produk_umkm.sql` dan
   `database/sql/data_produk_umkm.sql` di MySQL
4. `php artisan serve`, lalu buka http://127.0.0.1:8000/produk

## Route
| Method | URL | Fungsi |
|---|---|---|
| GET | /produk | Indeks, pencarian, filter |
| POST | /produk | Tambah produk |
| GET | /produk/{id}/edit | Form edit |
| PUT | /produk/{id} | Simpan perubahan |
| DELETE | /produk/{id} | Hapus produk |

## Catatan keamanan
File `.env` tidak diunggah ke repository.