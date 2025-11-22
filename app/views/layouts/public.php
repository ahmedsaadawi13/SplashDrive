<!-- FILE: /app/views/layouts/public.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="public-page">
    <div class="public-container">
        <header class="public-header">
            <h1><?php echo APP_NAME; ?></h1>
        </header>

        <main class="public-main">
            <?php echo $content; ?>
        </main>

        <footer class="public-footer">
            <p>Powered by <?php echo APP_NAME; ?></p>
        </footer>
    </div>
</body>
</html>
