# SP Treinamentos - Server Setup Guide

## Requirements
- **OS**: Ubuntu 22.04 LTS
- **Hardware**: 4vCPU, 8GB RAM, NVMe (Hetzner recommended)
- **Timezone**: America/São Paulo

## 1. Initial Server Setup & Update
```bash
# Update packages
apt update && apt upgrade -y

# Set Timezone
timedatectl set-timezone America/Sao_Paulo

# Install essential tools
apt install -y curl wget unzip git zip software-properties-common
```

## 2. Install Nginx
```bash
apt install -y nginx
systemctl enable nginx
systemctl start nginx
```

## 3. Install PHP 8.1 & Extensions
```bash
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y php8.1-fpm php8.1-cli php8.1-mysql php8.1-curl php8.1-gd \
php8.1-mbstring php8.1-xml php8.1-zip php8.1-bcmath php8.1-soap \
php8.1-intl php8.1-readline php8.1-imagick php8.1-redis

# Verify installation
php -v
```

## 4. Install MariaDB (MySQL)
```bash
apt install -y mariadb-server
systemctl enable mariadb
systemctl start mariadb

# Secure installation
mysql_secure_installation
```
**Database Setup:**
```sql
CREATE DATABASE wp_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sp_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON wp_lms.* TO 'sp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 5. Install Redis
```bash
apt install -y redis-server
systemctl enable redis-server
```

## 6. Install WordPress
```bash
cd /var/www/html
wget https://wordpress.org/latest.zip
unzip latest.zip
mv wordpress/* .
rm -rf wordpress latest.zip
chown -R www-data:www-data /var/www/html
```

## 7. Configure Nginx
Copy the provided `nginx.conf` to `/etc/nginx/sites-available/sp-lms` and link it:
```bash
ln -s /etc/nginx/sites-available/sp-lms /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

## 8. Certbot (SSL)
```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d curso.seudominio.com -d cliente1.seudominio.com
```
