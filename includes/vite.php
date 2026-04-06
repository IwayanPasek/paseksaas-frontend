<?php
/**
 * vite.php — Shared Vite compiled asset loader.
 * Returns the CSS and JS file paths from the dist directory.
 */

require_once __DIR__ . '/config.php';

function getViteAssets(): array {
    $css = '';
    $js  = '';

    if (is_dir(DIST_DIR)) {
        foreach (scandir(DIST_DIR) as $file) {
            if (str_ends_with($file, '.css')) $css = 'react-app/dist/assets/' . $file;
            if (str_ends_with($file, '.js'))  $js  = 'react-app/dist/assets/' . $file;
        }
    }

    return ['css' => $css, 'js' => $js];
}

/**
 * Render the standard HTML shell for a React page.
 */
function renderReactShell(string $title, string $windowVar, array $data): void {
    $assets = getViteAssets();
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script>window.<?= $windowVar ?> = <?= $jsonData ?>;</script>
    <?php if ($assets['css']): ?><link rel="stylesheet" href="<?= $assets['css'] ?>"><?php endif; ?>
    <style>body { background-color: #fafafa; margin: 0; font-family: 'Inter', system-ui, sans-serif; }</style>
</head>
<body>
    <div id="root">
        <?php if (!$assets['js']): ?>
            <div style="text-align:center;padding:50px;margin-top:20vh;font-family:sans-serif;color:#737373;">
                <h2>Aplikasi React belum di-build.</h2>
                <p>Jalankan <code>npm run build</code> di folder react-app.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($assets['js']): ?><script type="module" src="<?= $assets['js'] ?>"></script><?php endif; ?>
</body>
</html>
    <?php
}
