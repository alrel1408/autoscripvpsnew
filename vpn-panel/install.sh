#!/bin/bash

# AlrelShop VPN Panel Installer V.4
# Website Panel untuk Managing VPN Account
# Support Digital Ocean & Custom Server

Green="\e[92;1m"
RED="\033[31m"
YELLOW="\033[33m"
BLUE="\033[36m"
FONT="\033[0m"
GREENBG="\033[42;37m"
REDBG="\033[41;37m"
OK="${Green}✓${FONT}"
ERROR="${RED}✗${FONT}"

clear
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${FONT}"
echo -e "  ${GREEN}AlrelShop VPN Panel Installer V.4${FONT}"
echo -e "  ${BLUE}» Professional VPN Management System${FONT}"
echo -e "  ${BLUE}» Support Multi Server & Digital Ocean API${FONT}"
echo -e "  ${GREEN}Admin: 082285851668${FONT}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${FONT}"
echo ""

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${ERROR} This script must be run as root"
   exit 1
fi

# Check OS
if [[ $(cat /etc/os-release | grep -w ID | head -n1 | sed 's/=//g' | sed 's/"//g' | sed 's/ID//g') == "ubuntu" ]]; then
    echo -e "${OK} OS: Ubuntu $(cat /etc/os-release | grep -w VERSION_ID | head -n1 | sed 's/=//g' | sed 's/"//g' | sed 's/VERSION_ID//g')"
elif [[ $(cat /etc/os-release | grep -w ID | head -n1 | sed 's/=//g' | sed 's/"//g' | sed 's/ID//g') == "debian" ]]; then
    echo -e "${OK} OS: Debian $(cat /etc/os-release | grep -w VERSION_ID | head -n1 | sed 's/=//g' | sed 's/"//g' | sed 's/VERSION_ID//g')"
else
    echo -e "${ERROR} Unsupported OS"
    exit 1
fi

# Get server IP
SERVER_IP=$(curl -s ifconfig.me)
echo -e "${OK} Server IP: $SERVER_IP"

# Update system
echo -e "${OK} Updating system packages..."
apt update -y && apt upgrade -y

# Install required packages
echo -e "${OK} Installing required packages..."
apt install -y apache2 php php-mysql php-curl php-json php-mbstring php-xml php-zip php-gd mysql-server unzip curl git

# Start services
echo -e "${OK} Starting services..."
systemctl start apache2
systemctl start mysql
systemctl enable apache2
systemctl enable mysql

# Configure firewall
echo -e "${OK} Configuring firewall..."
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp
ufw --force enable

# Secure MySQL installation
echo -e "${OK} Setting up MySQL..."
DB_PASS=$(openssl rand -base64 32)
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';"
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -e "FLUSH PRIVILEGES;"

# Create database
echo -e "${OK} Creating database..."
mysql -u root -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS vpn_panel;"

# Download and setup panel files
echo -e "${OK} Setting up panel files..."
cd /var/www/html
rm -rf *

# Clone from GitHub or copy local files
if [ -d "/tmp/vpn-panel" ]; then
    cp -r /tmp/vpn-panel/* .
else
    # Download from GitHub (adjust URL when uploaded)
    echo -e "${OK} Downloading panel files..."
    # git clone https://github.com/alrel1408/vpn-panel.git .
    echo "Please upload panel files manually for now"
fi

# Set permissions
chown -R www-data:www-data /var/www/html/
chmod -R 755 /var/www/html/
chmod -R 777 /var/www/html/config/

# Update database config
if [ -f "config/database.php" ]; then
    sed -i "s/AlrelShop2024!/$DB_PASS/g" config/database.php
fi

# Configure Apache
echo -e "${OK} Configuring Apache..."
cat > /etc/apache2/sites-available/vpn-panel.conf << EOF
<VirtualHost *:80>
    ServerName $SERVER_IP
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/vpn-panel_error.log
    CustomLog \${APACHE_LOG_DIR}/vpn-panel_access.log combined
</VirtualHost>
EOF

a2ensite vpn-panel.conf
a2dissite 000-default.conf
a2enmod rewrite
systemctl reload apache2

# Create htaccess for security
cat > /var/www/html/.htaccess << EOF
# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Hide PHP version
ServerTokens Prod
Header unset X-Powered-By

# Prevent access to sensitive files
<Files "*.env">
    Order allow,deny
    Deny from all
</Files>

<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
EOF

# Install SSL Certificate (Let's Encrypt)
echo -e "${OK} Installing SSL certificate..."
apt install -y certbot python3-certbot-apache

echo -e "${OK} Panel installation completed!"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${FONT}"
echo -e "  ${YELLOW}Panel URL: http://$SERVER_IP${FONT}"
echo -e "  ${YELLOW}Default Admin: admin${FONT}"
echo -e "  ${YELLOW}Default Password: AlrelShop2024${FONT}"
echo -e "  ${YELLOW}Database Password: $DB_PASS${FONT}"
echo -e "  ${YELLOW}Database saved to: /root/db_password.txt${FONT}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${FONT}"
echo ""
echo -e "${OK} To setup SSL certificate:"
echo -e "    certbot --apache -d your-domain.com"
echo ""
echo -e "${OK} Installation completed!"

# Save database password
echo "MySQL Root Password: $DB_PASS" > /root/db_password.txt
echo "Generated on: $(date)" >> /root/db_password.txt