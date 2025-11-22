<!-- FILE: /app/views/auth/login.php -->
<div class="auth-page">
    <div class="auth-container">
        <h1>Sign In to <?php echo APP_NAME; ?></h1>

        <form method="POST" action="/auth/login" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required class="form-control">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required class="form-control">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>

            <div class="form-footer">
                <p>Don't have an account? <a href="/auth/register">Sign Up</a></p>
            </div>
        </form>

        <div class="demo-credentials">
            <h3>Demo Credentials</h3>
            <p><strong>Platform Admin:</strong> admin@splashdrive.com / password</p>
            <p><strong>Tenant Admin:</strong> john@techcorp.com / password</p>
            <p><strong>Regular User:</strong> sarah@techcorp.com / password</p>
        </div>
    </div>
</div>
