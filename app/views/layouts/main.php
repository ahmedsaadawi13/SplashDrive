<!-- FILE: /app/views/layouts/main.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="app-logo"><a href="/dashboard"><?php echo APP_NAME; ?></a></h1>
                </div>
                <nav class="header-nav">
                    <?php if (Auth::check()): ?>
                        <a href="/dashboard">Dashboard</a>
                        <a href="/files">Files</a>
                        <?php if (Auth::can('manage_users')): ?>
                            <a href="/users">Users</a>
                        <?php endif; ?>
                        <?php if (Auth::can('manage_billing')): ?>
                            <a href="/subscription">Subscription</a>
                            <a href="/billing">Billing</a>
                        <?php endif; ?>
                        <?php if (Auth::can('view_activity_log')): ?>
                            <a href="/activity">Activity</a>
                        <?php endif; ?>
                        <?php if (Auth::isPlatformAdmin()): ?>
                            <a href="/tenants">Tenants</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </nav>
                <div class="header-right">
                    <?php if (Auth::check()): ?>
                        <div class="user-menu">
                            <span class="user-name"><?php echo htmlspecialchars(Auth::user()['name']); ?></span>
                            <span class="user-role">(<?php echo htmlspecialchars(Auth::role()); ?>)</span>
                            <a href="/auth/logout" class="btn btn-sm btn-secondary">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="/auth/login" class="btn btn-sm btn-primary">Login</a>
                        <a href="/auth/register" class="btn btn-sm btn-secondary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars(Session::getFlash('success')); ?>
            </div>
        <?php endif; ?>

        <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars(Session::getFlash('error')); ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="app-main">
            <?php echo $content; ?>
        </main>

        <!-- Footer -->
        <footer class="app-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        </footer>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html>
