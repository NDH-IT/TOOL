<?php
$pageTitle = 'NDH IT Tools | Trang chủ';
require_once __DIR__ . '/layout/header.php';
?>

<style>
    .tools-wrapper {
        max-width: 1040px;
        margin: 0 auto;
        padding: 10px 0 26px;
    }

    .tools-header {
        text-align: center;
        margin-bottom: 6px;
    }

    .tools-title {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #e5e7eb;
    }

    .tools-subtitle {
        margin-top: 6px;
        font-size: 13px;
        color: var(--muted);
    }

    .tools-search {
        margin: 14px auto 18px;
        max-width: 420px;
    }

    .tools-search-inner {
        position: relative;
    }

    .tools-search-input {
        width: 100%;
        padding: 7px 11px 7px 30px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.7);
        background: rgba(15, 23, 42, 0.9);
        color: var(--text);
        font-size: 13px;
    }

    .tools-search-input:focus {
        outline: none;
        border-color: rgba(129, 140, 248, 0.95);
    }

    .tools-search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        color: #9ca3af;
        pointer-events: none;
    }

    .tools-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    @media (max-width: 900px) {
        .tools-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .tools-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .tool-card {
        position: relative;
        border-radius: 18px;
        padding: 14px 14px 13px;
        border: 1px solid rgba(31, 41, 55, 0.9);
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.28), transparent 55%),
            radial-gradient(circle at bottom right, rgba(8, 47, 73, 0.65), transparent 55%),
            rgba(15, 23, 42, 0.96);
        box-shadow:
            0 18px 45px rgba(15, 23, 42, 0.95),
            0 0 0 1px rgba(15, 23, 42, 0.9);
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
        overflow: hidden;
    }

    .tool-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top center, rgba(148, 163, 184, 0.16), transparent 55%);
        opacity: 0;
        transition: opacity 0.15s ease;
        pointer-events: none;
    }

    .tool-card:hover {
        transform: translateY(-2px);
        border-color: rgba(96, 165, 250, 0.9);
        box-shadow:
            0 22px 55px rgba(15, 23, 42, 0.98),
            0 0 0 1px rgba(30, 64, 175, 0.9);
    }

    .tool-card:hover::before {
        opacity: 1;
    }

    .tool-row-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }

    .tool-main {
        display: flex;
        gap: 8px;
        align-items: flex-start;
    }

    .tool-icon {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        background: radial-gradient(circle at 30% 0%, rgba(248, 250, 252, 0.2), transparent 55%),
                    rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(148, 163, 184, 0.7);
        box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.95);
    }

    .tool-title {
        font-size: 14px;
        font-weight: 600;
        color: #e5e7eb;
    }

    .tool-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        padding: 2px 7px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.6);
        color: #94a3b8;
        background: rgba(15, 23, 42, 0.9);
        white-space: nowrap;
    }

    .tool-desc {
        font-size: 12px;
        color: var(--muted);
    }

    .tool-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 2px;
    }

    .tool-meta-item {
        font-size: 10px;
        padding: 2px 7px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(51, 65, 85, 0.9);
        color: #9ca3af;
    }

    .tool-footer {
        margin-top: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
    }

    .tool-link {
        font-size: 11px;
        color: #bfdbfe;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        opacity: 0.9;
    }

    .tool-link span {
        text-decoration: underline;
        text-decoration-style: dotted;
    }

    .tool-link:hover {
        opacity: 1;
    }

    .tool-button {
        border: none;
        border-radius: 999px;
        font-size: 11px;
        padding: 6px 12px;
        cursor: pointer;
        background: linear-gradient(to right, #4f46e5, #6366f1);
        color: #f9fafb;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 0 0 1px rgba(129, 140, 248, 0.7);
        text-decoration: none;
    }

    .tool-button:hover {
        opacity: 0.96;
        transform: translateY(-1px);
    }
</style>

<div class="tools-wrapper">
    <div class="tools-header">
        <div class="tools-title">NDH IT TOOLS</div>
        <div class="tools-subtitle">
            Tổng hợp các tiện ích nhỏ phục vụ người dùng: upload ảnh, rút gọn link, tạo QR, mini game...
        </div>
    </div>

    <div class="tools-search">
        <div class="tools-search-inner">
            <span class="tools-search-icon">🔍</span>
            <input
                type="text"
                id="toolSearch"
                class="tools-search-input"
                placeholder="Tìm kiếm tool theo tên (ví dụ: ghi chú, upload, QR, random...)"
            >
        </div>
    </div>

    <div class="tools-grid">
        <div class="tool-card">
            <div class="tool-row-header">
                <div class="tool-main">
                    <div class="tool-icon">🖼</div>
                    <div>
                        <div class="tool-title">Upload ảnh lấy link</div>
                        <div class="tool-meta">
                            <span class="tool-meta-item">Chống spam 30s</span>
                            <span class="tool-meta-item">Xem trước full ảnh</span>
                        </div>
                    </div>
                </div>
                <div class="tool-tag">/upanh</div>
            </div>
            <div class="tool-desc">
                Upload ảnh lên hosting và nhận link trực tiếp, phù hợp để dán vào website, chat, hoặc cho khách quét.
            </div>
            <div class="tool-footer">
                <a class="tool-link" href="upanh/">
                    <span>Mở tool</span>
                    <span>↗</span>
                </a>
                <a class="tool-button" href="upanh/">
                    Dùng ngay
                </a>
            </div>
        </div>

        <div class="tool-card">
            <div class="tool-row-header">
                <div class="tool-main">
                    <div class="tool-icon">🔗</div>
                    <div>
                        <div class="tool-title">Rút gọn link</div>
                        <div class="tool-meta">
                            <span class="tool-meta-item">Đường dẫn đẹp</span>
                            <span class="tool-meta-item">Chống spam 30s</span>
                        </div>
                    </div>
                </div>
                <div class="tool-tag">/rutgonlink</div>
            </div>
            <div class="tool-desc">
                Biến URL dài thành link ngắn dạng <code>/rutgonlink/ABC123</code>, dễ đọc, dễ gửi cho khách.
            </div>
            <div class="tool-footer">
                <a class="tool-link" href="rutgonlink/">
                    <span>Mở tool</span>
                    <span>↗</span>
                </a>
                <a class="tool-button" href="rutgonlink/">
                    Rút gọn ngay
                </a>
            </div>
        </div>

        <div class="tool-card">
            <div class="tool-row-header">
                <div class="tool-main">
                    <div class="tool-icon">📱</div>
                    <div>
                        <div class="tool-title">Chuyển link thành QR</div>
                        <div class="tool-meta">
                            <span class="tool-meta-item">Có thể chèn logo</span>
                            <span class="tool-meta-item">QR xinh xinh</span>
                            <span class="tool-meta-item">Chống spam 30s</span>
                        </div>
                    </div>
                </div>
                <div class="tool-tag">/linktoqr</div>
            </div>
            <div class="tool-desc">
                Nhập link &rarr; tạo QR code (có thể gắn logo nhỏ ở giữa), tải về để in trên kệ, quầy, poster...
            </div>
            <div class="tool-footer">
                <a class="tool-link" href="linktoqr/">
                    <span>Mở tool</span>
                    <span>↗</span>
                </a>
                <a class="tool-button" href="linktoqr/">
                    Tạo QR
                </a>
            </div>
        </div>
        
        <div class="tool-card">
            <div class="tool-row-header">
                <div class="tool-main">
                    <div class="tool-icon">📝</div>
                    <div>
                        <div class="tool-title">Ghi chú nhanh</div>
                        <div class="tool-meta">
                            <span class="tool-meta-item">Tạo note tự động</span>
                            <span class="tool-meta-item">Có thể đặt mật khẩu</span>
                            <span class="tool-meta-item">Autosave</span>
                        </div>
                    </div>
                </div>
                <div class="tool-tag">/note</div>
            </div>

            <div class="tool-desc">
                Mỗi lần mở tool sẽ tạo 1 đường dẫn ghi chú mới.
                Có thể đặt mật khẩu, giới hạn 3000–10000 từ, tự động lưu khi nhập.
            </div>

            <div class="tool-footer">
                <a class="tool-link" href="note/">
                    <span>Mở tool</span>
                    <span>↗</span>
                </a>
                <a class="tool-button" href="note/">
                    Tạo note
                </a>
            </div>
        </div>

        <div class="tool-card">
            <div class="tool-row-header">
                <div class="tool-main">
                    <div class="tool-icon">🎡</div>
                    <div>
                        <div class="tool-title">Vòng quay random</div>
                        <div class="tool-meta">
                            <span class="tool-meta-item">Quay ~10s</span>
                            <span class="tool-meta-item">Popup kết quả</span>
                            <span class="tool-meta-item">Chống spam 10s</span>
                        </div>
                    </div>
                </div>
                <div class="tool-tag">/random</div>
            </div>
            <div class="tool-desc">
                Nhập danh sách phần thưởng / lựa chọn, vòng quay xoay chậm với mũi tên chỉ kết quả, phù hợp mini game.
            </div>
            <div class="tool-footer">
                <a class="tool-link" href="random/">
                    <span>Mở tool</span>
                    <span>↗</span>
                </a>
                <a class="tool-button" href="random/">
                    Quay ngay
                </a>
            </div>
        </div>

        <div class="tool-card">
            <div class="tool-row-header">
                <div class="tool-main">
                    <div class="tool-icon">🎣</div>
                    <div>
                        <div class="tool-title">Câu cá random</div>
                        <div class="tool-meta">
                            <span class="tool-meta-item">Hồ cá 5 hàng</span>
                            <span class="tool-meta-item">Cá bơi trái ↔ phải</span>
                            <span class="tool-meta-item">Chống spam 10s</span>
                        </div>
                    </div>
                </div>
                <div class="tool-tag">/cauca</div>
            </div>
            <div class="tool-desc">
                Mỗi dòng là một con cá / kết quả. Cá bơi lộn xộn trong hồ, câu trúng cá sẽ bơi lên người câu & hiển thị popup.
            </div>
            <div class="tool-footer">
                <a class="tool-link" href="cauca/">
                    <span>Mở tool</span>
                    <span>↗</span>
                </a>
                <a class="tool-button" href="cauca/">
                    Câu cá
                </a>
            </div>
        </div>

        <div class="tool-card">
            <div class="tool-row-header">
                <div class="tool-main">
                    <div class="tool-icon">✨</div>
                    <div>
                        <div class="tool-title">Tool sắp ra mắt</div>
                        <div class="tool-meta">
                            <span class="tool-meta-item">Chờ đợi nhé</span>
                            <span class="tool-meta-item">Chờ đợi nhé</span>
                        </div>
                    </div>
                </div>
                <div class="tool-tag">/...</div>
            </div>
            <div class="tool-desc">
                Chờ đợi là hạnh phách hehehehehehehehhehhee.
            </div>
            <div class="tool-footer">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('toolSearch');
    if (!searchInput) return;

    const cards = Array.from(document.querySelectorAll('.tool-card'));

    function normalize(str) {
        return str
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    searchInput.addEventListener('input', function () {
        const query = normalize(searchInput.value.trim());

        cards.forEach(function (card) {
            const titleEl = card.querySelector('.tool-title');
            const metaEls = card.querySelectorAll('.tool-meta-item');

            const title = titleEl ? normalize(titleEl.textContent) : '';

            let metaText = '';
            metaEls.forEach(m => {
                metaText += ' ' + normalize(m.textContent);
            });

            if (
                query === '' ||
                title.includes(query) ||
                metaText.includes(query)
            ) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>

<?php
require_once __DIR__ . '/layout/footer.php';
