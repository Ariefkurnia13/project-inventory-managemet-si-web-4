# InventoriKu — Mini Project 3

Aplikasi Inventory Management System menggunakan PHP 8+, MySQL/MariaDB, PDO, dan HTML/CSS responsif.

## Fitur yang tercakup

- **Authentication:** login, logout, `password_hash()` dan `password_verify()`.
- **Authorization:** role `admin` dan `staff`. Admin mengelola master data dan pengguna; staff dapat melihat produk dan mencatat transaksi stok.
- **Master data:** kategori, produk, supplier.
- **Many-to-many:** produk terhubung ke beberapa supplier melalui tabel `product_suppliers`.
- **Transaksi:** barang masuk dan keluar, pengecekan stok, histori transaksi, transaksi database.
- **Listing:** pencarian, filter kategori/tanggal/jenis, sorting, pagination.
- **Dashboard:** statistik produk/kategori/supplier/stok menipis, estimasi nilai persediaan, transaksi terbaru.
- **Laporan:** filter histori dan ekspor CSV.
- **Security:** PDO prepared statements, validasi input, escaping output HTML, CSRF token, session ID regeneration, pengecekan role, password hashing.

## Kebutuhan

- PHP 8.1 atau lebih baru dengan ekstensi `pdo_mysql`
- MySQL 8+ atau MariaDB
- Apache/Nginx atau PHP built-in server (untuk pengembangan)

## Cara menjalankan (XAMPP)

1. Salin folder `inventory-management-system` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL di XAMPP.
3. Buka phpMyAdmin, lalu import `schema.sql`.
4. Jika username/password database Anda berbeda, ubah `DB_USER` dan `DB_PASS` di `config.php`.
5. Login melalui `http://localhost/inventory-management-system/login.php`.

Akun :
- Admin: `admin` / `Admin123!`
- Staff: `staff` / `Staff123!`


## Cara menjalankan dengan PHP built-in server

Setelah database diimpor dan konfigurasi disesuaikan:

```bash
php -S 127.0.0.1:8000
```

Lalu buka `http://127.0.0.1:8000/login.php`.

## Catatan perilaku

- Stok awal ketika produk baru dibuat akan dicatat sebagai transaksi masuk.
- Ketika produk diedit, jumlah stok tidak diedit langsung; gunakan menu **Transaksi stok** agar histori stok tetap konsisten.
- Transaksi keluar ditolak jika stok tidak mencukupi.
- Staff tidak dapat membuat/mengubah master data atau mengelola pengguna.
- Histori transaksi disimpan untuk audit; produk yang memiliki histori transaksi tidak dapat dihapus karena foreign key.
- Untuk produksi, tambahkan HTTPS, rate limiting login, backup database, kebijakan pengelolaan password, serta audit keamanan sesuai kebutuhan.

## Struktur folder

```text
inventory-management-system/
├── assets/style.css
├── config.php
├── schema.sql
├── setup.php
├── login.php
├── logout.php
├── index.php
├── products.php
├── categories.php
├── suppliers.php
├── transactions.php
├── history.php
├── users.php
├── partials_header.php
├── partials_footer.php
└── README.md
```
