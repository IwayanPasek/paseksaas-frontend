<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="bi bi-plus-circle-fill"></i> Tambah Produk</div>
    </div>
    <div class="panel-body">
        <form action="admin.php" method="POST" enctype="multipart/form-data" id="form-tambah">
            
            <input type="hidden" name="tambah_produk" value="1">

            <div class="form-group">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-input" placeholder="cth: Laptop Gaming" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Harga (IDR)</label>
                <input type="number" name="harga" class="form-input" placeholder="cth: 15000000" min="0" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Foto Produk</label>
                <div id="foto-preview-wrap" style="display:none;margin-bottom:.65rem;position:relative">
                    <div style="width:100%;aspect-ratio:16/9;border-radius:12px;overflow:hidden;background:var(--surface-2);border:1.5px solid var(--border);position:relative;">
                        <img id="foto-preview-img" src="" alt="Preview" style="width:100%;height:100%;object-fit:cover;display:block">
                        <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top,rgba(8,13,26,.85),transparent);padding:.5rem .75rem;display:flex;align-items:center;justify-content:space-between;">
                            <span id="foto-preview-name" style="font-size:.68rem;color:#e2eaf5;font-family:'DM Mono',monospace;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;"></span>
                            <span id="foto-preview-size" style="font-size:.65rem;color:var(--amber);font-family:'DM Mono',monospace;flex-shrink:0;"></span>
                        </div>
                    </div>
                    <div style="position:absolute;top:-8px;right:-8px;background:var(--green);color:#fff;font-size:.6rem;font-weight:700;letter-spacing:.05em;padding:.2rem .55rem;border-radius:100px;display:flex;align-items:center;gap:.3rem;box-shadow:0 2px 10px rgba(34,197,94,.3);border:2px solid var(--surface);"><i class="bi bi-check-circle-fill"></i> Terdeteksi</div>
                    <button type="button" onclick="clearFoto()" style="position:absolute;top:-8px;left:-8px;width:22px;height:22px;border-radius:50%;background:var(--red);color:#fff;border:2px solid var(--surface);font-size:.65rem;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(244,63,94,.3);transition:transform .15s;" title="Hapus pilihan foto"><i class="bi bi-x"></i></button>
                </div>
                <label class="file-label" id="file-label" for="foto-input">
                    <i class="bi bi-cloud-arrow-up" id="file-icon"></i>
                    <div style="flex:1;min-width:0">
                        <div id="file-name-display" style="font-size:.82rem;font-weight:500">Pilih atau seret gambar...</div>
                        <div style="font-size:.67rem;opacity:.6;margin-top:.1rem">JPG, PNG, WEBP · Maks 2MB</div>
                    </div>
                </label>
                <input type="file" name="foto" id="foto-input" accept="image/*" onchange="handleFotoChange(this)">
            </div>
            
            <div class="form-group">
                <label class="form-label">Deskripsi & AI Knowledge <span style="color:var(--amber);margin-left:.25rem">✦</span></label>
                <textarea name="deskripsi" class="form-input" placeholder="Tulis detail produk untuk konteks AI..." rows="5" required></textarea>
            </div>
            
            <button type="submit" class="btn-submit" id="submit-btn"><i class="bi bi-cloud-upload"></i> Simpan Produk</button>
        </form>
    </div>
</div>
