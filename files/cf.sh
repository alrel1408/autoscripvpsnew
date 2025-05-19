#!/bin/bash
Green="\e[92;1m"
RED="\033[31m"
YELLOW="\033[33m"
BLUE="\033[36m"
FONT="\033[0m"
GREENBG="\033[42;37m"
REDBG="\033[41;37m"
OK="${Green}--->${FONT}"
ERROR="${RED}[ERROR]${FONT}"
GRAY="\e[1;30m"
NC='\e[0m'
red='\e[1;31m'
green='\e[0;32m'
DF='\e[39m'
Bold='\e[1m'
Blink='\e[5m'
yell='\e[33m'
red='\e[31m'
green='\e[32m'
blue='\e[34m'
PURPLE='\e[35m'
cyan='\e[36m'
Lred='\e[91m'
Lgreen='\e[92m'
Lyellow='\e[93m'
NC='\e[0m'
GREEN='\033[0;32m'
ORANGE='\033[0;33m'
LIGHT='\033[0;37m'
grenbo="\e[92;1m"
red() { echo -e "\\033[32;1m${*}\\033[0m"; }
CHATID="2043946623"
KEY="6957450340:AAE3OyomqZQgf7SV68UUISd2Po-_Gd-Pwns"
export TIME="10"
export URL="https://api.telegram.org/bot$KEY/sendMessage"
MYIP=$(wget -qO- icanhazip.com);
apt install jq curl -y
if [ -f /root/xray/scdomain ]; then
    rm -rf /root/xray/scdomain
fi
if [ ! -d /root/xray ]; then
    mkdir -p /root/xray
fi
if [ ! -d /etc/xray ]; then
    mkdir -p /etc/xray
fi
if [ ! -d /etc/v2ray ]; then
    mkdir -p /etc/v2ray
fi
if [ ! -d /var/lib/kyt ]; then
    mkdir -p /var/lib/kyt
fi
touch /var/lib/kyt/ipvps.conf
cd /root
function domaincf() {
echo ""
echo ""
echo ""
read -rp "Input Sub Domain Kamu (Contoh: store111): " -e sub
DOMAIN=myvall.biz.id
echo $sub > /root/cfku
#SUB_DOMAIN=${sub}.myvall.biz.id
dns=${sub}.${DOMAIN}
dns2=*.${dns}
NS_DOMAIN=ns.${dns}
CF_ID=nvatryn@gmail.com
CF_KEY=e54ac7edea27dbef7cc1fdb13025d47da45c2
set -euo pipefail
IP=$(wget -qO- icanhazip.com);
# update domain vps mu
echo "Updating DNS RECORD (DomainNameSystem) for ${dns}..."
sleep 3
ZONE=$(curl -sLX GET "https://api.cloudflare.com/client/v4/zones?name=${DOMAIN}&status=active" \
-H "X-Auth-Email: ${CF_ID}" \
-H "X-Auth-Key: ${CF_KEY}" \
-H "Content-Type: application/json" | jq -r .result[0].id)
RECORD=$(curl -sLX GET "https://api.cloudflare.com/client/v4/zones/${ZONE}/dns_records?name=${dns}" \
-H "X-Auth-Email: ${CF_ID}" \
-H "X-Auth-Key: ${CF_KEY}" \
-H "Content-Type: application/json" | jq -r .result[0].id)
if [[ "${#RECORD}" -le 10 ]]; then
RECORD=$(curl -sLX POST "https://api.cloudflare.com/client/v4/zones/${ZONE}/dns_records" \
-H "X-Auth-Email: ${CF_ID}" \
-H "X-Auth-Key: ${CF_KEY}" \
-H "Content-Type: application/json" \
--data '{"type":"A","name":"'${dns}'","content":"'${IP}'","ttl":120,"proxied":false}' | jq -r .result.id)
RECORD=$(curl -sLX POST "https://api.cloudflare.com/client/v4/zones/${ZONE}/dns_records" \
-H "X-Auth-Email: ${CF_ID}" \
-H "X-Auth-Key: ${CF_KEY}" \
-H "Content-Type: application/json" \
--data '{"type":"A","name":"'${dns2}'","content":"'${IP}'","ttl":120,"proxied":false}' | jq -r .result.id)
fi
RESULT=$(curl -sLX PUT "https://api.cloudflare.com/client/v4/zones/${ZONE}/dns_records/${RECORD}" \
-H "X-Auth-Email: ${CF_ID}" \
-H "X-Auth-Key: ${CF_KEY}" \
-H "Content-Type: application/json" \
--data '{"type":"A","name":"'${dns}'","content":"'${IP}'","ttl":120,"proxied":false}')
RESULT=$(curl -sLX PUT "https://api.cloudflare.com/client/v4/zones/${ZONE}/dns_records/${RECORD}" \
-H "X-Auth-Email: ${CF_ID}" \
-H "X-Auth-Key: ${CF_KEY}" \
-H "Content-Type: application/json" \
--data '{"type":"A","name":"'${dns2}'","content":"'${IP}'","ttl":120,"proxied":false}')
# update nameserver mu
echo "Updating NS RECORD (NameServer) for ${NS_DOMAIN} "
ZONE=$(curl -sLX GET "https://api.cloudflare.com/client/v4/zones?name=${DOMAIN}&status=active" \
    -H "X-Auth-Email: ${CF_ID}" \
    -H "X-Auth-Key: ${CF_KEY}" \
    -H "Content-Type: application/json" | jq -r .result[0].id)
RECORD=$(curl -sLX GET "https://api.cloudflare.com/client/v4/zones/${ZONE}/dns_records?name=${NS_DOMAIN}" \
    -H "X-Auth-Email: ${CF_ID}" \
    -H "X-Auth-Key: ${CF_KEY}" \
    -H "Content-Type: application/json" | jq -r .result[0].id)
    if [[ "${#RECORD}" -le 10 ]]; then
        RECORD=$(curl -sLX POST "https://api.cloudflare.com/client/v4/zones/${ZONE}/dns_records" \
        -H "X-Auth-Email: ${CF_ID}" \
        -H "X-Auth-Key: ${CF_KEY}" \
        -H "Content-Type: application/json" \
        --data '{"type":"NS","name":"'${NS_DOMAIN}'","content":"'${dns}'","ttl":120,"proxied":false}' | jq -r .result.id)
    fi
RESULT=$(curl -sLX PUT "https://api.cloudflare.com/client/v4/zones/${ZONE}/dns_records/${RECORD}" \
    -H "X-Auth-Email: ${CF_ID}" \
    -H "X-Auth-Key: ${CF_KEY}" \
    -H "Content-Type: application/json" \
    --data '{"type":"NS","name":"'${NS_DOMAIN}'","content":"'${dns}'","ttl":120,"proxied":false}')
echo "DOMAIN KAMU : $dns"
echo "$dns" > /etc/xray/domain
echo "$dns" > /root/scdomain
echo "$dns" > /etc/xray/scdomain
echo ${dns} > /etc/v2ray/domain
echo $dns > /root/domain
echo "IP=$dns" > /var/lib/kyt/ipvps.conf
echo "IP=$dns" > /var/lib/ipvps.conf
echo "NAMESERVER KAMU : $NS_DOMAIN"
echo $NS_DOMAIN > /etc/xray/nsdomain
echo $NS_DOMAIN > /etc/xray/dns
echo $NS_DOMAIN > /etc/xray/slwdomain
sleep 3
cd /root
}
function notif_addhost() {
red "Notif AddHost Tele"
sleep 2
CHATID="2043946623"
KEY="6957450340:AAE3OyomqZQgf7SV68UUISd2Po-_Gd-Pwns"
TIME="10"
TEXT="
<code>━━━━━━━━━━━━━━</code>
<b>   ⚠️ADDHOST NOTIF⚠️</b>
<b>     Add Domain New</b>
<code>━━━━━━━━━━━━━━</code>
<b>IP VPS  :</b> <code>$MYIP </code>
<b>DOMAIN :</b> <code>$sub</code>
<code>$dns</code>
<code>━━━━━━━━━━━━━━</code>
<code>NEW ADD DOMAIN</code>
<code>BY BOT : @bot-gege</code>
"
curl -s --max-time $TIME -d "chat_id=$CHATID&disable_web_page_preview=1&text=$TEXT&parse_mode=html" $URL >/dev/null && curl -s --max-time 10 -d "chat_id=1117211252&disable_web_page_preview=1&text=$TEXT&parse_mode=html" https://api.telegram.org/bot6203209250:AAG7GoBbaUqo2qh4N-IGvScNisDWTHfLh8M/sendMessage >/dev/null
}
domaincf
notif_addhost
echo -e "Done Record Domain= ${dns} For VPS"
sleep 2
if [ -f /root/cf ]; then
    rm -f /root/cf
fi
if [ -f /root/cf.sh ]; then
    rm -f /root/cf.sh
fi
rm -rf cf > /dev/null 2>&1
rm -rf cf.sh > /dev/null 2>&1
#auto delete file cf setelah running
