apt-get update
apt-get install -y libpcre3 libpcre3-dev

apt-get install -y libzip-dev

curl -O http://www.zlib.net/zlib-1.2.11.tar.gz
tar xvfz zlib-1.2.11.tar.gz
./configure
make && make install

wget https://nih.at/libzip/libzip-1.2.0.tar.gz
tar -zxvf libzip-1.2.0.tar.gz
cd libzip-1.2.0
./configure
make && make install
