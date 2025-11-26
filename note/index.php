<?php
// /tool/note/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
$pageTitle = 'Ghi chú | NDH IT Tools';
require_once __DIR__ . '/../layout/header.php';

// ----------------- HÀM PHỤ -----------------
function generateRandomSlug($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $out = '';
    $maxIdx = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        if (function_exists('random_int')) {
            $out .= $chars[random_int(0, $maxIdx)];
        } else {
            $out .= $chars[mt_rand(0, $maxIdx)];
        }
    }
    return $out;
}

function sanitizeSlugBasic($slug) {
    $slug = trim($slug);
    // Chấp nhận a-z, A-Z, 0-9, dấu -
    $slug = preg_replace('/[^a-zA-Z0-9\-]/', '', $slug);
    return $slug;
}

// ----------------- XỬ LÝ SLUG -----------------
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$slug = sanitizeSlugBasic($slug);

// Nếu không có slug -> tạo random và redirect
if ($slug === '') {
    // Tạo slug random & đảm bảo không trùng
    $maxTry = 10;
    $newSlug = '';
    for ($i = 0; $i < $maxTry; $i++) {
        $candidate = generateRandomSlug(6);
        $sql = "SELECT id FROM notes WHERE slug = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $candidate);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 0) {
                $newSlug = $candidate;
                $stmt->close();
                break;
            }
            $stmt->close();
        }
    }
    if ($newSlug === '') {
        $newSlug = generateRandomSlug(6);
    }

    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // /tool/note
    header('Location: ' . $basePath . '/' . $newSlug);
    exit;
}

// ----------------- LẤY NOTE TỪ DB (HOẶC TẠO MỚI) -----------------
$noteId        = null;
$noteContent   = '';
$passwordHash  = null;
$hasPassword   = false;
$needPassword  = false;
$passwordError = '';

$sql = "SELECT id, content, password_hash FROM notes WHERE slug = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $stmt->bind_result($id, $content, $pwhash);
    if ($stmt->fetch()) {
        $noteId       = $id;
        $noteContent  = $content;
        $passwordHash = $pwhash;
        $hasPassword  = !empty($passwordHash);
    }
    $stmt->close();
}

// ----------------- KIỂM TRA MẬT KHẨU (NẾU CÓ) -----------------
$isUnlocked = false;
if ($hasPassword) {
    // Đã mở khóa trước đó trong session?
    if (isset($_SESSION['note_unlocked']) && !empty($_SESSION['note_unlocked'][$noteId])) {
        $isUnlocked = true;
    } else {
        // Nếu gửi form mật khẩu
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_password'])) {
            $inputPw = isset($_POST['password']) ? $_POST['password'] : '';
            if ($inputPw === '') {
                $passwordError = 'Vui lòng nhập mật khẩu.';
            } else {
                if (password_verify($inputPw, $passwordHash)) {
                    // Đúng mật khẩu
                    if (!isset($_SESSION['note_unlocked'])) {
                        $_SESSION['note_unlocked'] = array();
                    }
                    $_SESSION['note_unlocked'][$noteId] = true;
                    $isUnlocked = true;

                    // Refresh để tránh repost form
                    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                    header('Location: ' . $basePath . '/' . $slug);
                    exit;
                } else {
                    $passwordError = 'Mật khẩu không đúng.';
                }
            }
        }
    }
}

// ----------------- TÍNH WORD COUNT HIỆN TẠI -----------------
function countWordsApprox($text) {
    $text = trim($text);
    if ($text === '') return 0;
    $parts = preg_split('/\s+/u', $text);
    $parts = array_filter($parts, function($w) { return $w !== ''; });
    return count($parts);
}
$currentWords     = countWordsApprox($noteContent);
$currentMaxWords  = $hasPassword ? 10000 : 3000;

// Chuẩn bị URL hiện tại để hiển thị
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host     = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // /tool/note
$fullUrl  = $protocol . $host . $basePath . '/' . $slug;

// ----------------- GIAO DIỆN -----------------
?>

<style>
    .card {
        background:
            radial-gradient(circle at top left, rgba(79, 70, 229, 0.25), transparent 55%),
            radial-gradient(circle at bottom right, rgba(8, 47, 73, 0.4), transparent 60%),
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
        display: flex;
        justify-content: space-between;
        gap: 10px;
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
        padding: 2px 8px
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

    .note-main {
        margin-top: 10px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .field-label {
        font-size: 13px;
        margin-bottom: 4px;
        color: var(--muted);
    }

    /* Khung editor có cột số dòng + nội dung */
    .note-editor {
        display: flex;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.7);
        background: rgba(15, 23, 42, 0.9);
        min-height: 600px; /* chiều cao chung */
        overflow: hidden;
    }

    .line-numbers {
        padding: 9px 6px 9px 10px;
        font-size: 12px;
        color: #64748b;
        text-align: right;
        border-right: 1px solid rgba(148, 163, 184, 0.4);
        user-select: none;
        white-space: pre;
        line-height: 1.4;
        overflow: hidden;
    }

    .input-area {
        width: 100%;
        padding: 9px 11px;
        border: none;
        border-radius: 0 10px 10px 0;
        background: transparent;
        color: var(--text);
        font-size: 13px;
        resize: vertical;
        min-height: 600px;
        line-height: 1.4;
        overflow: auto;
    }

    .input-area:focus {
        outline: none;
        border-color: transparent;
    }

    .input-text {
        width: 100%;
        padding: 7px 11px;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.7);
        background: rgba(15, 23, 42, 0.9);
        color: var(--text);
        font-size: 13px;
    }

    .input-text:focus {
        outline: none;
        border-color: rgba(129, 140, 248, 0.95);
    }

    .helper-text {
        font-size: 11px;
        margin-top: 4px;
        color: var(--muted);
    }

    .msg-error {
        margin-top: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 12px;
        background: rgba(127, 29, 29, 0.35);
        border: 1px solid rgba(248, 113, 113, 0.7);
        color: #fca5a5;
    }

    .btn-primary {
        border: none;
        border-radius: 999px;
        padding: 7px 16px;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(to right, #4f46e5, #6366f1);
        color: #f9fafb;
        box-shadow: 0 0 0 1px rgba(129, 140, 248, 0.7), 0 12px 28px rgba(79, 70, 229, 0.6);
        transition: transform 0.08s ease, opacity 0.12s ease;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        opacity: 0.96;
    }

    .password-form {
        margin-top: 10px;
    }

    .save-status {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
    }

    @media (max-width: 600px) {
        .card {
            padding: 18px 14px 16px;
        }

        .note-editor {
            min-height: 400px;
        }

        .input-area {
            min-height: 400px;
        }
    }
</style>

<div class="card" data-slug="<?= htmlspecialchars($slug) ?>" data-has-password="<?= $hasPassword ? '1' : '0' ?>">
    <div class="card-header">
        <div>
            <div class="card-title">
                Ghi chú
                <span class="card-badge"><?= $hasPassword ? 'Note có mật khẩu' : 'Note không mật khẩu' ?></span>
            </div>
            <div class="card-desc">
                Truy cập <code>/tool/note</code> sẽ tự random note mới.<br> 
                Truy cập <code>/tool/note/my-link</code> sẽ dùng note bạn đặt.
            </div>
        </div>
    </div>

    <?php if ($hasPassword && !$isUnlocked): ?>
        <!-- MÀN HÌNH NHẬP MẬT KHẨU -->
        <div class="password-form">
            <form method="post">
                <div class="field-label">Note này đã được đặt mật khẩu. Vui lòng nhập mật khẩu để xem.</div>
                <input type="password" name="password" class="input-text" placeholder="Nhập mật khẩu để mở note">
                <div class="helper-text">
                    Sau khi nhập đúng, bạn sẽ xem và chỉnh sửa nội dung.  
                    Nội dung tối đa: <strong>10000 từ</strong>.
                </div>
                <?php if ($passwordError): ?>
                    <div class="msg-error"><?= htmlspecialchars($passwordError) ?></div>
                <?php endif; ?>
                <div style="margin-top:8px;">
                    <button type="submit" name="check_password" value="1" class="btn-primary">
                        🔓 Mở khóa ghi chú
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- MÀN HÌNH SOẠN NOTE -->
        <div class="note-main">
            <div style="display:flex; justify-content: space-between; align-items:center;">
    <div class="field-label">Nội dung ghi chú</div>

    <button class="btn-primary" onclick="location.href='<?= $basePath ?>'">
    ➕ New note
    </button>
    </div>

                <!-- editor có cột số dòng -->
                <div class="note-editor">
                    <div class="line-numbers" id="lineNumbers"></div>
                    <textarea
                        id="noteContent"
                        class="input-area"
                        placeholder="Gõ nội dung ghi chú tại đây... (tự động lưu sau khi bạn dừng gõ)"
                    ><?= htmlspecialchars($noteContent) ?></textarea>
                </div>

                <div class="helper-text">
                    <?= $hasPassword
                        ? 'Note đã đặt mật khẩu · Giới hạn ~10000 từ.'
                        : 'Note chưa đặt mật khẩu · Giới hạn ~3000 từ. Để tăng giới hạn, bạn có thể đặt mật khẩu bên dưới.' ?>
                </div>
                <div id="saveStatus" class="save-status">
                    Từ hiện tại: <?= (int)$currentWords ?> / <?= (int)$currentMaxWords ?> (ước lượng).
                </div>
            </div>

            <?php if (!$hasPassword): ?>
                <div>
                    <div class="field-label">Đặt mật khẩu cho note này (tuỳ chọn)</div>
                    <input
                        type="password"
                        id="notePassword"
                        class="input-text"
                        placeholder="Nhập mật khẩu, để trống nếu không cần"
                    >
                    <div class="helper-text">
                        Nếu bạn đặt mật khẩu, lần sau mở note sẽ phải nhập mật khẩu.  
                        Khi đã có mật khẩu, dung lượng note tăng lên ~<strong>10000 từ</strong>.
                        (Mật khẩu sẽ được lưu cùng lúc với lần lưu tiếp theo của nội dung.)
                    </div>
                </div>
            <?php else: ?>
                <div class="helper-text">
                    Note này đã có mật khẩu. Hiện tại không cho đổi mật khẩu qua giao diện để tránh xoá nhầm.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!$hasPassword || $isUnlocked): ?>
<script>
(function () {
    const cardEl       = document.querySelector('.card[data-slug]');
    if (!cardEl) return;

    const NOTE_SLUG    = cardEl.getAttribute('data-slug');
    const HAS_PASSWORD = cardEl.getAttribute('data-has-password') === '1';

    const contentEl    = document.getElementById('noteContent');
    const pwEl         = document.getElementById('notePassword');
    const statusEl     = document.getElementById('saveStatus');
    const lineNumbersEl= document.getElementById('lineNumbers');

    if (!contentEl) return;

    let saveTimer = null;
    let isSaving  = false;

    function setStatus(text) {
        if (statusEl) statusEl.textContent = text;
    }

    // Cập nhật số dòng
    function updateLineNumbers() {
        if (!lineNumbersEl || !contentEl) return;
        const value = contentEl.value || '';
        const lines = value.split(/\r\n|\r|\n/).length || 1;
        let buf = '';
        for (let i = 1; i <= lines; i++) {
            buf += i + '\n';
        }
        lineNumbersEl.textContent = buf;
        // sync chiều cao nếu cần
        lineNumbersEl.scrollTop = contentEl.scrollTop;
    }

    function scheduleSave() {
        if (isSaving) return;
        setStatus('Đang lưu...');
        if (saveTimer) clearTimeout(saveTimer);
        saveTimer = setTimeout(doSave, 800);
    }

    function doSave() {
        if (!NOTE_SLUG) return;
        isSaving = true;

        const formData = new FormData();
        formData.append('slug', NOTE_SLUG);
        formData.append('content', contentEl.value);
        if (!HAS_PASSWORD && pwEl && pwEl.value.trim() !== '') {
            formData.append('password', pwEl.value);
        }

        fetch('save.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.status === 'ok') {
                const msg = 'Đã lưu lúc ' + (data.saved_at || '') +
                            ' · ' + data.word_count + '/' + data.max_words + ' từ';
                setStatus(msg);
                if (data.note_now_has_password && pwEl) {
                    pwEl.value = '';
                    pwEl.disabled = true;
                }
            } else if (data && data.message) {
                setStatus('Lỗi: ' + data.message);
            } else {
                setStatus('Không lưu được (lỗi không xác định).');
            }
        })
        .catch(() => {
            setStatus('Không lưu được (lỗi mạng hoặc server).');
        })
        .finally(() => {
            isSaving = false;
        });
    }

    // Gắn event
    contentEl.addEventListener('input', function () {
        updateLineNumbers();
        scheduleSave();
    });

    contentEl.addEventListener('scroll', function () {
        if (lineNumbersEl) {
            lineNumbersEl.scrollTop = contentEl.scrollTop;
        }
    });

    if (!HAS_PASSWORD && pwEl) {
        pwEl.addEventListener('change', scheduleSave);
    }

    // cập nhật số dòng lần đầu khi load trang
    updateLineNumbers();
})();
</script>
<?php endif; ?>

<?php
require_once __DIR__ . '/../layout/footer.php';
