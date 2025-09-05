# Deployment Setup Instructions

## GitHub Repository Variables (Settings > Secrets and variables > Actions > Variables)

Set the following **Variables**:

```
DOCKER_IMAGE_NAME=your-dockerhub-username/menuscanorder
APP_BASE_URL=https://your-domain.com
APP_PORT=9000
```

## GitHub Repository Secrets (Settings > Secrets and variables > Actions > Secrets)

Set the following **Secrets**:

### Docker Hub
```
DOCKERHUB_USERNAME=your-dockerhub-username
DOCKERHUB_TOKEN=your-dockerhub-access-token
```

### EC2 Connection
```
EC2_HOST=your-ec2-ip-address
EC2_USER=ubuntu
EC2_SSH_KEY=your-private-ssh-key-content
```

### Database (Third-party MySQL)
```
DB_HOSTNAME=your-db-host.com
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
DB_PORT=3306
```

### Application
```
ENCRYPTION_KEY=base64:your-32-character-encryption-key
```

## Application Environment Setup (.env file for local development)

Create a `.env` file in your project root:

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'http://localhost:8080'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = your-db-host.com
database.default.database = your_database_name
database.default.username = your_db_username
database.default.password = your_db_password
database.default.DBDriver = MySQLi
database.default.port = 3306

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
encryption.key = base64:your-32-character-encryption-key
```

## How to Generate Encryption Key

Run this command to generate a secure encryption key:
```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

## EC2 Setup Requirements

1. **Install Docker on EC2:**
```bash
sudo apt update
sudo apt install -y docker.io
sudo systemctl start docker
sudo systemctl enable docker
sudo usermod -aG docker ubuntu
```

2. **Configure nginx on EC2** (if using separate nginx):
```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    location / {
        proxy_pass http://127.0.0.1:9000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## Deployment Process

1. Push code to `main` branch
2. GitHub Actions will:
   - Build Docker image
   - Push to Docker Hub
   - SSH to EC2
   - Pull and run latest image with environment variables

## Manual Deployment on EC2

If needed to deploy manually:
```bash
docker stop menuscanorder-app || true
docker rm menuscanorder-app || true
docker pull your-dockerhub-username/menuscanorder:latest

docker run -d \
  --name menuscanorder-app \
  --restart unless-stopped \
  -p 9000:9000 \
  -e CI_ENVIRONMENT=production \
  -e database.default.hostname=your-db-host.com \
  -e database.default.database=your_database_name \
  -e database.default.username=your_db_username \
  -e database.default.password=your_db_password \
  -e database.default.port=3306 \
  -e app.baseURL=https://your-domain.com \
  -e encryption.key=base64:your-encryption-key \
  your-dockerhub-username/menuscanorder:latest
```