<?php
session_start();

/* ========== CHỐNG SPAM 30s ========== */
$limitSeconds = 30;
if (!isset($_SESSION['last_qr_time'])) {
    $_SESSION['last_qr_time'] = 0;
}
$timeNow    = time();
$timePassed = $timeNow - $_SESSION['last_qr_time'];

/* ========== BIẾN CƠ BẢN ========== */
$link      = '';
$error     = '';
$success   = '';
$dataUrl   = ''; // QR dạng data:image/png;base64,...

$maxLogoSize = 2 * 1024 * 1024; // 2MB

/* ========== HÀM PHỤ ========== */
function createImageFromAny($filePath) {
    $info = getimagesize($filePath);
    if ($info === false) return false;
    $mime = $info['mime'];

    switch ($mime) {
        case 'image/png':
            return imagecreatefrompng($filePath);
        case 'image/jpeg':
        case 'image/jpg':
            return imagecreatefromjpeg($filePath);
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                return imagecreatefromwebp($filePath);
            }
            return false;
        default:
            return false;
    }
}

/* ========== XỬ LÝ TẠO QR ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $link = trim($_POST['url'] ?? '');

    // 1. Chống spam
    if ($timePassed < $limitSeconds) {
        $remaining = $limitSeconds - $timePassed;
        $error = "Bạn thao tác quá nhanh, vui lòng chờ thêm $remaining giây rồi tạo tiếp.";
    }
    // 2. Chưa nhập URL
    elseif ($link === '') {
        $error = 'Chưa nhập URL cần tạo QR.';
    } else {
        // Chuẩn hoá URL để validate
        $linkForCheck = preg_match('~^https?://~i', $link) ? $link : 'http://' . $link;

        if (!filter_var($linkForCheck, FILTER_VALIDATE_URL)) {
            $error = 'URL không hợp lệ. Hãy kiểm tra lại.';
        } else {
            // ========== TẠO QR GỐC BẰNG API (600x600) ==========
            $qrSourceUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=0&data='
                         . rawurlencode($linkForCheck);

            $qrRaw = @file_get_contents($qrSourceUrl);
            if (!$qrRaw) {
                $error = 'Không tạo được QR từ server. Hãy thử lại sau.';
            } else {
                $qrImage = imagecreatefromstring($qrRaw);
                if (!$qrImage) {
                    $error = 'Không đọc được ảnh QR.';
                } else {
                    // ========== XỬ LÝ LOGO (NẾU CÓ) ==========
                    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                        if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                            $error = 'Upload logo bị lỗi. Bạn có thể thử lại hoặc bỏ qua logo.';
                        } else {
                            $logoTmp  = $_FILES['logo']['tmp_name'];
                            $logoSize = $_FILES['logo']['size'];

                            if ($logoSize > $maxLogoSize) {
                                $error = 'Logo tối đa 2MB.';
                            } else {
                                $logoImage = createImageFromAny($logoTmp);
                                if ($logoImage === false) {
                                    $error = 'Logo chỉ hỗ trợ PNG / JPG / WEBP.';
                                } else {
                                    $qrWidth  = imagesx($qrImage);
                                    $qrHeight = imagesy($qrImage);

                                    $logoWidth  = imagesx($logoImage);
                                    $logoHeight = imagesy($logoImage);

                                    // Logo chiếm ~20% chiều rộng QR
                                    $targetLogoWidth  = (int)($qrWidth * 0.2);
                                    $targetLogoHeight = (int)($targetLogoWidth * $logoHeight / $logoWidth);

                                    $logoResized = imagecreatetruecolor($targetLogoWidth, $targetLogoHeight);
                                    imagealphablending($logoResized, false);
                                    imagesavealpha($logoResized, true);

                                    // Resize logo
                                    imagecopyresampled(
                                        $logoResized, $logoImage,
                                        0, 0, 0, 0,
                                        $targetLogoWidth, $targetLogoHeight,
                                        $logoWidth, $logoHeight
                                    );

                                    // Vị trí logo ở giữa QR
                                    $dstX = (int)(($qrWidth - $targetLogoWidth) / 2);
                                    $dstY = (int)(($qrHeight - $targetLogoHeight) / 2);

                                    imagealphablending($qrImage, true);
                                    imagesavealpha($qrImage, true);

                                    imagecopy(
                                        $qrImage, $logoResized,
                                        $dstX, $dstY,
                                        0, 0,
                                        $targetLogoWidth, $targetLogoHeight
                                    );

                                    imagedestroy($logoResized);
                                    imagedestroy($logoImage);
                                }
                            }
                        }
                    }

                    if ($error === '') {
                        // ========== LÀM QR “XINH XINH CUTE” TRÊN NỀN ========
                        $qrWidth  = imagesx($qrImage);
                        $qrHeight = imagesy($qrImage);

                        $padding  = 40; // viền quanh
                        $canvasSize = $qrWidth + $padding * 2;

                        $canvas = imagecreatetruecolor($canvasSize, $canvasSize);
                        imagealphablending($canvas, false);
                        imagesavealpha($canvas, true);

                        // nền pastel xinh xinh
                        $bgColor = imagecolorallocate($canvas, 248, 250, 252); // gần như trắng
                        imagefilledrectangle($canvas, 0, 0, $canvasSize, $canvasSize, $bgColor);

                        // khung nhẹ
                        $borderColor = imagecolorallocate($canvas, 226, 232, 240);
                        imagerectangle($canvas, 1, 1, $canvasSize - 2, $canvasSize - 2, $borderColor);

                        // dán QR vào giữa
                        $dstX = (int)(($canvasSize - $qrWidth) / 2);
                        $dstY = (int)(($canvasSize - $qrHeight) / 2);

                        imagecopy(
                            $canvas, $qrImage,
                            $dstX, $dstY,
                            0, 0,
                            $qrWidth, $qrHeight
                        );

                        imagedestroy($qrImage);

                        // ========== CHUYỂN QR RA DATA URL (không lưu file) ==========
                        ob_start();
                        imagepng($canvas);
                        $pngData = ob_get_clean();
                        imagedestroy($canvas);

                        $dataUrl = 'data:image/png;base64,' . base64_encode($pngData);

                        $success = 'Tạo QR thành công' . (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE ? ' (đã chèn logo).' : '.');
                        $_SESSION['last_qr_time'] = time();
                    }
                }
            }
        }
    }
}

/* ========== GỌI HEADER CHUNG ========== */
$pageTitle = 'Tạo QR từ link (logo tuỳ chọn) | NDH IT Tools';
require_once __DIR__ . '/../layout/header.php';
?>

<!-- ========== CSS RIÊNG CỦA TOOL QR ========== -->
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
        max-width: 780px;
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

    .grid-qr {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 18px;
    }

    @media (max-width: 768px) {
        .grid-qr {
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
        min-height: 60px;
    }

    .input-area:focus {
        outline: none;
        border-color: rgba(129, 140, 248, 0.95);
    }

    .input-file {
        width: 100%;
        font-size: 12px;
        color: var(--text);
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

    .qr-card {
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: radial-gradient(circle at top, rgba(30, 64, 175, 0.25), transparent 65%),
                    rgba(15, 23, 42, 0.98);
        padding: 12px;
        text-align: center;
    }

    .qr-img-wrap {
        padding: 16px;
        background: #020617;
        border-radius: 12px;
        border: 1px solid rgba(30, 64, 175, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 260px;
    }

    .qr-img-wrap img {
        display: block;
        max-width: 100%;
        height: auto;
    }

    .output-link {
        margin-top: 8px;
        font-size: 11px;
        color: var(--muted);
        word-break: break-all;
    }
</style>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Tạo QR từ link (logo tuỳ chọn)
            <span class="card-badge">Tạo Qr xinh xinh để truy cập link bằng cách quét Qr</span>
        </div>
        <div class="card-desc">
            Nhập link cần tạo QR, có thể thêm logo nhỏ ở giữa. QR sẽ được tạo.
        </div>
    </div>

    <div class="grid-qr">
        <!-- Cột trái: form -->
        <div>
            <form method="post" enctype="multipart/form-data" id="qrForm">
                <div class="field-label">URL cần tạo QR</div>
                <textarea
                    name="url"
                    class="input-area"
                    placeholder="Ví dụ: https://ndhit.com"
                    required
                ><?php echo htmlspecialchars($link); ?></textarea>

                <div class="field-label" style="margin-top:10px;">Logo nhỏ ở giữa (có hoặc không)</div>
                <input type="file" name="logo" class="input-file" accept="image/png,image/jpeg,image/webp">
                <div class="helper-text">
                    PNG / JPG / WEBP · Tối đa 2MB · Không bắt buộc upload.
                </div>

                <div class="helper-text" style="margin-top:6px;">
                    Mỗi lần tạo QR cách nhau tối thiểu <?php echo $limitSeconds; ?> giây.
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn btn-primary">🔳 Tạo QR</button>
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('qrForm').reset();">
                        Làm lại
                    </button>
                </div>
            </form>

            <?php if ($error): ?>
                <div class="msg msg-error">
                    ⚠ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="msg msg-success">
                    ✅ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cột phải: preview -->
        <div>
            <div class="field-label">QR preview</div>
            <div class="qr-card">
                <div class="qr-img-wrap">
                    <?php if ($dataUrl): ?>
                        <img src="<?php echo htmlspecialchars($dataUrl); ?>" alt="QR code">
                    <?php else: ?>
                        <span style="color:var(--muted);font-size:13px;">
                            Chưa có QR. Nhập URL và (tuỳ chọn) logo bên trái rồi bấm <strong>Tạo QR</strong>.
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($dataUrl): ?>
                    <a href="<?php echo htmlspecialchars($dataUrl); ?>" download="qrcode.png"
                       class="btn btn-outline" style="margin-top:10px;">
                        ⬇ Tải QR PNG
                    </a>
                    <?php if ($link): ?>
                        <div class="output-link">
                            URL: <?php echo htmlspecialchars($link); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layout/footer.php';
