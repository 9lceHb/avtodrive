ARG PHP_VERSION=8.2
ARG COMPOSER_VERSION=latest

###########################################
# PHP dependencies
###########################################

FROM composer:${COMPOSER_VERSION} AS vendor
WORKDIR /var/www/html
COPY auth.json ./
COPY composer* ./
RUN composer install \
  --no-dev \
  --no-interaction \
  --prefer-dist \
  --ignore-platform-reqs \
  --optimize-autoloader \
  --apcu-autoloader \
  --ansi \
  --no-scripts

###########################################
FROM nginx/unit:1.26.1-php${PHP_VERSION}

ARG TZ=UTC

# Accepted values: app - horizon - scheduler
ARG CONTAINER_MODE=app

ARG APP_WITH_HORIZON=false
ARG APP_WITH_SCHEDULER=false

ARG PRODUCTION

ENV CONTAINER_MODE=${CONTAINER_MODE} \
    APP_WITH_HORIZON=${APP_WITH_HORIZON} \
    APP_WITH_SCHEDULER=${APP_WITH_SCHEDULER}

ENV ROOT=/var/www/html
WORKDIR $ROOT

SHELL ["/bin/bash", "-eou", "pipefail", "-c"]

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

RUN apt-get update; \
    apt-get upgrade -yqq; \
    pecl -q channel-update pecl.php.net; \
    apt-get install -yqq --no-install-recommends --show-progress \
          apt-utils \
          gnupg \
          gosu \
          git \
          curl \
          libcurl4-openssl-dev \
          ca-certificates \
          libmemcached-dev \
          libz-dev \
          libbrotli-dev \
          libpq-dev \
          libjpeg-dev \
          libpng-dev \
          libfreetype6-dev \
          libssl-dev \
          libwebp-dev \
          libmcrypt-dev \
          libonig-dev \
          libzip-dev zip unzip \
          libargon2-1 \
          libidn2-0 \
          libpcre2-8-0 \
          libpcre3 \
          libxml2-dev \
          libzstd1 \
          procps

###########################################
# pdo_mysql
###########################################

RUN docker-php-ext-install pdo_mysql;

###########################################
# zip
###########################################

RUN docker-php-ext-configure zip && docker-php-ext-install zip;

###########################################
# mbstring
###########################################

RUN docker-php-ext-install mbstring;

###########################################
# GD
###########################################

RUN docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-webp \
            --with-freetype \
    && docker-php-ext-install gd;

###########################################
# OPcache
###########################################

ARG INSTALL_OPCACHE=true

RUN if [ ${INSTALL_OPCACHE} = true ] && [${PRODUCTION} = true]; then \
      docker-php-ext-install opcache; \
  fi

###########################################
# PHP Redis
###########################################

ARG INSTALL_PHPREDIS=true

RUN if [ ${INSTALL_PHPREDIS} = true ]; then \
      pecl install -o -f redis \
      && rm -rf /tmp/pear \
      && docker-php-ext-enable redis; \
  fi

###########################################
# PCNTL
###########################################

ARG INSTALL_PCNTL=true

RUN if [ ${INSTALL_PCNTL} = true ]; then \
      docker-php-ext-install pcntl; \
  fi

###########################################
# BCMath
###########################################

ARG INSTALL_BCMATH=true

RUN if [ ${INSTALL_BCMATH} = true ]; then \
      docker-php-ext-install bcmath; \
  fi

###########################################
# RDKAFKA
###########################################

ARG INSTALL_RDKAFKA=false

RUN if [ ${INSTALL_RDKAFKA} = true ]; then \
      apt-get install -yqq --no-install-recommends --show-progress librdkafka-dev \
      && pecl -q install -o -f rdkafka \
      && docker-php-ext-enable rdkafka; \
  fi
###########################################################################
# Human Language and Character Encoding Support
###########################################################################

ARG INSTALL_INTL=true

RUN if [ ${INSTALL_INTL} = true ]; then \
      apt-get install -yqq --no-install-recommends --show-progress zlib1g-dev libicu-dev g++ \
      && docker-php-ext-configure intl \
      && docker-php-ext-install intl; \
  fi

###########################################
# MySQL Client
###########################################

ARG INSTALL_MYSQL_CLIENT=false

RUN if [ ${INSTALL_MYSQL_CLIENT} = true ]; then \
      apt-get install -yqq --no-install-recommends --show-progress default-mysql-client; \
  fi

###########################################
# pdo_pgsql
###########################################

ARG INSTALL_PDO_PGSQL=true

RUN if [ ${INSTALL_PDO_PGSQL} = true ]; then \
      docker-php-ext-install pdo_pgsql; \
  fi

###########################################
# pgsql
###########################################

ARG INSTALL_PGSQL=true

RUN if [ ${INSTALL_PGSQL} = true ]; then \
      docker-php-ext-install pgsql; \
  fi

###########################################
# sockets
###########################################

RUN docker-php-ext-install sockets;

###########################################
# soap
###########################################

RUN docker-php-ext-install soap;

###########################################
# pgsql client and postgis
###########################################

ARG INSTALL_PG_CLIENT=false
ARG INSTALL_POSTGIS=true

RUN if [ ${INSTALL_PG_CLIENT} = true ]; then \
      . /etc/os-release \
      && echo "deb http://apt.postgresql.org/pub/repos/apt $VERSION_CODENAME-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
      && curl -sL https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - \
      && apt-get install -yqq --no-install-recommends --show-progress postgresql-client-12 postgis; \
      if [ ${INSTALL_POSTGIS} = true ]; then \
        apt-get install -yqq --no-install-recommends --show-progress postgis; \
      fi; \
  fi

###########################################
# Laravel scheduler
###########################################

RUN if [ ${CONTAINER_MODE} = 'scheduler' ] || [ ${APP_WITH_SCHEDULER} = true ]; then \
    apt-get install -yqq --no-install-recommends --show-progress \
      wget \
      supervisor \
    && wget -q "https://github.com/aptible/supercronic/releases/download/v0.1.12/supercronic-linux-amd64" \
           -O /usr/bin/supercronic \
      && chmod +x /usr/bin/supercronic \
      && mkdir -p /etc/supercronic \
      && echo "*/1 * * * * su octane -c \"php ${ROOT}/artisan schedule:run --verbose --no-interaction\"" > /etc/supercronic/laravel; \
  fi

###########################################

RUN apt-get clean \
    && docker-php-source delete \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && rm /var/log/lastlog /var/log/faillog

COPY . .
COPY --from=vendor ${ROOT}/vendor vendor
COPY --from=vendor /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p \
  storage/framework/{sessions,views,cache} \
  storage/logs \
  bootstrap/cache \
  && chown -R www-data:www-data \
  storage \
  bootstrap/cache \
  && chmod -R ug+rwx storage bootstrap/cache

# COPY deployment/unit/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY deployment/unit/conf/config.json /docker-entrypoint.d/.unit.conf.json

CMD ["unitd", "--no-daemon"]
