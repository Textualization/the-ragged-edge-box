# Build binaries

FROM debian:bookworm-slim AS build
WORKDIR /ragged
RUN apt-get update && apt-get -y install --no-install-suggests build-essential git composer libgomp1 libblas3 liblapack3 wget php8.2-cli php8.2-curl php8.2-xml cmake libatlas-base-dev liblapack-dev debhelper-compat autoconf libtool automake chrpath lynx libreadline-dev tcl8.6-dev
COPY . .
RUN composer install && \
  composer exec -- php -r "require 'vendor/autoload.php'; OnnxRuntime\Vendor::check();" && \
  composer exec -- php -r "require 'vendor/autoload.php'; Textualization\SentenceTransphormers\Vendor::check();"

# Backport the sqlite3 version from Debian testing
RUN mkdir /ragged/deploy && \
    cp /ragged/box/*.sh \
       /ragged/box/*.ini \
       /ragged/box/*.service \
       /ragged/box/*.php \
       /ragged/box/*.network /ragged/deploy && \
    cd /ragged/download && \
    dpkg-source -x sqlite3_3.46.0-1.dsc && \
    cd sqlite3-3.46.0 && \
    ./debian/rules binary && \
    cd .. && \
    dpkg -i libsqlite3-0_3.46.0-1_amd64.deb libsqlite3-dev_3.46.0-1_amd64.deb sqlite3_3.46.0-1_amd64.deb && \
    cp libsqlite3-0_3.46.0-1_amd64.deb sqlite3_3.46.0-1_amd64.deb /ragged/deploy 

# compile the llama.cpp servers
RUN cd /ragged/llama.cpp && \
   mv Makefile Makefile,orig && \
   cp /ragged/Makefile.llama-generic /ragged/llama.cpp/Makefile && \
   make clean && \
   make && \
   cp llama-server /ragged/deploy/llama-server,generic && \
   mv Makefile,orig Makefile && \
   make clean && \
   make && \
   cp llama-server /ragged/deploy/llama-server,avx2
   
# compile the sqlite3 extension
RUN cd /ragged/sqlite-vss && \
   cd vendor/sqlite && \
   ./configure && make && \
   cd ../.. && \
   make loadable-release && \
   cp dist/release/vector0.so /ragged/deploy/vector0.so,avx2 && \
   cp dist/release/vss0.so /ragged/deploy/vss0.so,avx2 && \
    make clean && \
   rm -f CMakeCache.txt && \
   rm -rf build build_release dist && \
   cp /ragged/CMakeLists.txt.sqlite-vss-avx CMakeLists.txt && \
   make loadable-release && \
   cp dist/release/vector0.so /ragged/deploy/vector0.so,avx && \
   cp dist/release/vss0.so /ragged/deploy/vss0.so,avx

# Final image
FROM debian:bookworm-slim
WORKDIR /
RUN printf "edgebox\nedgebox" | passwd && \
    printf "edgebox\nedgebox\nRagged Edge\n\n\n\n\ny" | adduser box && \
    mkdir /home/box/vendor && \
    mkdir /home/box/site && \
    mkdir /home/box/data
COPY --from=build /ragged/vendor /home/box/vendor
COPY --from=build /ragged/site /home/box/site
COPY --from=build /ragged/data /home/box/data
COPY --from=build /ragged/deploy/* /ragged/download/*.guff /home/box/
RUN chown -R box:box /home/box && \
    apt-get update && apt-get -y --no-install-suggests --no-install-recommends install \
    curl \
    php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-curl \
    libgomp1 libblas3 liblapack3 linux-image-amd64 systemd-sysv libreadline8 zlib1g \
    file poppler-utils antiword pandoc && \
    dpkg -i /home/box/libsqlite3-0_3.46.0-1_amd64.deb /home/box/sqlite3_3.46.0-1_amd64.deb && \
    rm /home/box/sqlite3_3.46.0-1_amd64.deb /home/box/libsqlite3-0_3.46.0-1_amd64.deb && \
    echo 'ragged' > /etc/hostname && \
    echo '/dev/sda1 / ext3 rw,noatime 0 0' > /etc/fstab && \
    true `# set PHP.ini files` && \
    mv /home/box/php.ini /etc/php/8.2/cli/ `# set PHP.ini files` && \
    true `# set systemd files` && \
    mv /home/box/*.service /etc/systemd/system/ && \
    mv /home/box/*.network /etc/systemd/network/
RUN systemctl enable ragged-php.service && \
    systemctl enable ragged-llama.service && \
    systemctl enable systemd-networkd
