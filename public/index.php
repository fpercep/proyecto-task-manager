<?php
$viewPath = __DIR__ . '/../resources/views';
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$pageFile = $viewPath . '/pages/' . $page . '.php';

ob_start();
if (file_exists($pageFile)) {
    include $pageFile;
} else {
    echo "<p>El archivo <strong>$page.php</strong> no existe en la carpeta pages.</p>";
}
$content = ob_get_clean();

include $viewPath . '/layouts/app.php';