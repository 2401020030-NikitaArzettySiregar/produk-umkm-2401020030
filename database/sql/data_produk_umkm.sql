USE produk_umkm_2401020030;

INSERT INTO kategori_produk (nama_kategori) VALUES
    ('Makanan'),
    ('Minuman');

INSERT INTO produk
    (kode_produk, nama_produk, sku, stok, kategori_produk_id) VALUES
    ('PR00000001', 'Keripik Gonggong', 'SKU-MKN-001', 40, 1),
    ('PR00000002', 'Kopi Bubuk Lokal', 'SKU-MNM-001', 25, 2),
    ('PR00000003', 'Kue Bangkit',      'SKU-MKN-002', 30, 1);