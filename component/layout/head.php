<?php
$page_title = $page_title ?? 'SiAGRI';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="SiAGRI — Platform digital agrikultur untuk petani modern Indonesia">
<title><?= htmlspecialchars($page_title) ?> - SiAGRI</title>

<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'siagri-dark':    '#164a41',
                'siagri-green':   '#4d774e',
                'siagri-gold':    '#f1b24a',
                'siagri-light':   '#f0f7f4',
                'siagri-slate':   '#2d3748',
                'siagri-muted':   '#718096',
                'siagri-border':  '#e2e8f0',
                'siagri-surface': '#ffffff',
            },
            fontFamily: {
                'poppins': ['Poppins', 'sans-serif'],
            }
        }
    }
}
</script>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Global Styles -->
<style>
    * { font-family: 'Poppins', sans-serif; }
    html { scroll-behavior: smooth; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #f0f7f4; }
    ::-webkit-scrollbar-thumb { background: #4d774e; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #164a41; }
    .btn-lift { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .btn-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(22, 74, 65, 0.25); }
    .btn-lift:active { transform: translateY(0); }
    .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(22, 74, 65, 0.12); }
    .fade-in { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?php if (!empty($extra_head)) echo $extra_head; ?>
