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
<link rel="stylesheet" href="<?= $path_prefix ?>assets/css/global.css"> 


<?php if (!empty($extra_head)) echo $extra_head; ?>
