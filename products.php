<?php
require_once __DIR__ . '/config.php'; require_login(); $pdo=db();
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 if (current_user()['role'] !== 'admin') { http_response_code(403); exit('Hanya admin yang dapat mengubah data master produk.'); }
 $id=(int)($_POST['id']??0); $sku=trim((string)($_POST['sku']??'')); $name=trim((string)($_POST['name']??''));
 $category=(int)($_POST['category_id']??0); $qty=max(0,(int)($_POST['quantity']??0)); $min=max(0,(int)($_POST['min_stock']??5)); $unit=trim((string)($_POST['unit']??'pcs'));
 $price=(float)($_POST['price']??0); $description=trim((string)($_POST['description']??'')); $supplierIds=array_values(array_unique(array_filter(array_map('intval', $_POST['supplier_ids']??[]))));
 if ($sku===''||$name===''||$price<0||$unit==='') flash('SKU, nama, satuan wajib diisi; harga tidak boleh negatif.','error');
 else try {
  $pdo->beginTransaction();
  if ($id) {
   $s=$pdo->prepare('UPDATE products SET sku=?,name=?,category_id=?,quantity=?,min_stock=?,unit=?,price=?,description=? WHERE id=?');
   $s->execute([$sku,$name,$category?:null,$qty,$min,$unit,$price,$description?:null,$id]);
  } else {
   $s=$pdo->prepare('INSERT INTO products (sku,name,category_id,quantity,min_stock,unit,price,description) VALUES (?,?,?,?,?,?,?,?)');
   $s->execute([$sku,$name,$category?:null,$qty,$min,$unit,$price,$description?:null]); $id=(int)$pdo->lastInsertId();
   if ($qty>0) { $t=$pdo->prepare("INSERT INTO stock_transactions(product_id,user_id,type,quantity,note) VALUES(?,?,'in',?,'Stok awal saat produk dibuat')"); $t->execute([$id,current_user()['id'],$qty]); }
  }
  $pdo->prepare('DELETE FROM product_suppliers WHERE product_id=?')->execute([$id]);
  $s=$pdo->prepare('INSERT INTO product_suppliers(product_id,supplier_id) VALUES(?,?)');
  foreach($supplierIds as $sid) $s->execute([$id,$sid]);
  $pdo->commit(); flash('Data produk berhasil disimpan.');
 } catch(Throwable $ex) { if($pdo->inTransaction())$pdo->rollBack(); flash('Gagal menyimpan. Pastikan SKU unik dan data valid.','error'); }
 redirect('products.php');
}
if (isset($_GET['delete'])) {
 require_admin();
 $id=(int)$_GET['delete'];
 try { $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]); flash('Produk dihapus.'); }
 catch(Throwable $ex) { flash('Produk tidak dapat dihapus karena sudah memiliki histori transaksi.','error'); }
 redirect('products.php');
}
$edit=null;
if(isset($_GET['edit'])) { $s=$pdo->prepare('SELECT * FROM products WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch()?:null; }
$categories=$pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll(); $suppliers=$pdo->query('SELECT id,name FROM suppliers ORDER BY name')->fetchAll();
$selected=[]; if($edit){$s=$pdo->prepare('SELECT supplier_id FROM product_suppliers WHERE product_id=?');$s->execute([$edit['id']]);$selected=array_column($s->fetchAll(),'supplier_id');}
$q=trim((string)($_GET['q']??'')); $cat=(int)($_GET['category']??0); $sort=(string)($_GET['sort']??'name'); $dir=strtolower((string)($_GET['dir']??'asc'))==='desc'?'DESC':'ASC';
$allowed=['name'=>'p.name','sku'=>'p.sku','quantity'=>'p.quantity','price'=>'p.price','category'=>'c.name']; $order=$allowed[$sort]??'p.name'; $where=[];$params=[];
if($q!==''){$where[]='(p.name LIKE ? OR p.sku LIKE ?)';$params[]="%$q%";$params[]="%$q%";}
if($cat){$where[]='p.category_id=?';$params[]=$cat;}
$clause=$where?' WHERE '.implode(' AND ',$where):''; $s=$pdo->prepare("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id=p.category_id$clause");$s->execute($params);$total=(int)$s->fetchColumn();
$per=10;$pages=max(1,(int)ceil($total/$per));$page=max(1,min($pages,(int)($_GET['page']??1)));$offset=($page-1)*$per;
$s=$pdo->prepare("SELECT p.*,c.name category_name,(SELECT GROUP_CONCAT(su.name SEPARATOR ', ') FROM product_suppliers ps JOIN suppliers su ON su.id=ps.supplier_id WHERE ps.product_id=p.id) supplier_names FROM products p LEFT JOIN categories c ON c.id=p.category_id$clause ORDER BY $order $dir LIMIT $per OFFSET $offset");$s->execute($params);$rows=$s->fetchAll();
$pageTitle='Produk';require 'partials_header.php';
?>
<?php if(current_user()['role']==='admin'): ?><section class="panel"><div class="panel-heading"><h2><?= $edit?'Edit produk':'Tambah produk' ?></h2></div><form method="post" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<div><label>SKU / Kode produk *</label><input name="sku" maxlength="50" required value="<?= e($edit['sku']??'') ?>"></div><div><label>Nama produk *</label><input name="name" maxlength="150" required value="<?= e($edit['name']??'') ?>"></div>
<div><label>Kategori</label><select name="category_id"><option value="0">-- Tanpa kategori --</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= (int)($edit['category_id']??0)===$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
<div><label>Stok awal / jumlah saat ini</label><input type="number" min="0" name="quantity" value="<?= (int)($edit['quantity']??0) ?>" required <?= $edit?'disabled':'' ?>></div>
<div><label>Stok minimum</label><input type="number" min="0" name="min_stock" value="<?= (int)($edit['min_stock']??5) ?>" required></div><div><label>Satuan</label><input name="unit" maxlength="30" value="<?= e($edit['unit']??'pcs') ?>" required></div>
<div><label>Harga satuan (Rp)</label><input type="number" min="0" step="0.01" name="price" value="<?= e((string)($edit['price']??0)) ?>" required></div>
<div><label>Supplier (bisa pilih lebih dari satu)</label><select name="supplier_ids[]" multiple size="3"><?php foreach($suppliers as $su): ?><option value="<?= $su['id'] ?>" <?= in_array($su['id'],$selected)?'selected':'' ?>><?= e($su['name']) ?></option><?php endforeach; ?></select><small>Gunakan Ctrl/Command untuk memilih beberapa supplier.</small></div>
<div class="wide"><label>Deskripsi</label><textarea name="description" rows="2"><?= e($edit['description']??'') ?></textarea></div>
<div class="wide form-actions"><button class="btn primary">Simpan produk</button><?php if($edit): ?><a class="btn" href="products.php">Batal edit</a><?php endif; ?></div></form></section><?php endif; ?>
<section class="panel"><div class="panel-heading"><h2>Daftar produk <span class="muted">(<?= $total ?>)</span></h2></div>
<form method="get" class="filters"><input name="q" placeholder="Cari nama atau SKU..." value="<?= e($q) ?>"><select name="category"><option value="0">Semua kategori</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $cat===$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><select name="sort"><?php foreach(['name'=>'Nama','sku'=>'SKU','quantity'=>'Stok','price'=>'Harga','category'=>'Kategori'] as $k=>$v): ?><option value="<?= $k ?>" <?= $sort===$k?'selected':'' ?>>Urut: <?= $v ?></option><?php endforeach; ?></select><select name="dir"><option value="asc" <?= $dir==='ASC'?'selected':'' ?>>Naik</option><option value="desc" <?= $dir==='DESC'?'selected':'' ?>>Turun</option></select><button class="btn">Terapkan</button><a class="btn" href="products.php">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>Produk</th><th>Kategori</th><th>Supplier</th><th>Stok</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?= e($r['name']) ?></strong><small><?= e($r['sku']) ?></small></td><td><?= e($r['category_name']??'-') ?></td><td><?= e($r['supplier_names']??'-') ?></td><td><?= (int)$r['quantity'] ?> <?= e($r['unit']) ?></td><td>Rp <?= number_format((float)$r['price'],0,',','.') ?></td><td><span class="badge <?= $r['quantity'] <= $r['min_stock']?'low':'ok' ?>"><?= $r['quantity'] <= $r['min_stock']?'Stok menipis':'Aman' ?></span></td><td class="actions"><?php if(current_user()['role']==='admin'): ?><a href="?edit=<?= $r['id'] ?>">Edit</a><a class="danger-link" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Hapus produk ini?')">Hapus</a><?php else: ?><span class="muted">Lihat</span><?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="7" class="empty">Data produk tidak ditemukan.</td></tr><?php endif; ?></tbody></table></div>
<div class="pagination"><span>Halaman <?= $page ?> dari <?= $pages ?></span><div><?php if($page>1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">← Sebelumnya</a><?php endif; ?> <?php if($page<$pages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">Berikutnya →</a><?php endif; ?></div></div>
</section><?php require 'partials_footer.php'; ?>
