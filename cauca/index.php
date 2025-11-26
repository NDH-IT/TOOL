<?php
// /tool/cauca/index.php
$pageTitle = 'Câu cá random | NDH IT Tools';
require_once __DIR__ . '/../layout/header.php';
?>

<style>
    .card {
        background:
            radial-gradient(circle at top left, rgba(79, 70, 229, 0.25), transparent 55%),
            radial-gradient(circle at bottom right, rgba(8, 47, 73, 0.5), transparent 60%),
            var(--card-bg);
        border-radius: 18px;
        padding: 22px 20px 20px;
        border: 1px solid var(--card-border);
        box-shadow:
            0 18px 45px rgba(15, 23, 42, 0.95),
            0 0 0 1px rgba(15, 23, 42, 0.9);
        max-width: 900px;
        margin: 0 auto;
    }

    .card-header { margin-bottom: 16px; }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(22, 163, 74, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.5);
        color: #bbf7d0;
    }

    .card-desc {
        margin-top: 4px;
        font-size: 13px;
        color: var(--muted);
    }

    .fish-tool {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 6px;
    }

    .field-label {
        font-size: 13px;
        margin-bottom: 6px;
        color: var(--muted);
    }

    .input-area {
        width: 100%;
        padding: 9px 11px;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.7);
        background: rgba(15, 23, 42, 0.8);
        color: var(--text);
        font-size: 13px;
        resize: vertical;
        min-height: 120px;
    }

    .input-area:focus {
        outline: none;
        border-color: rgba(129, 140, 248, 0.95);
    }

    .helper-text {
        font-size: 11px;
        margin-top: 4px;
        color: var(--muted);
    }

    .btn-row {
        margin-top: 10px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        border-radius: 999px;
        padding: 7px 16px;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: transform 0.08s ease, box-shadow 0.08s ease, opacity 0.15s ease;
    }

    .btn-primary {
        background: linear-gradient(to right, #4f46e5, #6366f1);
        color: #f9fafb;
        box-shadow: 0 0 0 1px rgba(129, 140, 248, 0.7), 0 12px 28px rgba(79, 70, 229, 0.6);
    }

    .btn-outline {
        background: transparent;
        color: var(--muted);
        border: 1px solid rgba(148, 163, 184, 0.5);
    }

    .btn-soft {
        background: rgba(15, 23, 42, 0.9);
        color: #e5e7eb;
        border: 1px solid rgba(148, 163, 184, 0.5);
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        cursor: pointer;
    }

    .btn-soft.primary {
        background: rgba(22, 163, 74, 0.1);
        border-color: rgba(34, 197, 94, 0.8);
        color: #bbf7d0;
    }

    .btn:hover { transform: translateY(-1px); opacity: 0.96; }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .msg {
        margin-top: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 12px;
    }

    .msg-error {
        background: rgba(127, 29, 29, 0.35);
        border: 1px solid rgba(248, 113, 113, 0.7);
        color: #fca5a5;
    }

    .msg-info {
        background: rgba(30, 64, 175, 0.35);
        border: 1px solid rgba(59, 130, 246, 0.7);
        color: #bfdbfe;
    }

    .fisher-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
    }

    .fisher-img-wrap {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fisher-img-wrap img {
        max-width: 100%;
        height: auto;
        display: block;
    }

    .catch-result {
        min-height: 28px;
        padding: 4px 10px;
        border-radius: 999px;
        border: 1px dashed rgba(148, 163, 184, 0.6);
        font-size: 13px;
        color: var(--muted);
        text-align: center;
        max-width: 260px;
    }

    .catch-result.has-value {
        border-style: solid;
        border-color: rgba(34, 197, 94, 0.8);
        color: #bbf7d0;
        background: rgba(22, 101, 52, 0.35);
    }

    /* Hồ cá */
    .pond-block { margin-top: 10px; }

    .pond {
        border-radius: 16px;
        padding: 10px 8px 12px;
        background:
            radial-gradient(circle at top, rgba(59, 130, 246, 0.35), transparent 60%),
            #0f172a;
        border: 1px solid rgba(56, 189, 248, 0.7);
        box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.9);
    }

    .pond-title {
        font-size: 13px;
        color: #bae6fd;
        display: flex;
        justify-content: space-between;
        gap: 6px;
    }

    .pond-water {
        margin-top: 8px;
        border-radius: 12px;
        background: #38bdf8; /* 1 màu xanh da trời */
        overflow: hidden;
        padding: 6px 4px 8px;
    }

    .pond-lane {
        position: relative;
        height: 52px;
        margin-bottom: 4px;
    }

    .pond-lane:last-child { margin-bottom: 0; }

    .pond-lane.top-empty {}
    .pond-lane-row {}

    /* Cá trong hồ: tự bơi qua lại & quay mặt theo hướng bơi */
    .fish {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-width: 80px;
        padding: 2px 6px;
        left: 0;
        animation: swim-horizontal linear infinite;
    }

    .fish img {
        width: 96px;
        height: auto;
        animation: swim-face linear infinite;
    }

    .fish-label {
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.85);
        color: #e5e7eb;
        border: 1px solid rgba(148, 163, 184, 0.7);
        white-space: nowrap;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .fish-color-0 .fish-label { border-color: #fb923c; }
    .fish-color-1 .fish-label { border-color: #4ade80; }
    .fish-color-2 .fish-label { border-color: #60a5fa; }
    .fish-color-3 .fish-label { border-color: #a855f7; }
    .fish-color-4 .fish-label { border-color: #f973c5; }
    .fish-color-5 .fish-label { border-color: #facc15; }

    /* Giữ class cho JS nếu còn xài, nhưng không cần style thêm */
    .fish.dir-right,
    .fish.dir-left { }

    /* Bơi trái -> phải -> trái trong hồ */
    @keyframes swim-horizontal {
        0%    { left: 0%; }
        49.9% { left: 75%; }
        50%   { left: 75%; }
        100%  { left: 0%; }
    }

    /* Mặt cá quay theo hướng bơi */
    @keyframes swim-face {
        0%, 49.9% {
            transform: scaleX(1);   /* nhìn sang phải */
        }
        50%, 100% {
            transform: scaleX(-1);  /* quay lại nhìn sang trái */
        }
    }

    .fish-hidden {
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .fish-caught-fly {
        position: fixed;
        z-index: 9999;
        pointer-events: none;
    }

    .fish-caught-fly img {
        width: 40px; /* cá bay lên, muốn to hơn thì chỉnh ở đây */
        height: auto;
    }

    /* MODAL KẾT QUẢ */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 50;
    }

    .modal-dialog {
        background:
            radial-gradient(circle at top left, rgba(79, 70, 229, 0.28), transparent 55%),
            #020617;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.6);
        box-shadow:
            0 22px 50px rgba(15, 23, 42, 0.9),
            0 0 0 1px rgba(15, 23, 42, 0.9);
        max-width: 360px;
        width: 88%;
        padding: 18px 18px 16px;
        color: #e5e7eb;
        text-align: center;
        transform: translateY(8px);
        opacity: 0;
    }

    .modal-title {
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #a5b4fc;
        margin-bottom: 6px;
    }

    .modal-value {
        font-size: 20px;
        font-weight: 600;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(34, 197, 94, 0.7);
        box-shadow: 0 0 0 1px rgba(22, 163, 74, 0.5);
        margin-bottom: 8px;
        word-break: break-word;
    }

    .modal-sub {
        font-size: 11px;
        color: #9ca3af;
        margin-bottom: 10px;
    }

    .modal-actions {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    @media (max-width: 600px) {
        .card { padding: 18px 14px 16px; }
    }
</style>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Câu cá random
            <span class="card-badge">Mini game· Câu cá ngẫu nhiên</span>
        </div>
        <div class="card-desc">
            Nhập nhiều dòng (mỗi dòng 1 kết quả) &rarr; Thả cá xuống hồ &rarr; Bấm <strong>Câu cá</strong>.
            Khi câu, 1 con cá trong hồ sẽ bơi từ từ (5s) lên người câu cá, đến nơi mới hiện cửa sổ thông báo kết quả.
        </div>
    </div>

    <div class="fish-tool">
        <!-- 1. Khung nhập -->
        <div>
            <div class="field-label">Danh sách cá (mỗi dòng một kết quả)</div>
            <textarea id="fishInput" class="input-area" placeholder="Ví dụ:
Huỳnh đẹp trai
Huỳnh đại ca
Huỳnh đẳng cấp"></textarea>
            <div class="helper-text">
                Mỗi dòng tương ứng với 1 con cá / 1 kết quả. Thêm cá xong mới bấm <strong>Câu cá</strong>.
            </div>
        </div>

        <!-- 2. Nút Thêm cá & Câu cá -->
        <div>
            <div class="btn-row">
                <button type="button" class="btn btn-outline" id="btnAddFish">
                    ➕ Thêm cá xuống hồ
                </button>
            </div>
            <div id="messageBox" class="msg" style="display:none;"></div>
            <div class="helper-text">
                Chống spam: mỗi lần <strong>Câu cá</strong> cách nhau tối thiểu <strong>10 giây</strong>.
            </div>
        </div>

        <!-- 3. Người câu cá -->
        <div class="fisher-block">
            <div class="fisher-img-wrap" id="fisherArea">
                <img src="anh/cauthu.gif" alt="Người câu cá">
            </div>
                <button type="button" class="btn btn-primary" id="btnCatchFish">
                    🎣 Câu cá
                </button>
            <div id="catchResult" class="catch-result">
                Chưa câu được con nào...
            </div>
        </div>

        <!-- 4. Hồ cá -->
        <div class="pond-block">
            <div class="pond">
                <div class="pond-title">
                    <span>Hồ cá</span>
                    <span id="pondCounter" style="font-size:11px;">0 con cá</span>
                </div>
                <div class="pond-water" id="pondWater">
                    <div class="pond-lane top-empty"></div>
                    <div class="pond-lane pond-lane-row" data-row="0"></div>
                    <div class="pond-lane pond-lane-row" data-row="1"></div>
                    <div class="pond-lane pond-lane-row" data-row="2"></div>
                    <div class="pond-lane pond-lane-row" data-row="3"></div>
                    <div class="pond-lane pond-lane-row" data-row="4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KẾT QUẢ -->
<div class="modal-overlay" id="resultModal">
    <div class="modal-dialog" id="resultModalDialog">
        <div class="modal-title">ĐÃ CÂU TRÚNG</div>
        <div class="modal-value" id="resultModalText">...</div>
        <div class="modal-sub">Chọn cách xử lý kết quả rồi tiếp tục câu cá.</div>
        <div class="modal-actions">
            <button type="button" class="btn-soft" id="btnClearResultText">
                🧹 Xoá kết quả
            </button>
            <button type="button" class="btn-soft primary" id="btnKeepResultText">
                ✅ Giữ kết quả
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const inputEl      = document.getElementById('fishInput');
    const btnAddFish   = document.getElementById('btnAddFish');
    const btnCatchFish = document.getElementById('btnCatchFish');
    const messageBox   = document.getElementById('messageBox');
    const pondCounter  = document.getElementById('pondCounter');
    const pondWater    = document.getElementById('pondWater');
    const catchResult  = document.getElementById('catchResult');
    const fisherArea   = document.getElementById('fisherArea');

    const modal        = document.getElementById('resultModal');
    const modalDialog  = document.getElementById('resultModalDialog');
    const modalText    = document.getElementById('resultModalText');
    const btnClearRT   = document.getElementById('btnClearResultText');
    const btnKeepRT    = document.getElementById('btnKeepResultText');

    let fishPool = []; // { text }
    const COOLDOWN_SECONDS = 10;
    let lastCatchTime = 0;

    // lưu con cá vừa trúng để xóa khỏi hồ nếu cần
    let lastWinnerIndex = null;
    let lastWinnerText  = '';

    function showMessage(text, type) {
        messageBox.textContent = text;
        messageBox.className = 'msg ' + (type === 'error' ? 'msg-error' : 'msg-info');
        messageBox.style.display = 'block';
    }

    function clearMessage() {
        messageBox.style.display = 'none';
    }

    function updatePondCounter() {
        pondCounter.textContent = fishPool.length + ' con cá';
    }

    function renderPond() {
        const rows = pondWater.querySelectorAll('.pond-lane-row');
        rows.forEach(row => {
            row.innerHTML = '';
        });

        fishPool.forEach((fish, idx) => {
            const rowIndex = idx % 5;
            const rowEl = pondWater.querySelector('.pond-lane-row[data-row="' + rowIndex + '"]');
            if (!rowEl) return;

            const colorIndex = idx % 6;

            const fishDiv = document.createElement('div');
            fishDiv.className = 'fish fish-color-' + colorIndex;
            fishDiv.dataset.index = String(idx);

            // tốc độ bơi & lệch pha cho mỗi con cá
            const duration = 7 + Math.random() * 6; // 7–13s
            const delay    = Math.random() * duration;
            fishDiv.style.animationDuration = duration.toFixed(1) + 's';
            fishDiv.style.animationDelay    = (-delay).toFixed(1) + 's';

            const img = document.createElement('img');
            img.src = 'anh/cc.gif';
            img.alt = 'Cá';
            img.style.animationDuration = fishDiv.style.animationDuration;
            img.style.animationDelay    = fishDiv.style.animationDelay;

            const label = document.createElement('div');
            label.className = 'fish-label';
            label.textContent = fish.text;

            fishDiv.appendChild(img);
            fishDiv.appendChild(label);
            rowEl.appendChild(fishDiv);
        });

        updatePondCounter();
    }

    function openResultModal(text) {
        if (!modal || !modalDialog || !modalText) return;
        modalText.textContent = text;
        modal.style.display = 'flex';
        modalDialog.style.opacity = '0';
        modalDialog.style.transform = 'translateY(8px)';

        requestAnimationFrame(() => {
            modalDialog.style.transition = 'opacity 0.3s ease, transform 0.25s ease';
            modalDialog.style.opacity = '1';
            modalDialog.style.transform = 'translateY(0)';
        });
    }

    function closeResultModal() {
        if (!modal || !modalDialog) return;
        modal.style.display = 'none';
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                // click ra ngoài = giữ kết quả, chỉ đóng modal
                closeResultModal();
            }
        });
    }

    if (btnClearRT) {
        btnClearRT.addEventListener('click', function () {
            // Xoá con cá đã trúng khỏi hồ + xoá text kết quả
            if (lastWinnerIndex !== null && lastWinnerIndex >= 0 && lastWinnerIndex < fishPool.length) {
                fishPool.splice(lastWinnerIndex, 1);
                renderPond();
            }
            lastWinnerIndex = null;
            lastWinnerText  = '';

            catchResult.textContent = 'Chưa câu được con nào...';
            catchResult.classList.remove('has-value');

            closeResultModal();
        });
    }

    if (btnKeepRT) {
        btnKeepRT.addEventListener('click', function () {
            // giữ nguyên kết quả + cá trong hồ
            closeResultModal();
        });
    }

    btnAddFish.addEventListener('click', () => {
        clearMessage();
        const raw = inputEl.value.trim();
        if (!raw) {
            showMessage('Chưa nhập danh sách cá. Mỗi dòng là 1 con cá / 1 kết quả.', 'error');
            return;
        }
        const lines = raw.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);

        if (lines.length === 0) {
            showMessage('Danh sách không hợp lệ. Vui lòng nhập mỗi dòng 1 kết quả.', 'error');
            return;
        }

        fishPool = lines.map(text => ({ text }));
        renderPond();

        showMessage('Đã thả ' + fishPool.length + ' con cá xuống hồ. Bấm Câu cá để bắt 1 con may mắn.', 'info');
    });

    btnCatchFish.addEventListener('click', () => {
        clearMessage();
        const now = Date.now();
        const diff = (now - lastCatchTime) / 1000;

        if (diff < COOLDOWN_SECONDS) {
            const remain = Math.ceil(COOLDOWN_SECONDS - diff);
            showMessage('Bạn câu hơi nhanh, vui lòng chờ thêm ' + remain + ' giây rồi câu tiếp.', 'error');
            return;
        }

        if (fishPool.length === 0) {
            showMessage('Chưa có cá nào trong hồ. Hãy nhập danh sách và bấm Thêm cá trước.', 'error');
            return;
        }

        lastCatchTime = now;

        const winnerIndex = Math.floor(Math.random() * fishPool.length);
        const winner = fishPool[winnerIndex];

        lastWinnerIndex = winnerIndex;
        lastWinnerText  = winner.text;

        catchResult.textContent = 'Đang câu...';
        catchResult.classList.add('has-value');

        const fishEl = pondWater.querySelector('.fish[data-index="' + winnerIndex + '"]');

        if (!fishEl) {
            // fallback: đợi 5s rồi show kết quả + modal
            setTimeout(() => {
                const msg = 'Đã câu trúng: ' + winner.text;
                catchResult.textContent = msg;
                showMessage(msg, 'info');
                openResultModal(msg);
            }, 5000);
            return;
        }

        // Ẩn cá trong hồ (giống như nó bơi ra)
        fishEl.classList.add('fish-hidden');

        // Clone để bay lên (không có label)
        const rect = fishEl.getBoundingClientRect();
        const fisherRect = fisherArea.getBoundingClientRect();

        const clone = fishEl.cloneNode(true);
        const cloneLabel = clone.querySelector('.fish-label');
        if (cloneLabel) cloneLabel.remove();

        clone.classList.remove('fish-hidden');
        clone.classList.add('fish-caught-fly');
        clone.style.left = rect.left + 'px';
        clone.style.top  = rect.top + 'px';
        clone.style.opacity = '1';
        clone.style.transform = 'translate(0, 0)';
        clone.style.transition = 'transform 5s ease-out, opacity 5s ease-out';
        document.body.appendChild(clone);

        const fromX = rect.left + rect.width / 2;
        const fromY = rect.top  + rect.height / 2;
        const toX   = fisherRect.left + fisherRect.width / 2;
        const toY   = fisherRect.top  + fisherRect.height / 2;

        const dx = toX - fromX;
        const dy = toY - fromY;

        requestAnimationFrame(() => {
            clone.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(1.1)';
            clone.style.opacity = '0.1';
        });

        setTimeout(() => {
            clone.remove();
            fishEl.classList.remove('fish-hidden');

            const msg = 'Đã câu trúng: ' + winner.text;
            catchResult.textContent = msg;
            showMessage(msg, 'info');
            openResultModal(msg);
        }, 5000);
    });
})();
</script>

<?php
require_once __DIR__ . '/../layout/footer.php';
