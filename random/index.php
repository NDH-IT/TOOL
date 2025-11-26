<?php
session_start();

/* ========= CHỐNG SPAM 10 GIÂY ========= */
$limitSeconds = 10;
if (!isset($_SESSION['last_random_time'])) {
    $_SESSION['last_random_time'] = 0;
}
$timeNow    = time();
$timePassed = $timeNow - $_SESSION['last_random_time'];

/* ========= BIẾN CHO GIAO DIỆN ========= */
$itemsRaw    = '';
$items       = [];
$winnerIndex = -1;
$winnerText  = '';
$error       = '';
$success     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items'])) {
    $itemsRaw = trim($_POST['items'] ?? '');

    // Chống spam trước
    if ($timePassed < $limitSeconds) {
        $remaining = $limitSeconds - $timePassed;
        $error = "Bạn quay hơi nhanh, vui lòng chờ thêm $remaining giây rồi quay tiếp.";
    } elseif ($itemsRaw === '') {
        $error = 'Chưa nhập danh sách để quay random.';
    } else {
        // Tách từng dòng, loại bỏ dòng trống
        $lines = preg_split('/\r\n|\r|\n/', $itemsRaw);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $items[] = $line;
            }
        }

        if (count($items) < 2) {
            $error = 'Cần ít nhất 2 lựa chọn để quay cho vui.';
        } else {
            // Random bằng PHP
            $winnerIndex = array_rand($items);
            $winnerText  = $items[$winnerIndex];

            $_SESSION['last_random_time'] = time();
            $success = 'Đã quay xong, vòng quay sẽ xoay và hiển thị kết quả.';
        }
    }
}

/* ========= GỌI HEADER CHUNG ========= */
$pageTitle = 'Vòng quay random | NDH IT Tools';
require_once __DIR__ . '/../layout/header.php';
?>

<!-- ========= CSS RIÊNG CHO TOOL RANDOM ========= -->
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

    .card-header {
        margin-bottom: 16px;
    }

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

    .grid-random {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.2fr);
        gap: 18px;
    }

    @media (max-width: 768px) {
        .grid-random {
            grid-template-columns: minmax(0, 1fr);
        }
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
        min-height: 160px;
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
        margin-top: 14px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        border: none;
        border-radius: 999px;
        padding: 8px 18px;
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

    .btn:hover {
        transform: translateY(-1px);
        opacity: 0.96;
    }

    .msg {
        margin-top: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 13px;
    }

    .msg-error {
        background: rgba(127, 29, 29, 0.35);
        border: 1px solid rgba(248, 113, 113, 0.7);
        color: #fca5a5;
    }

    .msg-success {
        background: rgba(22, 101, 52, 0.45);
        border: 1px solid rgba(34, 197, 94, 0.7);
        color: #bbf7d0;
    }

    /* Vùng vòng quay */
    .wheel-wrapper {
        position: relative;
        width: 100%;
        max-width: 360px;
        margin: 0 auto;
        aspect-ratio: 1 / 1;
    }

    .wheel-canvas {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        display: block;
    }

    /* Mũi tên chỉ */
    .wheel-pointer {
        position: absolute;
        top: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 14px solid transparent;
        border-right: 14px solid transparent;
        border-bottom: 24px solid #f97316; /* màu cam */
        filter: drop-shadow(0 0 6px rgba(248, 171, 80, 0.8));
        z-index: 5;
    }

    .wheel-pointer-base {
        position: absolute;
        top: 12px;
        left: 50%;
        transform: translateX(-50%);
        width: 18px;
        height: 18px;
        border-radius: 999px;
        background: #0f172a;
        border: 2px solid #f97316;
        z-index: 4;
    }

    /* Label nhỏ dưới vòng quay (optional) */
    .winner-label {
        margin-top: 10px;
        font-size: 13px;
        text-align: center;
        color: #bbf7d0;
        opacity: 0;
        transform: translateY(4px);
    }

    .winner-label strong {
        font-size: 14px;
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
    }
</style>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Vòng quay random
            <span class="card-badge">Mini game. Vòng quay ngẫu nhiên</span>
        </div>
        <div class="card-desc">
            Nhập danh sách (mỗi dòng một lựa chọn) &rarr; bấm <strong>Quay</strong> để vòng quay xoay,
            dừng đúng tại ô trúng. Kết quả sẽ hiện trong cửa sổ thông báo, bấm ra ngoài để tắt.
        </div>
    </div>

    <div class="grid-random">
        <!-- CỘT TRÁI: NHẬP DANH SÁCH -->
        <div>
            <form method="post" id="randomForm">
                <div class="field-label">Danh sách (mỗi dòng một lựa chọn)</div>
                <textarea
                    name="items"
                    class="input-area"
                    placeholder="Ví dụ:
A
B
C
D"
                    required
                ><?php echo htmlspecialchars($itemsRaw); ?></textarea>

                <div class="helper-text">
                    Tip: Dùng để quay trúng thưởng, bốc thăm, chọn người may mắn, chọn món ăn,...
                </div>
                <div class="helper-text" style="margin-top:6px;">
                    Mỗi lần quay cách nhau tối thiểu <?php echo $limitSeconds; ?> giây để tránh spam.
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn btn-primary">
                        🎡 Quay vòng tròn
                    </button>
                    <button type="button" class="btn btn-outline" id="clearListBtn">
                        Xoá danh sách
                    </button>
                </div>
            </form>

            <?php if ($error): ?>
                <div class="msg msg-error">
                    ⚠ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success && $winnerText !== ''): ?>
                <div class="msg msg-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- CỘT PHẢI: VÒNG QUAY HÌNH TRÒN -->
        <div>
            <div class="field-label">Vòng quay</div>
            <div class="wheel-wrapper" id="wheelWrapper"
                 data-winner-index="<?php echo $winnerIndex; ?>">
                <canvas id="wheelCanvas" class="wheel-canvas"></canvas>
                <div class="wheel-pointer"></div>
                <div class="wheel-pointer-base"></div>
            </div>

            <?php if ($winnerText !== ''): ?>
                <div class="winner-label" id="winnerLabel">
                    🎉 Kết quả: <strong><?php echo htmlspecialchars($winnerText); ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL KẾT QUẢ -->
<?php if ($winnerText !== ''): ?>
<div class="modal-overlay" id="winnerModal">
    <div class="modal-dialog" id="winnerModalDialog">
        <div class="modal-title">KẾT QUẢ VÒNG QUAY</div>
        <div class="modal-value">
            <?php echo htmlspecialchars($winnerText); ?>
        </div>
        <div class="modal-sub">
            Bấm ra ngoài cửa sổ này để đóng và quay tiếp lần sau.
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Dữ liệu từ PHP sang JS
var wheelItems = <?php echo json_encode(array_values($items), JSON_UNESCAPED_UNICODE); ?>;
var wheelWinnerIndex = <?php echo (int)$winnerIndex; ?>;
var spinDuration = 10000; // 10 giây
var winnerTextJS = <?php echo json_encode($winnerText, JSON_UNESCAPED_UNICODE); ?>;

document.addEventListener('DOMContentLoaded', function () {
    // XÓA DANH SÁCH: load lại trang cho sạch (không giữ value PHP)
    var clearBtn = document.getElementById('clearListBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            // load lại trang bằng GET, không có POST -> textarea trống
            window.location.href = window.location.pathname;
        });
    }

    var canvas = document.getElementById('wheelCanvas');
    var wrapper = document.getElementById('wheelWrapper');
    if (!canvas || !wrapper) return;

    // Resize canvas theo kích thước wrapper
    var rect = wrapper.getBoundingClientRect();
    var size = Math.min(rect.width, rect.height);
    canvas.width = size * window.devicePixelRatio;
    canvas.height = size * window.devicePixelRatio;
    var ctx = canvas.getContext('2d');
    ctx.scale(window.devicePixelRatio, window.devicePixelRatio);

    var cx = size / 2;
    var cy = size / 2;
    var radius = size / 2 - 8;

    // Nếu không có item -> vẽ vòng trống
    if (!wheelItems || wheelItems.length === 0) {
        ctx.beginPath();
        ctx.arc(cx, cy, radius, 0, Math.PI * 2);
        ctx.fillStyle = '#0f172a';
        ctx.fill();
        ctx.strokeStyle = 'rgba(148, 163, 184, 0.5)';
        ctx.lineWidth = 2;
        ctx.stroke();

        ctx.fillStyle = '#9ca3af';
        ctx.font = '13px system-ui';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Nhập danh sách bên trái rồi bấm Quay', cx, cy);
        return;
    }

    var colors = [
        '#f97316', '#22c55e', '#3b82f6', '#a855f7',
        '#ec4899', '#eab308', '#2dd4bf', '#fb7185',
        '#38bdf8', '#4ade80', '#fb923c', '#facc15'
    ];

    var sliceCount = wheelItems.length;
    var sliceAngle = Math.PI * 2 / sliceCount;

    function drawWheel(rotation) {
        ctx.clearRect(0, 0, size, size);

        for (var i = 0; i < sliceCount; i++) {
            var start = i * sliceAngle + rotation;
            var end   = start + sliceAngle;

            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, radius, start, end);
            ctx.closePath();
            ctx.fillStyle = colors[i % colors.length];
            ctx.fill();

            // viền nhẹ
            ctx.strokeStyle = 'rgba(15, 23, 42, 0.7)';
            ctx.lineWidth = 1;
            ctx.stroke();

            // Text
            var mid = start + sliceAngle / 2;
            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(mid);
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#0f172a';
            ctx.font = '12px system-ui';
            ctx.fillText(wheelItems[i], radius - 12, 0);
            ctx.restore();
        }

        // Vòng tròn giữa
        ctx.beginPath();
        ctx.arc(cx, cy, radius * 0.16, 0, Math.PI * 2);
        ctx.fillStyle = '#020617';
        ctx.fill();
        ctx.strokeStyle = 'rgba(148, 163, 184, 0.7)';
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    // Vẽ tĩnh lần đầu
    drawWheel(0);

    // Nếu có kết quả quay thì animate
    if (wheelWinnerIndex >= 0 && wheelWinnerIndex < sliceCount) {
        var pointerAngle = -Math.PI / 2; // mũi tên ở trên cùng
        var winnerCenter = wheelWinnerIndex * sliceAngle + sliceAngle / 2;

        // Muốn center của winner trùng với pointerAngle:
        // pointerAngle = winnerCenter + rotationEnd
        var baseRotation = pointerAngle - winnerCenter;

        // Thêm vài vòng cho nó xoay đã
        var extraRounds = 4; // 4 vòng
        var finalRotation = baseRotation + extraRounds * Math.PI * 2;

        var startTime = null;

        function easeOutCubic(t) {
            return 1 - Math.pow(1 - t, 3);
        }

        var winnerLabel = document.getElementById('winnerLabel');
        var modal = document.getElementById('winnerModal');
        var modalDialog = document.getElementById('winnerModalDialog');

        function animateSpin(timestamp) {
            if (!startTime) startTime = timestamp;
            var elapsed = timestamp - startTime;
            var t = Math.min(elapsed / spinDuration, 1); // 0 -> 1

            var eased = easeOutCubic(t);
            var currentRotation = eased * finalRotation;

            drawWheel(currentRotation);

            if (t < 1) {
                requestAnimationFrame(animateSpin);
            } else {
                // Hiện label nhỏ dưới vòng quay
                if (winnerLabel) {
                    winnerLabel.style.transition = 'opacity 0.4s ease, transform 0.3s ease';
                    winnerLabel.style.opacity = '1';
                    winnerLabel.style.transform = 'translateY(0)';
                }

                // Hiện modal kết quả
                if (modal && modalDialog && winnerTextJS) {
                    modal.style.display = 'flex';
                    // nhỏ delay cho đẹp
                    setTimeout(function () {
                        modalDialog.style.transition = 'opacity 0.3s ease, transform 0.25s ease';
                        modalDialog.style.opacity = '1';
                        modalDialog.style.transform = 'translateY(0)';
                    }, 150);
                }
            }
        }

        requestAnimationFrame(animateSpin);

        // Đóng modal khi click ra ngoài
        if (modal && modalDialog) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    }
});
</script>

<?php
/* ========= FOOTER CHUNG ========= */
require_once __DIR__ . '/../layout/footer.php';
