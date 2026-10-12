<?php require_once __DIR__ . '/config.php'; require_login(); $u = current_user(); $pageTitle = $pageTitle ?? 'Dashboard'; ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($pageTitle) ?> - InventoriKu</title><link rel="stylesheet" href="assets/style.css"></head><body>
<aside class="sidebar"><a class="brand" href="index.php">▦ InventoriKu</a><nav>
<a href="index.php">Dashboard</a><a href="products.php">Produk</a><a href="categories.php">Kategori</a><a href="suppliers.php">Supplier</a><a href="transactions.php">Transaksi stok</a><a href="history.php">Histori</a>
<?php if ($u['role'] === 'admin'): ?><a href="users.php">Manajemen pengguna</a><?php endif; ?>
</nav><div class="side-user"><strong><?= e($u['name']) ?></strong><span><?= e(strtoupper($u['role'])) ?></span><a href="logout.php">Keluar ↗</a></div></aside>
<main class="main"><header class="topbar"><div><span class="muted">Sistem Inventori</span><h1><?= e($pageTitle) ?></h1></div><div class="user-pill"><?= e($u['name']) ?> · <?= e($u['role']) ?></div></header>
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?><div class="alert <?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endif; ?>
