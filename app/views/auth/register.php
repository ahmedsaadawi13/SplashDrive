<!-- FILE: /app/views/auth/register.php -->
<div class="auth-page">
    <div class="auth-container">
        <h1>Create Your Account</h1>

        <form method="POST" action="/auth/register" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">

            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required class="form-control">
            </div>

            <div class="form-group">
                <label for="organization_name">Organization Name</label>
                <input type="text" id="organization_name" name="organization_name" required class="form-control">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6" class="form-control">
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6" class="form-control">
            </div>

            <div class="form-group">
                <label for="plan_id">Choose a Plan</label>
                <select id="plan_id" name="plan_id" class="form-control">
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['id']; ?>">
                            <?php echo htmlspecialchars($plan['name']); ?> - $<?php echo number_format($plan['price_monthly'], 2); ?>/month
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </div>

            <div class="form-footer">
                <p>Already have an account? <a href="/auth/login">Sign In</a></p>
            </div>
        </form>
    </div>
</div>
