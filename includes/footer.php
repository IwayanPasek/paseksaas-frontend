        </div></div></div><div id="toast" style="opacity: 0; pointer-events: none; position: fixed; top: 20px; right: 20px; z-index: 9999; transition: all 0.4s ease; transform: translateX(50px);" class="flex items-start gap-3 bg-[#0d1427] border rounded-2xl p-4 shadow-2xl max-w-sm">
    <div id="toast-icon-bg" class="w-8 h-8 rounded-lg flex shrink-0 items-center justify-center text-sm">
        <i id="toast-icon" class="bi"></i>
    </div>
    <div class="flex-1 min-w-0">
        <div id="toast-title" class="text-sm font-bold text-white mb-1"></div>
        <div id="toast-msg" class="text-xs text-slate-400"></div>
    </div>
    <button class="w-6 h-6 flex shrink-0 items-center justify-center text-slate-500 hover:text-white rounded-md hover:bg-white/10 transition-colors" onclick="dismissToast()">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

<script>
    const toast = document.getElementById('toast');

    function showToast(type, title, message) {
        const iconBg = document.getElementById('toast-icon-bg');
        const icon = document.getElementById('toast-icon');
        
        document.getElementById('toast-title').textContent = title;
        document.getElementById('toast-msg').innerHTML = message;

        if (type === 'success') {
            toast.style.borderColor = 'rgba(34, 197, 94, 0.3)';
            iconBg.className = 'w-8 h-8 rounded-lg flex shrink-0 items-center justify-center text-sm bg-emerald-500/10 text-emerald-500 border border-emerald-500/20';
            icon.className = 'bi bi-check-lg';
        } else {
            toast.style.borderColor = 'rgba(244, 63, 94, 0.3)';
            iconBg.className = 'w-8 h-8 rounded-lg flex shrink-0 items-center justify-center text-sm bg-rose-500/10 text-rose-500 border border-rose-500/20';
            icon.className = 'bi bi-exclamation-lg';
        }

        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
        toast.style.pointerEvents = 'auto';

        setTimeout(dismissToast, 4500);
    }

    function dismissToast() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(50px)';
        toast.style.pointerEvents = 'none';
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        const msg    = urlParams.get('msg') || '';

        if (status === 'success') {
            showToast('success', 'Berhasil!', `Tindakan berhasil: <b>${msg}</b>`);
        } else if (status === 'error') {
            showToast('error', 'Gagal!', msg);
        } else if (status === 'deleted') {
            showToast('success', 'Dihapus!', 'Data produk berhasil dihapus.');
        }

        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function switchTab(tabId, event = null) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        if(event) {
            event.currentTarget.classList.add('active');
        } else {
            const targetBtn = document.querySelector(`button[onclick="switchTab('${tabId}', event)"]`);
            if(targetBtn) targetBtn.classList.add('active');
        }
        
        document.getElementById(tabId).classList.add('active');
        closeSidebar();
    }

    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    function openSidebar() { sidebar.classList.add('mobile-open'); overlay.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('mobile-open'); overlay.classList.remove('show'); }

    function handleFotoChange(input) {
        const previewWrap = document.getElementById('foto-preview-wrap');
        const previewImg  = document.getElementById('foto-preview-img');
        const previewName = document.getElementById('foto-preview-name');
        const previewSize = document.getElementById('foto-preview-size');
        const fileLabel   = document.getElementById('file-label');

        if (input.files && input.files[0]) {
            const file = input.files[0];

            // Validasi Maksimal 2MB via JavaScript
            if (file.size > 2 * 1024 * 1024) { 
                showToast('error', 'Terlalu Besar!', 'Ukuran foto maksimal adalah 2MB. Silakan kompres gambar Anda.');
                clearFoto(); 
                return; 
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewName.textContent = file.name;
                previewSize.textContent = formatBytes(file.size);

                previewWrap.style.display = 'block';
                fileLabel.style.display   = 'none';

                previewWrap.style.opacity = '0';
                previewWrap.style.transform = 'translateY(6px)';
                previewWrap.style.transition = 'opacity .25s ease, transform .25s ease';
                requestAnimationFrame(() => {
                    previewWrap.style.opacity = '1';
                    previewWrap.style.transform = 'translateY(0)';
                });
            };
            reader.readAsDataURL(file);
        } else {
            resetFotoUI();
        }
    }

    function clearFoto() {
        document.getElementById('foto-input').value = '';
        resetFotoUI();
    }

    function resetFotoUI() {
        document.getElementById('foto-preview-wrap').style.display = 'none';
        document.getElementById('foto-preview-img').src  = '';
        document.getElementById('file-label').style.display = '';
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    const formTambah = document.getElementById('form-tambah');
    if(formTambah) {
        formTambah.addEventListener('submit', function () {
            const btn = document.getElementById('submit-btn');
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.8';
            btn.innerHTML = `<i class="bi bi-arrow-clockwise spin"></i> Menyimpan...`;
        });
    }
</script>
</body>
</html>
