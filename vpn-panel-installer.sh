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

# Secure MySQL installation
echo -e "${OK} Setting up MySQL..."
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'AlrelShop2024!';"
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -e "FLUSH PRIVILEGES;"

# Create database
echo -e "${OK} Creating database..."
mysql -u root -pAlrelShop2024! -e "CREATE DATABASE IF NOT EXISTS vpn_panel;"

# Download and setup panel files
echo -e "${OK} Setting up panel files..."
cd /var/www/html
rm -rf *

# Create panel structure
mkdir -p assets/css assets/js assets/images config includes pages api

echo -e "${OK} Panel installation completed!"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${FONT}"
echo -e "  ${YELLOW}Panel URL: http://$(curl -s ifconfig.me)${FONT}"
echo -e "  ${YELLOW}Default Admin: admin${FONT}"
echo -e "  ${YELLOW}Default Password: AlrelShop2024${FONT}"
echo -e "  ${YELLOW}Database Password: AlrelShop2024!${FONT}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${FONT}"
echo ""
echo -e "${OK} Installation completed! Please run the panel setup next."