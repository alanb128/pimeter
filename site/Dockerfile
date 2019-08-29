FROM  balenalib/rpi-raspbian

RUN echo 'deb http://mirrordirector.raspbian.org/raspbian/ jessie main contrib non-free rpi' >> /etc/apt/sources.list

RUN apt-get update && apt-get install -y \
    nano \
    zip \
    wget \
    curl \
    sqlite sqlite3 \
    php5 \
    php5-fpm \
    php5-cli \
    php5-json \
    php5-gd \
    php5-sqlite \
    php5-memcache \
    php5-mcrypt \
    nginx \
    --no-install-recommends && \
    rm -rf /var/lib/apt/lists/* && \
    mkdir -p /var/www/html && chown -R www-data:www-data /var/www

RUN update-alternatives --set php /usr/bin/php5

RUN echo "daemon off;" >> /etc/nginx/nginx.conf

# Build this with option --build-arg CACHEBUST=$(date +%s) and everything below will NOT be cached
ARG CACHEBUST=1
RUN echo "HELLO CACHE $CACHEBUST"


RUN wget -O /etc/nginx/sites-available/default 'https://raw.githubusercontent.com/alanb128/wmeter/master/nginx-default'

ENTRYPOINT []

CMD service php5-fpm start && nginx
