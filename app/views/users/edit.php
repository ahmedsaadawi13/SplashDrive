<!-- FILE: /app/views/users/edit.php -->
<div class="users-page">
    <h1>Edit User</h1>

    <form method="POST" action="/users/edit/<?php echo $user['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" class="form-control">
                <option value="<?php echo ROLE_USER; ?>" <?php echo $user['role'] === ROLE_USER ? 'selected' : ''; ?>>User</option>
                <option value="<?php echo ROLE_TENANT_ADMIN; ?>" <?php echo $user['role'] === ROLE_TENANT_ADMIN ? 'selected' : ''; ?>>Tenant Admin</option>
                <option value="<?php echo ROLE_READ_ONLY; ?>" <?php echo $user['role'] === ROLE_READ_ONLY ? 'selected' : ''; ?>>Read Only</option>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="suspended" <?php echo $user['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
