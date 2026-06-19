<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login-page.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role     = $_SESSION['role'];
$success  = "";
$error    = "";

// ─── BUAT DISKUSI BARU ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_topic'])) {
    $title   = mysqli_real_escape_string($conn, trim($_POST['post_title'] ?? ''));
    $content = mysqli_real_escape_string($conn, trim($_POST['post_content'] ?? ''));

    if (empty($title) || empty($content)) {
        $error = "Judul dan isi diskusi wajib diisi!";
    } else {
        $result = mysqli_query($conn,
            "INSERT INTO forum_discussions (user_id, post_title, post_content)
             VALUES ($user_id, '$title', '$content')"
        );
        if ($result) {
            $success = "Diskusi berhasil dibuat!";
        } else {
            $error = "Gagal membuat diskusi: " . mysqli_error($conn);
        }
    }
}

// ─── AMBIL SEMUA DISKUSI ──────────────────────────────────────────────────────
$discussions = mysqli_query($conn,
    "SELECT fd.*, u.username, u.role
     FROM forum_discussions fd
     JOIN users u ON fd.user_id = u.user_id
     ORDER BY fd.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $page_title = 'Forum Diskusi'; include 'component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'forum'; include 'component/layout/navbar.php'; ?>

<div class="max-w-6xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-siagri-dark">Forum Diskusi</h1>
            <p class="text-gray-400 text-sm mt-0.5">Tanyakan, diskusikan, dan berbagi pengalaman pertanian</p>
        </div>
        <button id="btn-new" onclick="toggleForumForm(true)"
                class="bg-siagri-dark text-white px-5 py-2.5 rounded-xl text-sm
                       font-semibold hover:bg-siagri-green transition btn-lift">
            + Diskusi Baru
        </button>
    </div>

    <!-- Notifikasi -->
    <?php if ($success): ?>
    <div class="mb-5 p-3 bg-green-50 text-green-800 rounded-xl border border-green-200 text-sm">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-5 p-3 bg-red-50 text-red-800 rounded-xl border border-red-200 text-sm">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Form Diskusi Baru -->
    <div id="form-new" class="hidden mb-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-siagri-dark mb-4">Buat Diskusi Baru</h3>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Diskusi</label>
                <input type="text" name="post_title" required placeholder="Contoh: Cara mengatasi hama wereng..."
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                              focus:outline-none focus:border-siagri-dark transition">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Isi Diskusi</label>
                <textarea name="post_content" required rows="10"
                          placeholder="Jelaskan pertanyaan atau topik diskusi kamu..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                 focus:outline-none focus:border-siagri-dark transition resize-y min-h-[250px]"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" name="create_topic"
                        class="bg-siagri-dark text-white px-6 py-2.5 rounded-xl text-sm
                               font-semibold hover:bg-siagri-green transition">
                    Kirim
                </button>
                <button type="button"
                        onclick="toggleForumForm(false)"
                        class="text-gray-500 hover:text-gray-700 text-sm transition">
                    Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar Diskusi -->
    <?php if (mysqli_num_rows($discussions) > 0): ?>
    <div class="space-y-4">
        <?php while ($d = mysqli_fetch_assoc($discussions)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5
                    hover:shadow-md transition card-hover">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-base font-bold text-siagri-dark leading-snug mb-2">
                        <?= htmlspecialchars($d['post_title']) ?>
                    </h3>
                    <p class="text-sm text-gray-600 leading-relaxed mb-3 line-clamp-3">
                        <?= nl2br(htmlspecialchars($d['post_content'])) ?>
                    </p>
                    <div class="flex items-center gap-3 text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <?php
                            $badge_colors = [
                                'Farmer' => 'bg-green-100 text-green-700',
                                'Kiosk'  => 'bg-yellow-100 text-yellow-700',
                                'Expert' => 'bg-blue-100 text-blue-700',
                                'Admin'  => 'bg-red-100 text-red-700',
                            ];
                            $bc = $badge_colors[$d['role']] ?? 'bg-gray-100 text-gray-600';
                            ?>
                            <span class="<?= $bc ?> px-2 py-0.5 rounded-full font-semibold text-xs">
                                <?= htmlspecialchars($d['username']) ?>
                            </span>
                        </span>
                        <span>•</span>
                        <span><?= date('d M Y, H:i', strtotime($d['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div id="empty-state" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <h3 class="text-lg font-bold text-siagri-dark mb-2">Belum Ada Diskusi</h3>
        <p class="text-gray-400 text-sm mb-6">Jadilah yang pertama memulai diskusi!</p>
        <button onclick="toggleForumForm(true); window.scrollTo({top:0, behavior:'smooth'})"
                class="bg-siagri-gold text-siagri-dark px-6 py-2.5 rounded-xl text-sm
                       font-bold hover:bg-yellow-400 transition">
            Mulai Diskusi
        </button>
    </div>
    <?php endif; ?>

</div>

<script src="assets/js/forum.js"></script>
<?php include 'component/layout/footer.php'; ?>

</body>
</html>
