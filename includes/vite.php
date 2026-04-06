<?php
/**
 * vite.php — Shared Vite compiled asset loader.
 * Returns the CSS and JS file paths from the dist directory.
 */

require_once __DIR__ . '/config.php';

function getViteAssets(): array {
    $css = [];
    $js  = [];

    if (is_dir(DIST_DIR)) {
        foreach (scandir(DIST_DIR) as $file) {
            if (str_ends_with($file, '.css')) $css[] = '/react-app/dist/assets/' . $file;
            if (str_ends_with($file, '.js'))  $js[]  = '/react-app/dist/assets/' . $file;
        }
    }

    return ['css' => $css, 'js' => $js];
}

/**
 * Render the standard HTML shell for a React page.
 */
function renderReactShell(string $title, string $windowVar, array $data, string $seoDesc = ''): void {
    $assets = getViteAssets();
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $safeTitle = htmlspecialchars($title);
    $safeDesc = htmlspecialchars($seoDesc ?: 'E-Commerce Platform & AI Assistant powered by PasekSaaS');
    $currentUrl = htmlspecialchars("https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $safeTitle ?></title>
    
    <!-- SEO & Open Graph Meta Tags -->
    <meta name="description" content="<?= $safeDesc ?>">
    <meta property="og:title" content="<?= $safeTitle ?>">
    <meta property="og:description" content="<?= $safeDesc ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $currentUrl ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= $safeTitle ?>">
    <meta name="twitter:description" content="<?= $safeDesc ?>">
    
    <script>window.<?= $windowVar ?> = <?= $jsonData ?>;</script>
    <?php foreach ($assets['css'] as $c): ?><link rel="stylesheet" href="<?= $c ?>"><?php endforeach; ?>
    <style>body { background-color: #fafafa; margin: 0; font-family: 'Inter', system-ui, sans-serif; }</style>
</head>
<body>
    <div id="root">
        <?php if (empty($assets['js'])): ?>
            <div style="text-align:center;padding:50px;margin-top:20vh;font-family:sans-serif;color:#737373;">
                <h2>Aplikasi React belum di-build.</h2>
                <p>Jalankan <code>npm run build</code> di folder react-app.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php foreach ($assets['js'] as $j): ?><script type="module" src="<?= $j ?>"></script><?php endforeach; ?>
</body>
</html>
    <?php
}
