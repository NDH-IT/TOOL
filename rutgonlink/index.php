<?php
session_start();

/* ========== CẤU HÌNH DB + BIẾN CƠ BẢN ========== */

$baseShortUrl = 'https://ndhit.com/tool/rutgonlink/'; // đã dùng dạng /rutgonlink/MA

// SỬA LẠI CHO ĐÚNG THÔNG SỐ DATABASE CỦA BẠN
$dbHost = 'localhost';
$dbName = 'adminhv1_ndhit';
$dbUser = 'adminhv1_ndhit';
$dbPass = 'adminhv1_ndhit';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    $pdo = null;
}

/* ====== CHỐNG SPAM: TỐI THIỂU 30S MỚI ĐƯỢC TẠO 1 LẦN ====== */
$limitSeconds = 30;
if (!isset($_SESSION['last_short_time'])) {
    $_SESSION['last_short_time'] = 0;
}
$timeNow    = time();
$timePassed = $timeNow - $_SESSION['last_short_time'];

/* ========== CHỨC NĂNG REDIRECT NẾU CÓ THAM SỐ c (từ .htaccess) ========== */

if (isset($_GET['c']) && $_GET['c'] !== '') {
    $code = trim($_GET['c']);

    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id, long_url, hits FROM short_links WHERE code = :code LIMIT 1");
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $pdo->prepare("UPDATE short_links SET hits = hits + 1 WHERE id = :id")
                ->execute([':id' => $row['id']]);

            $url = $row['long_url'];
            if (!preg_match('~^https?://~i', $url)) {
                $url = 'http://' . $url;
            }
            header("Location: " . $url, true, 302);
            exit;
        }
    }

    header("HTTP/1.1 404 Not Found");
    echo "Link rút gọn không tồn tại hoặc đã bị xoá.";
    exit;
}

/* ========== XỬ LÝ TẠO LINK RÚT GỌN ========== */

$error     = '';
$success   = '';
$shortUrl  = '';
$longUrl   = '';
$custom    = '';

function generateCode($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $res = '';
    for ($i = 0; $i < $length; $i++) {
        $res .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $res;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['long_url'])) {
    $longUrl = trim($_POST['long_url']);
    $custom  = isset($_POST['custom_code']) ? trim($_POST['custom_code']) : '';

    if (!$pdo) {
        $error = 'Không kết nối được database, vui lòng báo admin.';
    }
    // CHỐNG SPAM 30S
    elseif ($timePassed < $limitSeconds) {
        $remaining = $limitSeconds - $timePassed;
        $error = "Bạn thao tác quá nhanh, vui lòng chờ thêm $remaining giây rồi tạo link mới.";
    }
    // KIỂM TRA ĐỂ TRỐNG URL
    elseif ($longUrl === '') {
        $error = 'Chưa nhập URL cần rút gọn.';
    } else {
        // Nếu không có http/https thì thêm tạm để validate
        $forCheck = preg_match('~^https?://~i', $longUrl) ? $longUrl : 'http://' . $longUrl;

        if (!filter_var($forCheck, FILTER_VALIDATE_URL)) {
            $error = 'URL không hợp lệ. Hãy kiểm tra lại.';
        } else {
            // Xử lý alias / code
            if ($custom !== '') {
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $custom)) {
                    $error = 'Alias chỉ được chứa chữ, số, dấu - và _.';
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM short_links WHERE code = :code LIMIT 1");
                    $stmt->execute([':code' => $custom]);
                    if ($stmt->fetch()) {
                        $error = 'Alias đã tồn tại, hãy chọn alias khác.';
                    } else {
                        $code = $custom;
                    }
                }
            } else {
                // Tự sinh code
                do {
                    $code = generateCode(6);
                    $stmt = $pdo->prepare("SELECT id FROM short_links WHERE code = :code LIMIT 1");
                    $stmt->execute([':code' => $code]);
                    $exists = $stmt->fetch();
                } while ($exists);
            }

            if ($error === '') {
                $stmt = $pdo->prepare("
                    INSERT INTO short_links (code, long_url, created_at, ip)
                    VALUES (:code, :long_url, NOW(), :ip)
                ");
                $stmt->execute([
                    ':code'     => $code,
                    ':long_url' => $longUrl,
                    ':ip'       => $_SERVER['REMOTE_ADDR'] ?? null
                ]);

                // LƯU THỜI GIAN TẠO LINK CUỐI CÙNG ĐỂ CHỐNG SPAM
                $_SESSION['last_short_time'] = time();

                $shortUrl = $baseShortUrl . $code;
                $success  = 'Tạo link rút gọn thành công!';
            }
        }
    }
}


/* ========== GỌI LAYOUT HEADER CHUNG ========== */
$pageTitle = 'Rút gọn link | NDH IT Tools';
require_once __DIR__ . '/../layout/header.php';
?>

<!-- CSS RIÊNG CHO TOOL RÚT GỌN LINK -->
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
        max-width: 760px;
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

    .input-area:focus,
    .input-text:focus {
        outline: none;
        border-color: rgba(129, 140, 248, 0.95);
    }

    .input-text {
        width: 100%;
        padding: 8px 11px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.7);
        background: rgba(15, 23, 42, 0.8);
        color: var(--text);
        font-size: 13px;
    }

    .helper-text {
        font-size: 11px;
        margin-top: 4px;
        color: var(--muted);
    }

    .row {
        margin-top: 12px;
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

    .btn:active {
        transform: translateY(0);
        box-shadow: none;
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
</style>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Rút gọn link
            <span class="card-badge">Rút gọn link dễ nhớ, dễ dùng.</span>
        </div>
        <div class="card-desc">
            Nhập URL dài &rarr; tạo link ngắn dạng <code>ndhit.com/tool/rutgonlink/...</code> để chia sẻ nhanh hơn.
        </div>
    </div>

    <form method="post" id="shortForm">
        <div class="row">
            <div class="field-label">URL cần rút gọn</div>
            <textarea
                name="long_url"
                class="input-area"
                placeholder="Ví dụ: https://www.huynhdeptrai.com/vip-pro/abc?ref=xyz"
                required
            ><?php echo htmlspecialchars($longUrl); ?></textarea>
            <div class="helper-text">
                Có thể nhập URL không có http/https, hệ thống sẽ tự xử lý khi redirect.
            </div>
        </div>

        <div class="row">
            <div class="field-label">Alias (có thể tùy chọn)</div>
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span style="font-size:12px;color:var(--muted);">
                    <?php echo htmlspecialchars($baseShortUrl); ?>
                </span>
                <input
                    type="text"
                    name="custom_code"
                    class="input-text"
                    style="max-width:160px;"
                    value="<?php echo htmlspecialchars($custom); ?>"
                    placeholder="my-link"
                >
            </div>
            <div class="helper-text">
                Chỉ cho phép chữ, số, dấu <code>-</code> và <code>_</code>.
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary">
                🔗 Tạo link rút gọn
            </button>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('shortForm').reset();">
                Làm lại
            </button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="msg msg-error">
            ⚠ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success && $shortUrl): ?>
        <div class="msg msg-success">
            ✅ <?php echo htmlspecialchars($success); ?><br>
            <div style="margin-top:4px;font-size:12px;">Link rút gọn:</div>
            <div class="output-group">
                <input
                    type="text"
                    id="shortUrl"
                    readonly
                    value="<?php echo htmlspecialchars($shortUrl); ?>"
                    onclick="this.select();"
                >
                <button type="button" class="btn btn-outline" onclick="copyShortUrl();">
                    Sao chép
                </button>
            </div>
            <div class="helper-text" id="copyStatus" style="margin-top:4px;"></div>
        </div>
    <?php endif; ?>
</div>

<script>
function copyShortUrl() {
    var input = document.getElementById('shortUrl');
    var status = document.getElementById('copyStatus');
    if (!input) return;

    input.select();
    input.setSelectionRange(0, 99999);

    try {
        var ok = document.execCommand('copy');
        if (ok) {
            status.textContent = 'Đã sao chép link rút gọn ✔';
        } else {
            status.textContent = 'Không sao chép được, hãy bôi đen và Ctrl+C.';
        }
    } catch (e) {
        status.textContent = 'Trình duyệt không hỗ trợ copy tự động. Vui lòng copy thủ công.';
    }
    setTimeout(function () {
        status.textContent = '';
    }, 2500);
}
</script>

<?php
/* ========== FOOTER CHUNG ========== */
require_once __DIR__ . '/../layout/footer.php';
