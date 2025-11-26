<?php
// /tool/note/save.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

function sanitizeSlugBasic($slug) {
    $slug = trim($slug);
    $slug = preg_replace('/[^a-zA-Z0-9\-]/', '', $slug);
    return $slug;
}

function countWordsApprox($text) {
    $text = trim($text);
    if ($text === '') return 0;
    $parts = preg_split('/\s+/u', $text);
    $parts = array_filter($parts, function($w) { return $w !== ''; });
    return count($parts);
}

$slug     = isset($_POST['slug']) ? sanitizeSlugBasic($_POST['slug']) : '';
$content  = isset($_POST['content']) ? trim($_POST['content']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($slug === '') {
    echo json_encode(['status' => 'error', 'message' => 'Slug không hợp lệ.']);
    exit;
}

// Tìm note theo slug nếu đã tồn tại
$noteId       = null;
$passwordHash = null;
$oldContent   = '';
$hasPassword  = false;

$sql = "SELECT id, content, password_hash FROM notes WHERE slug = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $stmt->bind_result($id, $dbContent, $dbPwHash);
    if ($stmt->fetch()) {
        $noteId       = $id;
        $oldContent   = $dbContent;
        $passwordHash = $dbPwHash;
        $hasPassword  = !empty($passwordHash);
    }
    $stmt->close();
}

// Nếu note đã có mật khẩu mà session chưa unlock -> không cho lưu
if ($hasPassword) {
    if (!isset($_SESSION['note_unlocked']) || empty($_SESSION['note_unlocked'][$noteId])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Note này có mật khẩu. Vui lòng tải lại trang và nhập mật khẩu chính xác trước khi chỉnh sửa.'
        ]);
        exit;
    }
}

// Nếu note CHƯA có mật khẩu mà user gửi password lên -> set mật khẩu (1 lần)
$noteNowHasPassword = false;
if (!$hasPassword && $password !== '') {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $hasPassword  = true;
    $noteNowHasPassword = true;
}

// Tính giới hạn từ theo trạng thái mật khẩu
$wordCount = countWordsApprox($content);
$maxWords  = $hasPassword ? 10000 : 3000;

if ($wordCount > $maxWords) {
    echo json_encode([
        'status'     => 'error',
        'message'    => 'Vượt giới hạn ' . $maxWords . ' từ. Hiện tại khoảng ' . $wordCount . ' từ.',
        'word_count' => $wordCount,
        'max_words'  => $maxWords
    ]);
    exit;
}

// ----------------- LOGIC "CHỈ LƯU KHI CÓ NỘI DUNG HOẶC MẬT KHẨU" -----------------

// Nếu CHƯA có record trong DB
if ($noteId === null) {
    // Không có nội dung + không có mật khẩu -> KHÔNG lưu gì vào database
    if ($content === '' && $passwordHash === null) {
        $now   = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $timeStr = $now->format('H:i:s d/m/Y');

        echo json_encode([
            'status'                => 'ok',
            'message'               => 'Note trống, chưa lưu vào database.',
            'word_count'            => 0,
            'max_words'             => $maxWords,
            'saved_at'              => $timeStr,
            'note_now_has_password' => false
        ]);
        exit;
    }

    // Có nội dung hoặc có mật khẩu -> TẠO MỚI record
    $sql = "INSERT INTO notes (slug, content, password_hash) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Không tạo được ghi chú mới.']);
        exit;
    }
    $stmt->bind_param("sss", $slug, $content, $passwordHash);
    if (!$stmt->execute()) {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Không tạo được ghi chú mới.']);
        exit;
    }
    $noteId = $stmt->insert_id;
    $stmt->close();

} else {
    // ĐÃ có record trong DB

    // Nếu nội dung rỗng, KHÔNG có mật khẩu -> XÓA luôn record (dọn rác)
    if ($content === '' && $passwordHash === null) {
        $del = $mysqli->prepare("DELETE FROM notes WHERE id = ?");
        if ($del) {
            $del->bind_param("i", $noteId);
            $del->execute();
            $del->close();
        }

        $now   = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $timeStr = $now->format('H:i:s d/m/Y');

        echo json_encode([
            'status'                => 'ok',
            'message'               => 'Note trống, đã xoá khỏi database.',
            'word_count'            => 0,
            'max_words'             => $maxWords,
            'saved_at'              => $timeStr,
            'note_now_has_password' => false
        ]);
        exit;
    }

    // Ngược lại: có nội dung HOẶC có mật khẩu -> UPDATE
    if ($passwordHash !== null) {
        $sql = "UPDATE notes SET content = ?, password_hash = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Không chuẩn bị được câu lệnh cập nhật.']);
            exit;
        }
        $stmt->bind_param("ssi", $content, $passwordHash, $noteId);
    } else {
        $sql = "UPDATE notes SET content = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Không chuẩn bị được câu lệnh cập nhật.']);
            exit;
        }
        $stmt->bind_param("si", $content, $noteId);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Không lưu được ghi chú vào database.']);
        exit;
    }
    $stmt->close();
}

// Thời gian lưu
$now   = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
$timeStr = $now->format('H:i:s d/m/Y');

echo json_encode([
    'status'                => 'ok',
    'message'               => 'Đã lưu ghi chú.',
    'word_count'            => $wordCount,
    'max_words'             => $maxWords,
    'saved_at'              => $timeStr,
    'note_now_has_password' => $noteNowHasPassword
]);
