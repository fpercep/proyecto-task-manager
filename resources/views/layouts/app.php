<!DOCTYPE html>
<html lang="es">
<!-- En caso de error, $viewPath y $content no existirán
$viewPath y $content cuelgan de index.php, es decir es dependiente -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        orange: { 50: '#fff7ed', 100: '#ffedd5', 400: '#fb923c', 500: '#f97316', 600: '#ea580c' }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-white h-screen flex flex-col overflow-hidden" style="font-family: 'Inter', sans-serif;">

    <?php include $viewPath . '/components/header.php'; ?>

    <div class="flex flex-1 overflow-hidden">

        <?php include $viewPath . '/components/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-white p-8">
            <div class="max-w-7xl mx-auto">
                <?php
                if (isset($content)) {
                    echo $content;
                }
                ?>
            </div>
        </main>

    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>

</html>