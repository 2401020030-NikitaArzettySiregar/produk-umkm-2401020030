<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
</head>
<body>
    <h1>Edit Produk</h1>

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('produk.update', $produk['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>Kode Produk<br>
            <input type="text" name="kode_produk"
                   value="{{ old('kode_produk', $produk['kode_produk']) }}"></p>
        <p>Nama Produk<br>
            <input type="text" name="nama_produk"
                   value="{{ old('nama_produk', $produk['nama_produk']) }}"></p>
        <p>SKU<br>
            <input type="text" name="sku"
                   value="{{ old('sku', $produk['sku']) }}"></p>
        <p>Stok<br>
            <input type="number" name="stok"
                   value="{{ old('stok', $produk['stok']) }}"></p>

        <p>Kategori<br>
            <select name="kategori_produk_id">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($daftarKategori as $kategori)
                    <option value="{{ $kategori['id'] }}"
                        @selected(old('kategori_produk_id', $produk['kategori_produk_id']) == $kategori['id'])>
                        {{ $kategori['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('produk.index') }}">Batal</a>
    </form>
</body>
</html>