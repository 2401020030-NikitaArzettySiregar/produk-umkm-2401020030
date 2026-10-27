<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
 * Fungsi bantu sederhana.
 * statusStok() dipakai pada Blade untuk menampilkan status produk.
 */
if (! function_exists('statusStok')) {
    function statusStok(int $stok): string
    {
        if ($stok > 0) {
            return 'Tersedia';
        }

        return 'Habis';
    }
}

if (! function_exists('bersihkanTeks')) {
    function bersihkanTeks(mixed $nilai): string
    {
        return trim(strip_tags((string) $nilai));
    }
}

Route::get('/', function () {
    return view('welcome');
});

/* ===== READ: indeks, pencarian, dan filter ===== */
Route::get('/produk', function (Request $request) {
    try {
        $pdo = DB::connection()->getPdo();

        $kataKunci = bersihkanTeks($request->query('q'));
        $filterKategori = bersihkanTeks($request->query('kategori'));

        $sql = 'SELECT p.id, p.kode_produk, p.nama_produk, p.sku,
                       p.stok, k.nama_kategori
                FROM produk AS p
                JOIN kategori_produk AS k
                    ON k.id = p.kategori_produk_id
                WHERE 1 = 1';
        $parameter = [];

        if ($kataKunci !== '') {
            $sql .= ' AND (p.kode_produk LIKE :cari_kode
                        OR p.nama_produk LIKE :cari_nama
                        OR p.sku LIKE :cari_sku)';
            $pola = '%' . addcslashes($kataKunci, '%_\\') . '%';
            $parameter['cari_kode'] = $pola;
            $parameter['cari_nama'] = $pola;
            $parameter['cari_sku'] = $pola;
        }

        if ($filterKategori !== '' && ctype_digit($filterKategori)) {
            $sql .= ' AND p.kategori_produk_id = :kategori';
            $parameter['kategori'] = $filterKategori;
        }

        $sql .= ' ORDER BY p.kode_produk';

        $pernyataanProduk = $pdo->prepare($sql);
        $pernyataanProduk->execute($parameter);
        $daftarProduk = $pernyataanProduk->fetchAll(\PDO::FETCH_ASSOC);

        $pernyataanKategori = $pdo->prepare(
            'SELECT id, nama_kategori
             FROM kategori_produk
             ORDER BY nama_kategori'
        );
        $pernyataanKategori->execute();
        $daftarKategori = $pernyataanKategori->fetchAll(\PDO::FETCH_ASSOC);

        return view('produk', compact(
            'daftarProduk',
            'daftarKategori',
            'kataKunci',
            'filterKategori'
        ));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('produk.index');

/* ===== CREATE ===== */
Route::post('/produk', function (Request $request) {
    $request->merge([
        'kode_produk' => bersihkanTeks($request->input('kode_produk')),
        'nama_produk' => bersihkanTeks($request->input('nama_produk')),
        'sku' => bersihkanTeks($request->input('sku')),
    ]);

    $data = $request->validate([
        'kode_produk' => ['required', 'regex:/^[A-Za-z0-9]{10}$/'],
        'nama_produk' => ['required', 'string', 'min:3', 'max:100'],
        'sku' => ['required', 'string', 'min:5', 'max:30'],
        'stok' => ['required', 'integer', 'min:0', 'max:500'],
        'kategori_produk_id' => ['required', 'integer'],
    ], [
        'kode_produk.required' => 'Kode produk wajib diisi.',
        'kode_produk.regex' => 'Kode produk harus 10 karakter huruf atau angka.',
        'nama_produk.required' => 'Nama produk wajib diisi.',
        'nama_produk.min' => 'Nama produk minimal 3 karakter.',
        'nama_produk.max' => 'Nama produk maksimal 100 karakter.',
        'sku.required' => 'SKU wajib diisi.',
        'sku.min' => 'SKU minimal 5 karakter.',
        'sku.max' => 'SKU maksimal 30 karakter.',
        'stok.required' => 'Stok wajib diisi.',
        'stok.integer' => 'Stok harus berupa bilangan bulat.',
        'stok.min' => 'Stok minimal 0.',
        'stok.max' => 'Stok maksimal 500.',
        'kategori_produk_id.required' => 'Kategori wajib dipilih.',
        'kategori_produk_id.integer' => 'Kategori tidak valid.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cekKategori = $pdo->prepare(
            'SELECT COUNT(*) FROM kategori_produk WHERE id = :id'
        );
        $cekKategori->execute(['id' => $data['kategori_produk_id']]);

        if ((int) $cekKategori->fetchColumn() === 0) {
            return back()->withInput()->with(
                'gagal',
                'Kategori produk tidak tersedia.'
            );
        }

        $cekDuplikat = $pdo->prepare(
            'SELECT COUNT(*) FROM produk
             WHERE kode_produk = :kode OR sku = :sku'
        );
        $cekDuplikat->execute([
            'kode' => $data['kode_produk'],
            'sku' => $data['sku'],
        ]);

        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Kode produk atau SKU sudah digunakan.'
            );
        }

        $simpan = $pdo->prepare(
            'INSERT INTO produk
             (kode_produk, nama_produk, sku, stok, kategori_produk_id)
             VALUES (:kode, :nama, :sku, :stok, :kategori)'
        );
        $simpan->execute([
            'kode' => $data['kode_produk'],
            'nama' => $data['nama_produk'],
            'sku' => $data['sku'],
            'stok' => $data['stok'],
            'kategori' => $data['kategori_produk_id'],
        ]);

        return redirect()->route('produk.index')->with(
            'berhasil',
            'Produk berhasil ditambahkan.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal disimpan. Periksa koneksi dan data.'
        );
    }
})->name('produk.store');

/* ===== UPDATE: form edit ===== */
Route::get('/produk/{id}/edit', function (string $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $pernyataanProduk = $pdo->prepare(
            'SELECT id, kode_produk, nama_produk, sku, stok,
                    kategori_produk_id
             FROM produk
             WHERE id = :id'
        );
        $pernyataanProduk->execute(['id' => $id]);
        $produk = $pernyataanProduk->fetch(\PDO::FETCH_ASSOC);

        if ($produk === false) {
            return redirect()->route('produk.index')->with(
                'gagal',
                'Produk tidak ditemukan.'
            );
        }

        $pernyataanKategori = $pdo->prepare(
            'SELECT id, nama_kategori
             FROM kategori_produk
             ORDER BY nama_kategori'
        );
        $pernyataanKategori->execute();
        $daftarKategori = $pernyataanKategori->fetchAll(\PDO::FETCH_ASSOC);

        return view('produk-edit', compact('produk', 'daftarKategori'));
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('produk.index')->with(
            'gagal',
            'Data tidak dapat dibaca. Periksa koneksi dan query.'
        );
    }
})->whereNumber('id')->name('produk.edit');

/* ===== UPDATE: simpan perubahan ===== */
Route::put('/produk/{id}', function (Request $request, string $id) {
    $request->merge([
        'kode_produk' => bersihkanTeks($request->input('kode_produk')),
        'nama_produk' => bersihkanTeks($request->input('nama_produk')),
        'sku' => bersihkanTeks($request->input('sku')),
    ]);

    $data = $request->validate([
        'kode_produk' => ['required', 'regex:/^[A-Za-z0-9]{10}$/'],
        'nama_produk' => ['required', 'string', 'min:3', 'max:100'],
        'sku' => ['required', 'string', 'min:5', 'max:30'],
        'stok' => ['required', 'integer', 'min:0', 'max:500'],
        'kategori_produk_id' => ['required', 'integer'],
    ], [
        'kode_produk.required' => 'Kode produk wajib diisi.',
        'kode_produk.regex' => 'Kode produk harus 10 karakter huruf atau angka.',
        'nama_produk.required' => 'Nama produk wajib diisi.',
        'nama_produk.min' => 'Nama produk minimal 3 karakter.',
        'nama_produk.max' => 'Nama produk maksimal 100 karakter.',
        'sku.required' => 'SKU wajib diisi.',
        'sku.min' => 'SKU minimal 5 karakter.',
        'sku.max' => 'SKU maksimal 30 karakter.',
        'stok.required' => 'Stok wajib diisi.',
        'stok.integer' => 'Stok harus berupa bilangan bulat.',
        'stok.min' => 'Stok minimal 0.',
        'stok.max' => 'Stok maksimal 500.',
        'kategori_produk_id.required' => 'Kategori wajib dipilih.',
        'kategori_produk_id.integer' => 'Kategori tidak valid.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cekProduk = $pdo->prepare(
            'SELECT COUNT(*) FROM produk WHERE id = :id'
        );
        $cekProduk->execute(['id' => $id]);

        if ((int) $cekProduk->fetchColumn() === 0) {
            return redirect()->route('produk.index')->with(
                'gagal',
                'Produk tidak ditemukan.'
            );
        }

        $cekKategori = $pdo->prepare(
            'SELECT COUNT(*) FROM kategori_produk WHERE id = :id'
        );
        $cekKategori->execute(['id' => $data['kategori_produk_id']]);

        if ((int) $cekKategori->fetchColumn() === 0) {
            return back()->withInput()->with(
                'gagal',
                'Kategori produk tidak tersedia.'
            );
        }

        $cekKonflik = $pdo->prepare(
            'SELECT COUNT(*) FROM produk
             WHERE (kode_produk = :kode OR sku = :sku)
               AND id <> :id'
        );
        $cekKonflik->execute([
            'kode' => $data['kode_produk'],
            'sku' => $data['sku'],
            'id' => $id,
        ]);

        if ((int) $cekKonflik->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Kode produk atau SKU sudah digunakan produk lain.'
            );
        }

        $ubah = $pdo->prepare(
            'UPDATE produk
             SET kode_produk = :kode,
                 nama_produk = :nama,
                 sku = :sku,
                 stok = :stok,
                 kategori_produk_id = :kategori
             WHERE id = :id'
        );
        $ubah->execute([
            'kode' => $data['kode_produk'],
            'nama' => $data['nama_produk'],
            'sku' => $data['sku'],
            'stok' => $data['stok'],
            'kategori' => $data['kategori_produk_id'],
            'id' => $id,
        ]);

        return redirect()->route('produk.index')->with(
            'berhasil',
            'Produk berhasil diperbarui.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi dan data.'
        );
    }
})->whereNumber('id')->name('produk.update');

/* ===== DELETE ===== */
Route::delete('/produk/{id}', function (string $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare('DELETE FROM produk WHERE id = :id');
        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()->route('produk.index')->with(
                'gagal',
                'Produk tidak ditemukan.'
            );
        }

        return redirect()->route('produk.index')->with(
            'berhasil',
            'Produk berhasil dihapus.'
        );
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('produk.index')->with(
            'gagal',
            'Data gagal dihapus. Periksa koneksi dan data.'
        );
    }
})->whereNumber('id')->name('produk.destroy');