#!/bin/bash

# AlrelShop VPN Script - Password Protection Fix V.4
# Script untuk mencegah perubahan password root otomatis

Green="\e[92;1m"
RED="\033[31m"
YELLOW="\033[33m"
NC="\033[0m"

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "  ${GREEN}AlrelShop Password Protection Fix V.4${NC}"
echo -e "  ${YELLOW}» Mencegah perubahan password root otomatis${NC}"
echo -e "  ${YELLOW}» Admin: 082285851668${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Backup script asli
echo -e "${YELLOW}[INFO]${NC} Membuat backup script asli..."
cp ubu20-deb10-stable.sh ubu20-deb10-stable.sh.backup

# Hapus semua fungsi yang mengubah password
echo -e "${YELLOW}[INFO]${NC} Menonaktifkan semua fungsi perubahan password..."

# 1. Pastikan fungsi password_default tidak mengubah password
sed -i '/function password_default/,/^}/c\
function password_default() {\
echo -e "$green [INFO]$NC Password VPS AMAN - Tidak ada perubahan password"\
echo -e "$yellow [WARNING]$NC Script ini TIDAK akan mengubah password root Anda"\
echo -e "$green [SUCCESS]$NC Password tetap menggunakan yang sudah ada sebelumnya"\
sleep 3\
}' ubu20-deb10-stable.sh

# 2. Hapus download script eksternal yang bisa mengubah password
sed -i 's/wget ${REPO}files\/cf\.sh && chmod +x cf\.sh && \.\/cf\.sh/echo -e "${GREEN}[SKIP]${NC} CloudFlare setup di-skip untuk keamanan password"/' ubu20-deb10-stable.sh

# 3. Hapus script BBR yang mungkin bermasalah 
sed -i 's/wget ${REPO}files\/bbr\.sh &&  chmod +x bbr\.sh && \.\/bbr\.sh/echo -e "${GREEN}[SKIP]${NC} BBR akan diinstall manual untuk keamanan password"/' ubu20-deb10-stable.sh

# 4. Tambahkan peringatan di awal script
sed -i '1a\\n# ⚠️  PASSWORD PROTECTION ACTIVE - Script ini TIDAK akan mengubah password root\n# 🔒 AlrelShop V.4 - Password Safe Mode\n' ubu20-deb10-stable.sh

# 5. Ganti semua referensi password lama
sed -i 's/Vallstore@2024/ORIGINAL_PASSWORD_PRESERVED/g' ubu20-deb10-stable.sh

echo -e "${GREEN}[SUCCESS]${NC} Script telah dipatch untuk melindungi password!"
echo -e "${YELLOW}[INFO]${NC} Sekarang script tidak akan mengubah password root sama sekali"
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "  ${GREEN}INSTALASI AMAN - PASSWORD TERLINDUNGI${NC}"
echo -e "  ${YELLOW}Silakan jalankan: chmod +x ubu20-deb10-stable.sh${NC}"
echo -e "  ${YELLOW}Kemudian: ./ubu20-deb10-stable.sh${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Buat script untuk reset password jika diperlukan
cat > reset-vps-password.sh << 'EOF'
#!/bin/bash

# Script untuk reset password VPS jika terlanjur berubah

echo "================================="
echo "    VPS Password Reset Helper"
echo "    AlrelShop V.4"
echo "================================="
echo ""
echo "Jika password VPS Anda berubah, gunakan salah satu cara berikut:"
echo ""
echo "1. Melalui Console Digital Ocean/Provider:"
echo "   - Login ke panel Digital Ocean"
echo "   - Masuk ke Console server"
echo "   - Reset password dari sana"
echo ""
echo "2. Melalui Recovery Mode:"
echo "   - Boot ke recovery mode"
echo "   - Mount filesystem"
echo "   - Run: passwd root"
echo ""
echo "3. Password yang mungkin digunakan script:"
echo "   - Password asli Anda (yang terbaik)"
echo "   - Vallstore@2024"
echo "   - AlrelShop2024"
echo "   - admin123"
echo ""
echo "4. Kontak support:"
echo "   - WhatsApp: 082285851668"
echo "   - Telegram: @alrelshop"
EOF

chmod +x reset-vps-password.sh

echo -e "${GREEN}[INFO]${NC} Script reset-vps-password.sh juga telah dibuat"
echo -e "${YELLOW}[TIP]${NC} Jika password sudah terlanjur berubah, gunakan reset-vps-password.sh"