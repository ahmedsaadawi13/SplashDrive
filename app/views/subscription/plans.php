<!-- FILE: /app/views/subscription/plans.php -->
<div class="subscription-page">
    <h1>Choose a Plan</h1>

    <div class="plans-grid">
        <?php foreach ($plans as $plan): ?>
            <div class="plan-card <?php echo ($currentSubscription && $currentSubscription['plan_id'] == $plan['id']) ? 'current-plan' : ''; ?>">
                <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                <p class="plan-price">$<?php echo number_format($plan['price_monthly'], 2); ?>/month</p>
                <p class="plan-price-yearly">$<?php echo number_format($plan['price_yearly'], 2); ?>/year</p>
                <ul class="plan-features">
                    <?php
                    $features = explode(',', $plan['features']);
                    foreach ($features as $feature):
                    ?>
                        <li><?php echo htmlspecialchars(trim($feature)); ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($currentSubscription && $currentSubscription['plan_id'] == $plan['id']): ?>
                    <button class="btn btn-secondary" disabled>Current Plan</button>
                <?php else: ?>
                    <form method="POST" action="/subscription/change">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <div class="form-group">
                            <select name="billing_cycle" class="form-control">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Select Plan</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="/subscription" class="btn btn-secondary">Back to Subscription</a>
</div>
