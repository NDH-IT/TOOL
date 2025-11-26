<?php
session_start();

// ========== CẤU HÌNH ==========
$uploadDir = __DIR__ . '/uploads/';                   // thư mục lưu file
$baseUrl   = 'https://ndhit.com/tool/upanh/uploads/'; // URL public

$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxSize    = 5 * 1024 * 1024; // 5MB

// Chống spam: tối thiểu 30s giữa 2 lần upload
$limitSeconds = 30;
if (!isset($_SESSION['last_upload_time'])) {
    $_SESSION['last_upload_time'] = 0;
}
$timeNow    = time();
$timePassed = $timeNow - $_SESSION['last_upload_time'];

$linkAnh  = '';
$error    = '';
$metaInfo = null;

// ========== XỬ LÝ UPLOAD ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {

    if ($timePassed < $limitSeconds) {
        $remaining = $limitSeconds - $timePassed;
        $error = "Vui lòng chờ thêm $remaining giây rồi upload tiếp.";
    } else {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload bị lỗi. Vui lòng thử lại.';
        } else {
            $fileTmp  = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileSize = $_FILES['image']['size'];

            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                $error = 'Chỉ cho phép file ảnh: jpg, jpeg, png, gif, webp.';
            } elseif ($fileSize > $maxSize) {
                $error = 'Dung lượng tối đa 5MB.';
            } else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $target  = $uploadDir . $newName;

                if (move_uploaded_file($fileTmp, $target)) {
                    // Ghi nhận thời gian upload cuối để chống spam
                    $_SESSION['last_upload_time'] = time();

                    $linkAnh = $baseUrl . $newName;
                    $metaInfo = [
                        'name' => $fileName,
                        'size' => round($fileSize / 1024, 1) . ' KB',
                        'ext'  => strtoupper($ext)
                    ];
                } else {
                    $error = 'Không lưu được file. Kiểm tra phân quyền thư mục uploads.';
                }
            }
        }
    }
}

// ========== GỌI HEADER CHUNG ==========
$pageTitle = 'Upload ảnh lấy link | NDH IT Tools';
require_once __DIR__ . '/../layout/header.php';
?>

<!-- CSS RIÊNG CỦA TOOL UP ẢNH -->
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

    .grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(0, 1.2fr);
        gap: 18px;
    }

    @media (max-width: 768px) {
        .grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .field-label {
        font-size: 13px;
        margin-bottom: 6px;
        color: var(--muted);
    }

    .file-input-wrap {
        border-radius: 12px;
        border: 1px dashed rgba(148, 163, 184, 0.7);
        padding: 16px;
        background: rgba(15, 23, 42, 0.7);
    }

    .file-input-wrap:hover {
        border-style: solid;
        border-color: rgba(129, 140, 248, 0.9);
    }

    input[type="file"] {
        width: 100%;
        font-size: 13px;
        color: var(--text);
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

    .btn:active {
        transform: translateY(0);
        box-shadow: none;
    }

    .helper-text {
        font-size: 11px;
        margin-top: 6px;
        color: var(--muted);
    }

    .msg {
        margin-top: 10px;
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

    .output-group {
        margin-top: 6px;
        display: flex;
        gap: 6px;
    }

    .output-group input {
        flex: 1;
        padding: 7px 9px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.7);
        background: rgba(15, 23, 42, 0.8);
        color: var(--text);
        font-size: 12px;
    }

    .output-group input:focus {
        outline: none;
        border-color: rgba(129, 140, 248, 0.95);
    }

    .preview-card {
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: radial-gradient(circle at top, rgba(30, 64, 175, 0.25), transparent 65%),
                    rgba(15, 23, 42, 0.98);
        padding: 10px;
    }

    .preview-inner {
        border-radius: 10px;
        overflow: auto;       /* nếu ảnh dài thì scroll */
        background: #020617;
        border: 1px solid rgba(30, 64, 175, 0.5);
        max-height: 70vh;     /* xem được full ảnh theo chiều cao màn hình, không bị crop – scroll để xem hết */
    }

    .preview-inner img {
        display: block;
        width: 100%;
        height: auto;
        object-fit: unset;    /* không cắt ảnh */
    }

    .preview-empty {
        font-size: 13px;
        color: var(--muted);
        text-align: center;
        padding: 26px 10px;
    }

    .preview-meta {
        margin-top: 6px;
        font-size: 11px;
        color: var(--muted);
        display: flex;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }
</style>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Upload ảnh lấy link
            <span class="card-badge">Chuyển ảnh thành link để sử dụng.</span>
        </div>
        <div class="card-desc">
            Chọn ảnh từ máy &rarr; Upload &rarr; Lấy link trực tiếp để dán vào website, chat, tài liệu...
        </div>
    </div>

    <div class="grid">
        <!-- CỘT TRÁI: FORM UPLOAD -->
        <div>
            <form method="post" enctype="multipart/form-data" id="uploadForm">
                <div class="field-label">Chọn ảnh</div>
                <div class="file-input-wrap">
                    <input type="file" name="image" id="imageInput" accept="image/*" required>
                    <div class="helper-text">
                        Hỗ trợ: JPG, PNG, GIF, WEBP · Tối đa 5MB
                    </div>
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn btn-primary">
                        <span>⬆</span> Upload & tạo link
                    </button>
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('uploadForm').reset();">
                        Làm lại
                    </button>
                </div>
            </form>

            <?php if ($error): ?>
                <div class="msg msg-error" style="margin-top:14px;">
                    ⚠ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($linkAnh): ?>
                <div class="msg msg-success" style="margin-top:14px;">
                    ✅ Upload thành công!
                    <div style="margin-top:4px;font-size:12px;">Link ảnh trực tiếp:</div>
                    <div class="output-group">
                        <input
                            type="text"
                            id="imageUrl"
                            readonly
                            value="<?php echo htmlspecialchars($linkAnh); ?>"
                            onclick="this.select();"
                        >
                        <button type="button" class="btn btn-outline" onclick="copyImageUrl();">
                            Sao chép
                        </button>
                    </div>
                    <div class="helper-text" id="copyStatus" style="margin-top:4px;"></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- CỘT PHẢI: PREVIEW -->
        <div>
            <div class="field-label">Preview ảnh</div>
            <div class="preview-card">
                <div class="preview-inner" id="previewBox">
                    <?php if ($linkAnh): ?>
                        <img src="<?php echo htmlspecialchars($linkAnh); ?>" alt="Ảnh đã upload">
                    <?php else: ?>
                        <div class="preview-empty">
                            Chưa có ảnh.<br>
                            Chọn một file ở bên trái rồi bấm <strong>Upload & tạo link</strong>.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="preview-meta">
                    <?php if ($linkAnh && $metaInfo): ?>
                        <span><?php echo htmlspecialchars($metaInfo['name']); ?></span>
                        <span><?php echo htmlspecialchars($metaInfo['size']); ?> · <?php echo htmlspecialchars($metaInfo['ext']); ?></span>
                    <?php else: ?>
                        <span>Sẵn sàng tải ảnh...</span>
                        <span>ndhit.com/tool/upanh</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyImageUrl() {
    var input = document.getElementById('imageUrl');
    var status = document.getElementById('copyStatus');
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999);

    try {
        var ok = document.execCommand('copy');
        if (ok) {
            status.textContent = 'Đã sao chép link vào clipboard ✔';
        } else {
            status.textContent = 'Không sao chép được, hãy bôi đen và Ctrl+C.';
        }
    } catch (e) {
        status.textContent = 'Trình duyệt không hỗ trợ copy tự động. Vui lòng copy thủ công.';
    }
    setTimeout(function() {
        status.textContent = '';
    }, 2500);
}
</script>

<?php
require_once __DIR__ . '/../layout/footer.php';
