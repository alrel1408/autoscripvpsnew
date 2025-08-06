# AlrelShop VPN Panel V.4 - Demo Guide

## 🎯 **QUICK DEMO SETUP**

Untuk demo cepat panel, ikuti langkah berikut:

### 1. **Setup Database Demo**
```sql
-- Login MySQL
mysql -u root -p

-- Create demo database
CREATE DATABASE vpn_panel_demo;
USE vpn_panel_demo;

-- Import demo data (akan auto create saat akses pertama)
```

### 2. **Demo Accounts**
```
Admin Account:
Username: admin
Password: AlrelShop2024

Demo User:
Username: demo
Password: demo123
Balance: $100.00
```

### 3. **Demo Servers**
```
Server 1: Digital Ocean Singapore
IP: 178.128.29.122
Location: Singapore
Status: Active

Server 2: Digital Ocean US
IP: 192.241.200.45  
Location: New York
Status: Active

Server 3: Digital Ocean EU
IP: 159.89.200.123
Location: London
Status: Active
```

### 4. **Demo VPN Accounts**
```
SSH Account:
Username: demo-ssh-001
Password: demoPass123
Service: SSH/OpenVPN
Server: Singapore
Expires: 30 days from creation
Status: Active

VMess Account:  
Username: demo-vmess-001
Password: demoPass456
Service: VMess (V2Ray)
Server: New York
Expires: 30 days from creation
Status: Active
```

## 🎮 **DEMO FEATURES**

### **Dashboard Features:**
- ✅ Real-time statistics display
- ✅ Quick action buttons  
- ✅ Recent accounts overview
- ✅ Balance management
- ✅ Responsive design

### **Account Creation:**
- ✅ Multi-server selection
- ✅ Service type selection (SSH, VMess, VLess, Trojan, Shadowsocks)
- ✅ Duration selection (30-365 days)
- ✅ Real-time price calculation
- ✅ Auto balance deduction

### **Config Display:**
- ✅ Copy config with 1-click
- ✅ QR code generation for mobile
- ✅ Download links for apps
- ✅ Connection guides
- ✅ Beautiful config cards

### **Admin Panel:**
- ✅ User management
- ✅ Server management  
- ✅ Transaction monitoring
- ✅ System settings

## 📱 **MOBILE COMPATIBILITY**

Panel fully responsive untuk:
- ✅ **Android** (Chrome, Firefox)
- ✅ **iOS** (Safari, Chrome)  
- ✅ **Tablet** (iPad, Android tablets)
- ✅ **Desktop** (Windows, Mac, Linux)

## 🔗 **API ENDPOINTS** (Coming Soon)

```
POST /api/create-account
GET /api/accounts
GET /api/servers
POST /api/deposit
GET /api/balance
```

## 📊 **DEMO DATA**

Panel akan otomatis generate data demo termasuk:
- 3 sample servers
- 1 admin account
- Pricing configuration
- Sample VPN accounts
- Transaction history

## 🚀 **PRODUCTION READY**

Panel siap production dengan:
- ✅ Security best practices
- ✅ SQL injection protection
- ✅ XSS protection
- ✅ Session management
- ✅ Password hashing
- ✅ Error handling
- ✅ Responsive UI

## 📞 **Support**

Untuk demo atau pertanyaan:
- **WhatsApp:** 082285851668
- **Telegram:** @alrelshop
- **Email:** admin@alrelshop.com

---

**© 2024 AlrelShop - Professional VPN Panel Solution**