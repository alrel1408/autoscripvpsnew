# AlrelShop VPN Panel V.4

<p align="center">
<img src="https://readme-typing-svg.herokuapp.com?color=%2336BCF7&center=true&vCenter=true&lines=A+L+R+E+L+S+H+O+P++V+P+N++P+A+N+E+L" />
</p>

## 🚀 **Premium VPN Management Panel**

Panel management VPN yang canggih untuk mengelola akun VPN dengan mudah dan profesional.

### ✨ **Features**

- 🔐 **Multi-user Authentication** (Admin/Reseller/User)
- 💰 **Saldo Management System**
- 🖥️ **Multi-Server Support** 
- 🔗 **Digital Ocean API Integration**
- 📊 **Real-time Dashboard**
- 📱 **Responsive Mobile Design**
- 🎨 **Modern Bootstrap 5 UI**
- 📋 **QR Code Generator**
- 💳 **Payment & Transaction System**
- 🔄 **Auto Config Generation**

### 🛠️ **Installation**

#### **Requirements:**
- Ubuntu 18/20/22 or Debian 9/10/11
- Apache/Nginx Web Server
- PHP 7.4+ with extensions: mysql, curl, json, mbstring, xml, zip, gd
- MySQL/MariaDB 5.7+
- SSL Certificate (recommended)

#### **Quick Install:**
```bash
# Download installer
wget https://raw.githubusercontent.com/alrel1408/vpn-panel/main/install.sh

# Make executable
chmod +x install.sh

# Run installer
./install.sh
```

#### **Manual Installation:**
```bash
# 1. Update system
apt update && apt upgrade -y

# 2. Install requirements
apt install -y apache2 php php-mysql php-curl php-json php-mbstring php-xml php-zip php-gd mysql-server

# 3. Clone repository
git clone https://github.com/alrel1408/vpn-panel.git
cd vpn-panel

# 4. Copy files to web directory
cp -r * /var/www/html/

# 5. Set permissions
chown -R www-data:www-data /var/www/html/
chmod -R 755 /var/www/html/

# 6. Configure database
mysql -u root -p
CREATE DATABASE vpn_panel;
exit

# 7. Access panel and complete setup
# http://your-domain.com
```

### 🔧 **Configuration**

Edit `config/database.php` untuk konfigurasi database:

```php
$db_host = 'localhost';
$db_username = 'root';
$db_password = 'your-password';
$db_name = 'vpn_panel';
```

### 📊 **Default Login**

- **Username:** admin
- **Password:** AlrelShop2024
- **URL:** http://your-domain.com

⚠️ **Penting:** Ganti password default setelah login pertama!

### 🖥️ **Server Integration**

Panel ini terintegrasi dengan VPN server yang menggunakan script AlrelShop V.4:

```bash
# Install VPN server terlebih dahulu
wget https://raw.githubusercontent.com/alrel1408/autoscripvpsnew/main/ubu20-deb10-stable.sh
chmod +x ubu20-deb10-stable.sh
./ubu20-deb10-stable.sh
```

### 💰 **Pricing Configuration**

Edit harga di `create-account.php`:

```php
$pricing = [
    'ssh' => 3.00,           // SSH/OpenVPN
    'vmess' => 5.00,         // VMess
    'vless' => 5.00,         // VLess  
    'trojan' => 6.00,        // Trojan
    'shadowsocks' => 4.00    // Shadowsocks
];
```

### 🔗 **Supported Services**

- ✅ SSH/OpenVPN
- ✅ VMess (V2Ray)
- ✅ VLess (Xray)  
- ✅ Trojan
- ✅ Shadowsocks

### 📱 **Mobile Apps**

Panel mendukung konfigurasi untuk aplikasi:

**Android:**
- V2rayNG
- Shadowsocks
- ConnectBot
- OpenVPN Connect

**iOS:**
- Shadowrocket
- Quantumult X
- OpenVPN Connect

**Windows:**
- V2rayN
- Shadowsocks Windows
- PuTTY

### 🎨 **Screenshots**

![Login Page](https://via.placeholder.com/800x400?text=Login+Page)
![Dashboard](https://via.placeholder.com/800x400?text=Dashboard)
![Create Account](https://via.placeholder.com/800x400?text=Create+Account)
![View Config](https://via.placeholder.com/800x400?text=View+Config)

### 🔒 **Security Features**

- Password hashing dengan bcrypt
- Session management
- SQL injection protection
- XSS protection
- CSRF protection
- SSL/TLS support

### 📞 **Support**

- **WhatsApp:** 082285851668
- **Telegram:** @alrelshop
- **Email:** admin@alrelshop.com

### 📄 **License**

© 2024 AlrelShop. All rights reserved.

---

**Made with ❤️ by AlrelShop**