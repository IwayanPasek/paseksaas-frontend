<?php
/**
 * vite.php — Shared Vite compiled asset loader.
 * Returns the CSS and JS file paths from the dist directory.
 */

require_once __DIR__ . '/config.php';

function getViteAssets(): array {
    $css = [];
    $js  = [];
    $manifestPath = BASE_DIR . '/react-app/dist/.vite/manifest.json';

    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if ($manifest && isset($manifest['index.html'])) {
            $main = $manifest['index.html'];
            
            // Primary JS - using absolute paths from web root
            if (isset($main['file'])) {
                $js[] = '/react-app/dist/' . $main['file'];
            }
            
            // Primary CSS
            if (isset($main['css'])) {
                foreach ($main['css'] as $file) {
                    $css[] = '/react-app/dist/' . $file;
                }
            }
            
            // Imported chunks
            if (isset($main['imports'])) {
                foreach ($main['imports'] as $import) {
                    if (isset($manifest[$import]['css'])) {
                        foreach ($manifest[$import]['css'] as $file) {
                            $css[] = '/react-app/dist/' . $file;
                        }
                    }
                }
            }
        }
    } else if (is_dir(DIST_DIR)) {
        // Fallback for non-manifest builds
        foreach (scandir(DIST_DIR) as $file) {
            if (str_ends_with($file, '.css')) $css[] = '/react-app/dist/assets/' . $file;
            if (str_ends_with($file, '.js'))  $js[]  = '/react-app/dist/assets/' . $file;
        }
    }

    return ['css' => array_unique($css), 'js' => array_unique($js)];
}

/**
 * Render the standard HTML shell for a React page.
 */
function renderReactShell(string $title, string $windowVar, array $data, string $seoDesc = ''): void {
    $assets = getViteAssets();
    
    // Proactive protection against broken JSON serialization
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    if ($jsonData === false) {
        $jsonData = json_encode(['error' => 'Data serialization failure: ' . json_last_error_msg()]);
    }

    $safeTitle = htmlspecialchars($title);
    $safeDesc = htmlspecialchars($seoDesc ?: 'E-Commerce Platform & AI Assistant powered by PasekSaaS');
    $currentUrl = htmlspecialchars("https://" . ($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
    ?>
<!DOCTYPE html>
<html lang="en">
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
            <div style="text-align:center;padding:50px;margin-top:20vh;font-family:sans-serif;color:#737373;max-width:600px;margin-left:auto;margin-right:auto;border:1px solid #e5e7eb;border-radius:12px;background:#fff;">
                <h2 style="color:#ef4444;">Environment Not Ready</h2>
                <p>The React application build artifacts were not found.</p>
                <p style="font-size:0.9em;color:#9ca3af;">Expected manifest at: <code>react-app/dist/.vite/manifest.json</code></p>
                <div style="margin-top:20px;padding:12px;background:#f9fafb;border-radius:6px;text-align:left;font-family:monospace;">
                    $ npm run build
                </div>
            </div>
        <?php else: ?>
            <!-- React will mount here -->
        <?php endif; ?>
    </div>
    <?php foreach ($assets['js'] as $j): ?>
        <script type="module" crossorigin src="<?= $j ?>"></script>
    <?php endforeach; ?>
</body>
</html>
    <?php
}
