
# https://zhih.me/acme-sh-guide/
# https://github.com/Neilpang/acme.sh/tree/master/dnsapi

# 手工添加TXT记录，然后 --renew
# 检测是否生效 dig  -t txt  _acme-challenge.6tu.me @8.8.8.8
# /root/.acme.sh/acme.sh --issue --dns -d 6tu.me -d *.6tu.me --force --yes-I-know-dns-manual-mode-enough-go-ahead-please

# acme.sh --issue --standalone -d yisuo.run -d www.yisuo.run


export HE_Username=""
export HE_Password=""
export BRANCH=dev

source ~/.bashrc

/root/.acme.sh/acme.sh --upgrade --auto-upgrade

/root/.acme.sh/acme.sh --issue --dns dns_he -d 6tu.me -d *.6tu.me --force

acme.sh --installcert -d 6tu.me \
        --key-file /opt/lampp/etc/ssl.key/6tu.me.key \
        --fullchain-file /opt/lampp/etc/ssl.crt/6tu.me.crt \
        --reloadcmd "/opt/lampp/lampp reloadapache"

/opt/lampp/lampp stopapache
/opt/lampp/lampp startapache
