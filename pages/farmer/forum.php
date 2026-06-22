<?php
$path_prefix = '../../';

session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../pages/auth/login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role     = $_SESSION['role'];
$success  = "";
$error    = "";

// AUTO-CREATE TABLE FORUM_COMMENTS
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `forum_comments` (
    `comment_id` int NOT NULL AUTO_INCREMENT,
    `forum_id` int NOT NULL,
    `user_id` int NOT NULL,
    `comment_content` text NOT NULL,
    `parent_comment_id` int DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`comment_id`),
    CONSTRAINT `fk_forum_comments_discussion` FOREIGN KEY (`forum_id`) REFERENCES `forum_discussions` (`forum_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_forum_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_forum_comments_parent` FOREIGN KEY (`parent_comment_id`) REFERENCES `forum_comments` (`comment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;");

// BUAT DISKUSI BARU 
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

// TAMBAH KOMENTAR BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $forum_id = (int)$_POST['forum_id'];
    $content  = mysqli_real_escape_string($conn, trim($_POST['comment_content'] ?? ''));

    if (empty($content)) {
        $error = "Komentar tidak boleh kosong!";
    } else {
        $result = mysqli_query($conn,
            "INSERT INTO forum_comments (forum_id, user_id, comment_content, parent_comment_id)
             VALUES ($forum_id, $user_id, '$content', NULL)"
        );
        if ($result) {
            $success = "Komentar berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan komentar: " . mysqli_error($conn);
        }
    }
}

// TAMBAH BALASAN KOMENTAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $forum_id          = (int)$_POST['forum_id'];
    $parent_comment_id = (int)$_POST['parent_comment_id'];
    $content           = mysqli_real_escape_string($conn, trim($_POST['comment_content'] ?? ''));

    if (empty($content)) {
        $error = "Balasan tidak boleh kosong!";
    } else {
        $result = mysqli_query($conn,
            "INSERT INTO forum_comments (forum_id, user_id, comment_content, parent_comment_id)
             VALUES ($forum_id, $user_id, '$content', $parent_comment_id)"
        );
        if ($result) {
            $success = "Balasan berhasil dikirim!";
        } else {
            $error = "Gagal mengirim balasan: " . mysqli_error($conn);
        }
    }
}

// AMBIL SEMUA DISKUSI
$discussions = mysqli_query($conn,
    "SELECT fd.*, u.username, u.role
     FROM forum_discussions fd
     JOIN users u ON fd.user_id = u.user_id
     ORDER BY fd.created_at DESC"
);

// AMBIL SEMUA KOMENTAR & BALASAN
$comments_query = mysqli_query($conn,
    "SELECT fc.*, u.username, u.role
     FROM forum_comments fc
     JOIN users u ON fc.user_id = u.user_id
     ORDER BY fc.created_at ASC"
);

$comments_by_forum = [];
if ($comments_query) {
    while ($c = mysqli_fetch_assoc($comments_query)) {
        $fid = $c['forum_id'];
        if (!isset($comments_by_forum[$fid])) {
            $comments_by_forum[$fid] = [];
        }
        $comments_by_forum[$fid][] = $c;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php $page_title = 'Forum Diskusi'; include '../../component/layout/head.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<?php $current_page = 'forum'; include '../../component/layout/navbar.php'; ?>

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
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-5 p-3 bg-red-50 text-red-800 rounded-xl border border-red-200 text-sm">
        <svg class="inline-block w-4 h-4 mr-1.5 align-middle text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> <?= htmlspecialchars($error) ?>
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
    <div id="discussions-list" class="space-y-4">
        <?php while ($d = mysqli_fetch_assoc($discussions)): 
            $fid = $d['forum_id'];
            $c_list = $comments_by_forum[$fid] ?? [];
            $total_comments = count($c_list);

            $parent_comments = [];
            $replies = [];
            foreach ($c_list as $c) {
                if (empty($c['parent_comment_id'])) {
                    $parent_comments[] = $c;
                } else {
                    $replies[$c['parent_comment_id']][] = $c;
                }
            }
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5
                    hover:shadow-md transition card-hover" id="topic-<?= $fid ?>">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <h3 class="text-base font-bold text-siagri-dark leading-snug mb-2">
                        <?= htmlspecialchars($d['post_title']) ?>
                    </h3>
                    
                    <p id="content-<?= $fid ?>" class="text-sm text-gray-600 leading-relaxed mb-3 line-clamp-3">
                        <?= nl2br(htmlspecialchars($d['post_content'])) ?>
                    </p>

                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-50">
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
                                    <?= htmlspecialchars($d['username']) ?> (<?= $d['role'] ?>)
                                </span>
                            </span>
                            <span>•</span>
                            <span><?= date('d M Y, H:i', strtotime($d['created_at'])) ?></span>
                        </div>

                        <!-- Toggle Button -->
                        <button type="button" onclick="toggleTopicComments(<?= $fid ?>)" 
                                id="btn-toggle-<?= $fid ?>"
                                class="flex items-center gap-1.5 text-xs font-semibold text-siagri-green hover:underline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span><?= $total_comments ?> Komentar</span>
                        </button>
                    </div>

                    <!-- COLLAPSIBLE COMMENTS SECTION -->
                    <div id="comments-sec-<?= $fid ?>" class="hidden mt-6 pt-5 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-700 mb-4">Diskusi & Komentar</h4>
                        
                        <!-- List of comments -->
                        <div class="space-y-4 mb-6">
                            <?php if (count($parent_comments) === 0): ?>
                                <p class="text-xs text-gray-400 italic">Belum ada tanggapan. Silakan berikan tanggapan pertama Anda!</p>
                            <?php else: ?>
                                <?php foreach ($parent_comments as $pc): ?>
                                    <!-- Level 1 Comment -->
                                    <?php 
                                    $pc_is_expert = ($pc['role'] === 'Expert');
                                    $pc_bg = $pc_is_expert ? 'bg-blue-50/50 border border-blue-100' : 'bg-gray-50';
                                    ?>
                                    <div class="rounded-xl p-3.5 <?= $pc_bg ?>">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-gray-800"><?= htmlspecialchars($pc['username']) ?></span>
                                                <?php if ($pc_is_expert): ?>
                                                    <span class="bg-blue-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Pakar Pertanian</span>
                                                <?php else: ?>
                                                    <span class="text-[10px] text-gray-400 bg-gray-200/60 px-1.5 py-0.5 rounded"><?= $pc['role'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-[10px] text-gray-400"><?= date('d M Y, H:i', strtotime($pc['created_at'])) ?></span>
                                        </div>
                                        <p class="text-sm text-gray-700 leading-relaxed">
                                            <?= nl2br(htmlspecialchars($pc['comment_content'])) ?>
                                        </p>
                                        
                                        <!-- Reply Button -->
                                        <div class="mt-2 text-right">
                                            <button onclick="toggleReplyForm(<?= $pc['comment_id'] ?>)" 
                                                    class="text-xs font-semibold text-siagri-green hover:underline">
                                                Balas
                                            </button>
                                        </div>

                                        <!-- Reply Form (Inline, hidden by default) -->
                                        <form method="POST" id="reply-form-<?= $pc['comment_id'] ?>" class="hidden mt-3 bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                                            <input type="hidden" name="action" value="add_reply">
                                            <input type="hidden" name="forum_id" value="<?= $fid ?>">
                                            <input type="hidden" name="parent_comment_id" value="<?= $pc['comment_id'] ?>">
                                            <textarea name="comment_content" required rows="2" 
                                                      placeholder="Tulis balasan Anda untuk <?= htmlspecialchars($pc['username']) ?>..." 
                                                      class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:outline-none focus:border-siagri-green resize-none"></textarea>
                                            <div class="flex justify-end gap-2 mt-2">
                                                <button type="button" onclick="toggleReplyForm(<?= $pc['comment_id'] ?>)" 
                                                        class="text-[11px] text-gray-400 hover:text-gray-600 font-medium">Batal</button>
                                                <button type="submit" name="submit_reply" 
                                                        class="bg-siagri-dark text-white text-[11px] font-bold px-3 py-1 rounded hover:bg-siagri-green transition">Kirim</button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Level 2 Comments (Replies) -->
                                    <?php if (isset($replies[$pc['comment_id']])): ?>
                                        <div class="ml-8 mt-2 space-y-2 border-l-2 border-gray-200 pl-4">
                                            <?php foreach ($replies[$pc['comment_id']] as $rc): ?>
                                                <?php 
                                                $rc_is_expert = ($rc['role'] === 'Expert');
                                                $rc_bg = $rc_is_expert ? 'bg-blue-50/40 border border-blue-50' : 'bg-gray-50/70';
                                                ?>
                                                <div class="rounded-xl p-3 <?= $rc_bg ?>">
                                                    <div class="flex items-start justify-between mb-1">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-xs font-bold text-gray-800"><?= htmlspecialchars($rc['username']) ?></span>
                                                            <?php if ($rc_is_expert): ?>
                                                                <span class="bg-blue-600 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wider">Pakar Pertanian</span>
                                                            <?php else: ?>
                                                                <span class="text-[9px] text-gray-400 bg-gray-200/50 px-1 py-0.5 rounded"><?= $rc['role'] ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="text-[9px] text-gray-400"><?= date('d M Y, H:i', strtotime($rc['created_at'])) ?></span>
                                                    </div>
                                                    <p class="text-xs text-gray-700 leading-relaxed">
                                                        <?= nl2br(htmlspecialchars($rc['comment_content'])) ?>
                                                    </p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Write Comment Form -->
                        <form method="POST" class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <input type="hidden" name="action" value="add_comment">
                            <input type="hidden" name="forum_id" value="<?= $fid ?>">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Tambahkan Komentar</label>
                            <textarea name="comment_content" required rows="3" 
                                      placeholder="Tulis komentar atau tanggapan Anda..." 
                                      class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:outline-none focus:border-siagri-green resize-y min-h-[80px] bg-white"></textarea>
                            <div class="flex justify-end mt-2">
                                <button type="submit" name="submit_comment" 
                                        class="bg-siagri-dark text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-siagri-green transition">
                                    Kirim Komentar
                                </button>
                            </div>
                        </form>
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

<script src="../../Assets/js/forum.js?v=<?= time() ?>"></script>
<?php include '../../component/layout/footer.php'; ?>

</body>
</html>
