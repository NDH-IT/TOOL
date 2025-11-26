<?php
if (!isset($pageTitle)) {
    $pageTitle = 'NDH IT Tools';
}

$currentPath = $_SERVER['REQUEST_URI'] ?? '';

$homeActive  = ($currentPath === '/tool' || $currentPath === '/tool/') ? 'nav-active' : '';
$upanhActive = (strpos($currentPath, '/tool/upanh') === 0) ? 'nav-active' : '';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-soft: #eef2ff;
            --bg: #0f172a;
            --card-bg: #020617;
            --card-border: rgba(148, 163, 184, 0.35);
            --text: #e5e7eb;
            --muted: #9ca3af;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, #1d2433 0, #020617 55%, #000 100%);
            color: var(--text);
            min-height: 100vh;
        }

        .app {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(16px);
            background: linear-gradient(to right, rgba(15, 23, 42, .92), rgba(15, 23, 42, .85));
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            padding: 10px 18px;
        }

        .topbar-inner {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            border: 1px solid rgba(129, 140, 248, 0.6);
            background: radial-gradient(circle at 30% 30%, #a5b4fc, #4f46e5 45%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: #e5e7eb;
            box-shadow: 0 0 20px rgba(79, 70, 229, 0.55);
        }

        .brand-text-main {
            font-weight: 600;
            letter-spacing: 0.03em;
            font-size: 15px;
        }

        .brand-text-sub {
            font-size: 11px;
            color: var(--muted);
        }

        .nav {
            display: flex;
            gap: 10px;
            font-size: 13px;
        }

        .nav a {
            text-decoration: none;
            color: var(--muted);
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid transparent;
        }

        .nav a:hover {
            border-color: rgba(148, 163, 184, 0.4);
            color: #e5e7eb;
            background: rgba(15, 23, 42, 0.7);
        }

        .nav a.nav-active {
            border-color: rgba(129, 140, 248, 0.7);
            color: #e5e7eb;
            background: rgba(79, 70, 229, 0.2);
        }

        .main {
            flex: 1;
            padding: 26px 16px 32px;
        }

        .page-wrapper {
            max-width: 960px;
            margin: 0 auto;
        }

        .footer {
            padding: 10px 16px 18px;
            border-top: 1px solid rgba(15, 23, 42, 0.9);
            background: radial-gradient(circle at bottom, rgba(15, 23, 42, 0.95), #020617 55%);
            font-size: 11px;
            color: var(--muted);
        }

        .footer-inner {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.8);
        }

        @media (max-width: 768px) {
            .topbar-inner {
                flex-direction: column;
                align-items: flex-start;
            }
            .main {
                padding-top: 18px;
            }
        }
    </style>
</head>
<body>
<div class="app">
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-logo">H</div>
                <div>
                    <div class="brand-text-main">NDH IT TOOLS</div>
                    <div class="brand-text-sub">Bộ tool mini hỗ trợ công việc hằng ngày</div>
                </div>
            </div>
            <nav class="nav">
                <a href="/tool" class="<?php echo $homeActive; ?>">Trang chủ</a>
                <a href="/tool/upanh" class="<?php echo $upanhActive; ?>">Tạo link từ ảnh</a>
                <a href="/tool/linktoqr" class="<?php echo $upanhActive; ?>">Tạo qr từ link</a>
                <a href="/tool/rutgonlink" class="<?php echo $upanhActive; ?>">Rút gọn link</a>
                <a href="/tool/note" class="<?php echo $upanhActive; ?>">Note </a>
                <a href="/tool/random" class="<?php echo $upanhActive; ?>">Vòng quay rd</a>
                <a href="/tool/cauca" class="<?php echo $upanhActive; ?>">Câu cá rd</a>
                <!-- Sau này thêm tool khác ở đây -->
            </nav>
        </div>
    </header>
    <main class="main">
        <div class="page-wrapper">
