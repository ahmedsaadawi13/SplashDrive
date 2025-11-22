# SplashDrive

A complete multi-tenant SaaS file storage platform built with pure PHP and MySQL. No frameworks, clean architecture, beginner-friendly code.

## Features

- **Multi-Tenant Architecture**: Isolated data per organization
- **User Management**: Role-based access control (Platform Admin, Tenant Admin, User, Read-Only)
- **File Storage**: Secure file upload, download, and organization
- **Folder Hierarchy**: Nested folder structure with breadcrumb navigation
- **Trash/Recycle Bin**: Soft delete with restore capability
- **Public Sharing**: Generate secure shareable links with expiration
- **Subscription Management**: Multiple plans with quota enforcement
- **Billing System**: Invoice and payment tracking
- **Activity Logging**: Comprehensive audit trail
- **REST API**: Programmatic access with API key authentication
- **Search & Filter**: Find files by name, type, and date
- **Storage Quota**: Enforce limits per subscription plan
- **Responsive Design**: Mobile-friendly interface

## Requirements

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHP Extensions: PDO, PDO_MySQL, mbstring, fileinfo

## Installation

### 1. Clone or Download the Repository

```bash
git clone https://github.com/yourusername/splashdrive.git
cd splashdrive
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and configure your database credentials:

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=splashdrive
DB_USER=root
DB_PASS=your_password
```

### 3. Import Database

```bash
mysql -u root -p < database.sql
```

Or using phpMyAdmin:
1. Create a new database named `splashdrive`
2. Import the `database.sql` file

### 4. Set Permissions

```bash
chmod -R 755 /path/to/splashdrive
chmod -R 777 /path/to/splashdrive/storage
```

### 5. Configure Web Server

#### Apache Configuration

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName splashdrive.local
    DocumentRoot /path/to/splashdrive/public

    <Directory /path/to/splashdrive/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashdrive-error.log
    CustomLog ${APACHE_LOG_DIR}/splashdrive-access.log combined
</VirtualHost>
```

Enable mod_rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name splashdrive.local;
    root /path/to/splashdrive/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 6. Update Hosts File (Local Development)

```bash
sudo nano /etc/hosts
```

Add:
```
127.0.0.1   splashdrive.local
```

### 7. Access the Application

Open your browser and navigate to:
```
http://splashdrive.local
```

## Default Login Credentials

### Platform Admin
- Email: `admin@splashdrive.com`
- Password: `password`

### Tenant Admin (TechCorp Solutions)
- Email: `john@techcorp.com`
- Password: `password`

### Regular User
- Email: `sarah@techcorp.com`
- Password: `password`

### Read-Only User
- Email: `emily@techcorp.com`
- Password: `password`

### Tenant Admin (Creative Agency Inc)
- Email: `alice@creativeagency.com`
- Password: `password`

**Important**: Change these passwords immediately in production!

## Project Structure

```
splashdrive/
├── app/
│   ├── controllers/    # Application controllers
│   ├── models/         # Data models
│   ├── views/          # View templates
│   └── core/           # Core framework classes
├── config/             # Configuration files
├── public/             # Public web root
│   ├── index.php       # Application entry point
│   └── assets/         # CSS, JS, images
├── storage/            # File uploads
│   └── uploads/        # Tenant file storage
├── tests/              # Test scripts
├── database.sql        # Database schema and seed data
├── .env.example        # Environment template
└── README.md           # This file
```

## API Documentation

The SplashDrive API allows programmatic access to file and folder operations using API key authentication.

### Authentication

All API requests require an API key in the `X-API-KEY` header:

```bash
X-API-KEY: your_api_key_here
```

You can find your API key in the database `api_keys` table or create a new one programmatically.

### API Endpoints

#### 1. List Files and Folders

**Endpoint**: `GET /api/files/list`

**Parameters**:
- `folder_id` (optional): Filter by folder ID
- `page` (optional): Page number (default: 1)
- `limit` (optional): Results per page (default: 50, max: 100)
- `search` (optional): Search by filename

**Example Request**:
```bash
curl -X GET "http://splashdrive.local/api/files/list?folder_id=1&limit=20" \
  -H "X-API-KEY: sk_test_techcorp_1234567890abcdef1234567890abcdef12345678"
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "folders": [
      {
        "id": 1,
        "name": "Documents",
        "path": "/Documents",
        "parent_id": null,
        "subfolder_count": 2,
        "file_count": 5,
        "created_at": "2025-01-15 10:30:00"
      }
    ],
    "files": [
      {
        "id": 1,
        "name": "example.pdf",
        "size": 1048576,
        "size_formatted": "1.00 MB",
        "mime_type": "application/pdf",
        "extension": "pdf",
        "folder_id": 1,
        "uploaded_by": "John Smith",
        "download_count": 5,
        "created_at": "2025-01-15 11:00:00",
        "updated_at": "2025-01-15 11:00:00"
      }
    ]
  },
  "pagination": {
    "page": 1,
    "limit": 20,
    "total_files": 5
  }
}
```

#### 2. List Folders Only

**Endpoint**: `GET /api/folders/list`

**Parameters**:
- `parent_id` (optional): Filter by parent folder ID

**Example Request**:
```bash
curl -X GET "http://splashdrive.local/api/folders/list" \
  -H "X-API-KEY: sk_test_techcorp_1234567890abcdef1234567890abcdef12345678"
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "folders": [
      {
        "id": 1,
        "name": "Documents",
        "path": "/Documents",
        "parent_id": null,
        "subfolder_count": 2,
        "file_count": 5,
        "created_at": "2025-01-15 10:30:00"
      }
    ]
  }
}
```

#### 3. Upload File

**Endpoint**: `POST /api/files/upload`

**Content-Type**: `multipart/form-data`

**Parameters**:
- `file` (required): File to upload
- `folder_id` (optional): Target folder ID

**Example Request**:
```bash
curl -X POST "http://splashdrive.local/api/files/upload" \
  -H "X-API-KEY: sk_test_techcorp_1234567890abcdef1234567890abcdef12345678" \
  -F "file=@/path/to/document.pdf" \
  -F "folder_id=1"
```

**Example Response**:
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "file": {
      "id": 10,
      "name": "document.pdf",
      "size": 2097152,
      "size_formatted": "2.00 MB",
      "mime_type": "application/pdf",
      "extension": "pdf",
      "folder_id": 1
    }
  }
}
```

### Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error description"
}
```

Common HTTP status codes:
- `401`: Unauthorized (invalid or missing API key)
- `403`: Forbidden (quota exceeded, inactive tenant)
- `404`: Not found
- `400`: Bad request (validation error)
- `500`: Server error

## Testing

### Manual Testing Checklist

#### Authentication
- [x] Register new account
- [x] Login with valid credentials
- [x] Login with invalid credentials
- [x] Logout

#### File Operations
- [x] Upload file
- [x] Download file
- [x] Rename file
- [x] Delete file (soft delete)
- [x] Restore file from trash
- [x] Permanently delete file

#### Folder Operations
- [x] Create folder
- [x] Rename folder
- [x] Delete folder (soft delete)
- [x] Restore folder from trash
- [x] Navigate folder hierarchy

#### Sharing
- [x] Create share link
- [x] Access share link
- [x] Delete share link
- [x] Verify link expiration

#### User Management
- [x] Create user
- [x] Edit user
- [x] Delete user
- [x] Test role permissions

#### Subscription & Billing
- [x] View subscription
- [x] Change subscription plan
- [x] View invoices
- [x] Process payment (simulated)

#### Tenant Isolation
- [x] Verify users can only see their tenant's data
- [x] Test cross-tenant access prevention

#### Storage Quotas
- [x] Upload files until quota is reached
- [x] Verify upload blocked when quota exceeded
- [x] Verify warnings at 80% and 90% usage

#### API Testing
- [x] List files with valid API key
- [x] Upload file via API
- [x] Test invalid API key rejection

### Automated Testing

Basic test script:

```php
<?php
// FILE: /tests/basic_test.php
require_once __DIR__ . '/../config/config.php';

// Test database connection
try {
    $db = Database::getInstance();
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

// Test configuration
if (defined('APP_NAME')) {
    echo "✓ Configuration loaded successfully\n";
} else {
    echo "✗ Configuration not loaded\n";
}

// Test upload directory
if (is_writable(UPLOAD_PATH)) {
    echo "✓ Upload directory is writable\n";
} else {
    echo "✗ Upload directory is not writable\n";
}

echo "\nAll basic tests completed.\n";
```

Run tests:
```bash
php tests/basic_test.php
```

## Security Considerations

1. **Password Hashing**: All passwords are hashed using `password_hash()` with bcrypt
2. **CSRF Protection**: All forms include CSRF tokens
3. **SQL Injection**: All queries use prepared statements
4. **File Upload Validation**: File type and size validation
5. **Directory Traversal**: Filename sanitization prevents path traversal
6. **Session Security**: Session regeneration on login
7. **API Authentication**: Secure API key validation

### Production Recommendations

1. Change all default passwords
2. Use HTTPS (configure SSL/TLS)
3. Set `APP_ENV=production` in `.env`
4. Restrict file upload types as needed
5. Implement rate limiting
6. Regular database backups
7. Monitor activity logs
8. Keep PHP and MySQL updated

## Troubleshooting

### Upload Issues

**Problem**: Files not uploading

**Solutions**:
1. Check `upload_max_filesize` and `post_max_size` in `php.ini`
2. Verify `/storage/uploads` has write permissions (777)
3. Check PHP error logs

### Database Connection Issues

**Problem**: Can't connect to database

**Solutions**:
1. Verify database credentials in `.env`
2. Ensure MySQL service is running
3. Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Routing Issues

**Problem**: 404 errors on all pages except home

**Solutions**:
1. Enable Apache `mod_rewrite`: `sudo a2enmod rewrite`
2. Verify `.htaccess` file exists in `/public`
3. Check virtual host `AllowOverride All` directive

## Deployment

### Production Deployment Steps

1. **Prepare Server**
   - Install PHP 7.4+ with required extensions
   - Install MySQL 5.7+
   - Configure Apache/Nginx

2. **Deploy Code**
   ```bash
   git clone https://github.com/yourusername/splashdrive.git /var/www/splashdrive
   cd /var/www/splashdrive
   ```

3. **Configure Environment**
   ```bash
   cp .env.example .env
   nano .env  # Update production values
   ```

4. **Import Database**
   ```bash
   mysql -u root -p < database.sql
   ```

5. **Set Permissions**
   ```bash
   chown -R www-data:www-data /var/www/splashdrive
   chmod -R 755 /var/www/splashdrive
   chmod -R 777 /var/www/splashdrive/storage
   ```

6. **Configure SSL** (recommended)
   ```bash
   sudo certbot --apache -d yourdomain.com
   ```

7. **Test**
   - Access the application
   - Test file upload
   - Verify API endpoints

## License

This project is open-source and available under the MIT License.

## Support

For issues and questions:
- Create an issue on GitHub
- Email: support@splashdrive.com

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Changelog

### Version 1.0.0 (2025-01-15)
- Initial release
- Multi-tenant file storage
- User management with RBAC
- Subscription and billing
- REST API
- Activity logging
- Public sharing
- Trash/recycle bin

---

**Built with ❤️ using Pure PHP & MySQL**
