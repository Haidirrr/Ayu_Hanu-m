ELIZABETH STORE — PREMIUM EDITION

Cara menjalankan di XAMPP:
1. Ekstrak folder Elizabeth-Store-Premium ke C:\xampp\htdocs\
2. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
3. Buka http://localhost/phpmyadmin
4. Import file database.sql. File ini membuat database elizabeth_db dan tabel-tabelnya.
5. Buka http://localhost/Elizabeth-Store-Premium/

Jika nama folder di htdocs berbeda, sesuaikan URL-nya.
Pengaturan koneksi database ada di config.php (default XAMPP: root tanpa password).

Struktur utama:
- includes/functions.php : helper, session, keranjang, format harga
- includes/header.php dan footer.php : layout bersama
- assets/style.css : desain responsif premium
- api.php : endpoint tambah produk ke keranjang via fetch
- database.sql : skema dan data awal toko

Catatan: gambar produk akan memakai kolom products.image jika diisi dengan path relatif seperti assets/img/nama-gambar.jpg.
