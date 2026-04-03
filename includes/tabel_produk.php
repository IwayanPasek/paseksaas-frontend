<div class="panel" id="section-produk">
    <div class="panel-head">
        <div class="panel-title"><i class="bi bi-grid-3x3-gap-fill"></i> Inventaris Website</div>
        <span class="panel-badge"><?= count($list_produk) ?> Produk</span>
    </div>
    <?php if (empty($list_produk)): ?>
        <div class="empty-state">
            <i class="bi bi-box-seam"></i>
            <p>Belum ada produk. Tambahkan produk pertama Anda!</p>
        </div>
    <?php else: ?>
    <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Deskripsi</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list_produk as $p): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:.85rem">
                            <div class="prod-thumb">
                                <img src="assets/img/produk/<?= htmlspecialchars($p['foto_produk']) ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" onerror="this.parentElement.innerHTML='<div style=width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;opacity:.25>📦</div>'">
                            </div>
                            <div>
                                <div class="prod-name-cell"><?= htmlspecialchars($p['nama_produk']) ?></div>
                                <div class="prod-id-cell">#<?= $p['id_produk'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td><div class="price-cell"><small>Rp</small><?= number_format($p['harga'], 0, ',', '.') ?></div></td>
                    <td style="max-width:240px"><div style="font-size:.78rem;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.5"><?= htmlspecialchars($p['deskripsi'] ?? '—') ?></div></td>
                    <td style="text-align:center">
                        <a href="admin.php?hapus=<?= $p['id_produk'] ?>" onclick="return confirm('Hapus produk \'<?= htmlspecialchars(addslashes($p['nama_produk'])) ?>\'?')" class="action-btn action-delete"><i class="bi bi-trash3"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
