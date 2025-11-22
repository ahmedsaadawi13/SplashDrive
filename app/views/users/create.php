<!-- FILE: /app/views/users/create.php -->
<div class="users-page">
    <h1>Create User</h1>

    <form method="POST" action="/users/create">
        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required class="form-control">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required class="form-control">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="6" class="form-control">
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" class="form-control">
                <option value="<?php echo ROLE_USER; ?>">User</option>
                <option value="<?php echo ROLE_TENANT_ADMIN; ?>">Tenant Admin</option>
                <option value="<?php echo ROLE_READ_ONLY; ?>">Read Only</option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
