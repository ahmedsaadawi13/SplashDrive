<!-- FILE: /app/views/tenants/edit.php -->
<div class="tenants-page">
    <h1>Edit Tenant</h1>
    <form method="POST" action="/tenants/edit/<?php echo $tenant['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($tenant['name']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($tenant['email']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($tenant['phone']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control"><?php echo htmlspecialchars($tenant['address']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="active" <?php echo $tenant['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $tenant['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="suspended" <?php echo $tenant['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="/tenants" class="btn btn-secondary">Cancel</a>
    </form>
</div>
