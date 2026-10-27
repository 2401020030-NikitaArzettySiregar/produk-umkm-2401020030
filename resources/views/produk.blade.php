<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk UMKM</title>
</head>
<body>
    <h1>Data Produk UMKM</h1>

    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif

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

    <h2>Tambah Produk</h2>

    <form action="{{ route('produk.store') }}" method="POST">
        @csrf

        <p>Kode Produk<br>
            <input type="text" name="kode_produk" value="{{ old('kode_produk') }}"></p>
        <p>Nama Produk<br>
            <input type="text" name="nama_produk" value="{{ old('nama_produk') }}"></p>
        <p>SKU<br>
            <input type="text" name="sku" value="{{ old('sku') }}"></p>
        <p>Stok<br>
            <input type="number" name="stok" value="{{ old('stok') }}"></p>

        <p>Kategori<br>
            <select name="kategori_produk_id">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($daftarKategori as $kategori)
                    <option value="{{ $kategori['id'] }}"
                        @selected(old('kategori_produk_id') == $kategori['id'])>
                        {{ $kategori['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Tambah Produk</button>
    </form>

    <h2>Pencarian dan Filter</h2>

    <form action="{{ route('produk.index') }}" method="GET">
        <p>Kata kunci (kode, nama, atau SKU)<br>
            <input type="text" name="q" value="{{ $kataKunci }}"></p>

        <p>Kategori<br>
            <select name="kategori">
                <option value="">-- Semua Kategori --</option>
                @foreach ($daftarKategori as $kategori)
                    <option value="{{ $kategori['id'] }}"
                        @selected($filterKategori === (string) $kategori['id'])>
                        {{ $kategori['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Cari</button>
        <a href="{{ route('produk.index') }}">Reset</a>
    </form>

    <h2>Daftar Produk</h2>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Kode</th><th>Nama Produk</th><th>SKU</th>
            <th>Stok</th><th>Status</th><th>Kategori</th><th>Aksi</th>
        </tr>
        @forelse ($daftarProduk as $produk)
            <tr>
                <td>{{ $produk['kode_produk'] }}</td>
                <td>{{ $produk['nama_produk'] }}</td>
                <td>{{ $produk['sku'] }}</td>
                <td>{{ $produk['stok'] }}</td>
                <td>
                    @if (statusStok((int) $produk['stok']) === 'Tersedia')
                        Tersedia
                    @else
                        Habis
                    @endif
                </td>
                <td>{{ $produk['nama_kategori'] }}</td>
                <td>
                    <a href="{{ route('produk.edit', $produk['id']) }}">Edit</a>
                    <form action="{{ route('produk.destroy', $produk['id']) }}"
                          method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Data tidak ditemukan.</td></tr>
        @endforelse
    </table>
</body>
</html>