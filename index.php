<?php
require_once __DIR__ . '/config.php'; require_login();
$pdo = db();
$stats = [
 'products' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
 'categories' => (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
 'suppliers' => (int)$pdo->query('SELECT COUNT(*) FROM suppliers')->fetchColumn(),
 'low' => (int)$pdo->query('SELECT COUNT(*) FROM products WHERE quantity <= min_stock')->fetchColumn(),
 'value' => (float)$pdo->query('SELECT COALESCE(SUM(quantity * price),0) FROM products')->fetchColumn()
];
$recent = $pdo->query("SELECT t.*, p.name product_name, p.sku, u.name user_name FROM stock_transactions t JOIN products p ON p.id=t.product_id JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT 8")->fetchAll();
$pageTitle = 'Dashboard'; require 'partials_header.php';
?>
<section class="stats">
<div class="stat-card"><span>Total produk</span><strong><?= $stats['products'] ?></strong><small>SKU terdaftar</small></div>
<div class="stat-card"><span>Kategori</span><strong><?= $stats['categories'] ?></strong><small>Kategori aktif</small></div>
<div class="stat-card"><span>Supplier</span><strong><?= $stats['suppliers'] ?></strong><small>Mitra pemasok</small></div>
<div class="stat-card <?= $stats['low'] ? 'warning-card' : '' ?>"><span>Stok menipis</span><strong><?= $stats['low'] ?></strong><small>Stok ≤ batas minimum</small></div>
</section>
<div class="panel value-panel"><div><span class="muted">Estimasi nilai persediaan</span><h2>Rp <?= number_format($stats['value'], 0, ',', '.') ?></h2></div><a class="btn primary" href="transactions.php">+ Catat transaksi</a></div>
<section class="panel"><div class="panel-heading"><h2>Transaksi terbaru</h2><a href="history.php">Lihat semua →</a></div>
<?php if (!$recent): ?><p class="empty">Belum ada transaksi. Mulai dengan mencatat barang masuk atau keluar.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Petugas</th><th>Catatan</th></tr></thead><tbody>
<?php foreach ($recent as $r): ?><tr><td><?= e($r['created_at']) ?></td><td><strong><?= e($r['product_name']) ?></strong><small><?= e($r['sku']) ?></small></td><td><span class="badge <?= e($r['type']) ?>"><?= e($r['type']) ?></span></td><td><?= (int)$r['quantity'] ?></td><td><?= e($r['user_name']) ?></td><td><?= e($r['note']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<section class="panel"><div class="panel-heading"><h2>Akses cepat</h2></div><div class="quick-links"><a href="products.php">＋ Tambah / kelola produk</a><a href="categories.php">＋ Kelola kategori</a><a href="suppliers.php">＋ Kelola supplier</a><a href="history.php">▤ Laporan histori</a></div></section>
<?php require 'partials_footer.php'; ?>
