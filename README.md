# Menuscanorder

> ⚠️ **ARCHIVED PROJECT**: This project has been archived and migrated to Next.js. Please see the new repository: [qrorder](https://github.com/hiufungleung/qrorder)

## Introduction
This is a SaaS platform designed specifically for restaurants, cafes, and coffee shops to streamline their ordering process. The platform allows businesses to create digital menus with categories, items, and prices, then generates unique QR codes for each table that customers can scan to place orders.

This was an assessment project for INFS7202 - Web Information Systems at the University of Queensland, Semester 1 2024. It achieved a High Distinction (Grade Point: 7/7).

## Docker Deployment

### Pre-built Docker Image
The application is available as a Docker image:
```
docker pull hiufungleung/menuscanorder:latest
```

### Quick Start
```bash
docker run -d \
  --name menuscanorder \
  -p 8080:80 \
  -e CI_ENVIRONMENT=production \
  -e app.baseURL=http://your-domain.com \
  -e database.default.hostname=your-db-host \
  -e database.default.database=your-db-name \
  -e database.default.username=your-db-user \
  -e database.default.password=your-db-password \
  -e database.default.port=3306 \
  -e database.default.DBDriver=MySQLi \
  -e encryption.key=your-32-char-encryption-key \
  your-dockerhub-username/menuscanorder:latest
```

### Run Development locally
```Bash
php spark serve
```
Create a `.env` file from the template `env` to set up environment variables.

### Environment Variables
| Variable | Description | Required |
|----------|-------------|----------|
| `CI_ENVIRONMENT` | Set to `production` | Yes |
| `app.baseURL` | Your application's base URL | Yes |
| `database.default.hostname` | MySQL server hostname | Yes |
| `database.default.database` | Database name | Yes |
| `database.default.username` | Database username | Yes |
| `database.default.password` | Database password | Yes |
| `database.default.port` | Database port (default: 3306) | Yes |
| `database.default.DBDriver` | Set to `MySQLi` | Yes |
| `encryption.key` | 32-character encryption key | Yes |

Use the command to generate `encryption.key`
```Bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

### Build From Source
1. Clone the repository
2. Build the Docker image:
```bash
docker build -t menuscanorder:1.0 .
```

## Database Setup
1. Create a MySQL database
2. Import the database schema from `setup/DatabaseBuild.sql`
3. The default admin credentials are:
   - Email: `root@root.root`
   - Password: `8964`
   - It shows in the final line of the file for faster setup, since no entrance is provided to create an admin account directly.

## Architecture
- **Backend**: CodeIgniter 4 (PHP 8.1)
- **Database**: MySQL
- **Web Server**: nginx + PHP-FPM
- **Container**: Alpine Linux with multi-stage build

## Files Structure
- `setup/` - Database schema and configuration files
- `app/` - CodeIgniter application code
- `public/` - Web accessible files
- `writable/` - Cache, logs, and session files
