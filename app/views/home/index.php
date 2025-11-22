<!-- FILE: /app/views/home/index.php -->
<div class="home-page">
    <div class="hero">
        <h1>Welcome to <?php echo APP_NAME; ?></h1>
        <p class="lead">The secure cloud storage solution for your organization</p>
        <div class="hero-actions">
            <a href="/auth/register" class="btn btn-primary btn-lg">Get Started Free</a>
            <a href="/auth/login" class="btn btn-secondary btn-lg">Sign In</a>
        </div>
    </div>

    <div class="features">
        <h2>Features</h2>
        <div class="feature-grid">
            <div class="feature">
                <h3>Secure File Storage</h3>
                <p>Store your files securely with industry-standard encryption</p>
            </div>
            <div class="feature">
                <h3>Easy Sharing</h3>
                <p>Share files and folders with public links</p>
            </div>
            <div class="feature">
                <h3>Team Collaboration</h3>
                <p>Work together with your team members</p>
            </div>
            <div class="feature">
                <h3>Activity Tracking</h3>
                <p>Monitor all file operations with detailed activity logs</p>
            </div>
        </div>
    </div>

    <div class="pricing">
        <h2>Pricing Plans</h2>
        <div class="plans-grid">
            <?php foreach ($plans as $plan): ?>
                <div class="plan-card">
                    <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                    <p class="plan-price">$<?php echo number_format($plan['price_monthly'], 2); ?>/month</p>
                    <ul class="plan-features">
                        <?php
                        $features = explode(',', $plan['features']);
                        foreach ($features as $feature):
                        ?>
                            <li><?php echo htmlspecialchars(trim($feature)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="/auth/register?plan=<?php echo $plan['id']; ?>" class="btn btn-primary">Choose Plan</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
