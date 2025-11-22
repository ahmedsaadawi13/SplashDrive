<!-- FILE: /app/views/tenants/create.php -->
<div class="tenants-page">
    <h1>Create Tenant</h1>
    <form method="POST" action="/tenants/create">
        <input type="hidden" name="csrf_token" value="<?php echo Session::getCsrfToken(); ?>">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required class="form-control">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required class="form-control">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="/tenants" class="btn btn-secondary">Cancel</a>
    </form>
</div>
