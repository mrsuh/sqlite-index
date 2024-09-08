FROM debian:12.6-slim AS build

RUN mkdir /app
WORKDIR /app

RUN apt-get update
RUN apt-get install -y g++ build-essential make cmake tcl-dev tk-dev php wget unzip

RUN wget https://www.sqlite.org/src/zip/sqlite.zip?r=version-3.46.0 -O sqlite.zip
RUN unzip sqlite.zip

WORKDIR /app/sqlite
COPY ./sqlite.patch /app/sqlite.patch
RUN patch -p0 --force < ../sqlite.patch 

RUN mkdir /app/build
WORKDIR /app/build
RUN ../sqlite/configure CPPFLAGS="-DSQLITE_DEBUG -DSQLITE_ENABLE_EXPLAIN_COMMENTS -DSQLITE_ENABLE_TREETRACE -DSQLITE_ENABLE_WHERETRACE -DSQLITE_ENABLE_STMT_SCANSTATUS"
RUN make sqlite3
    
COPY . /app
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /app
RUN composer install --ignore-platform-reqs

FROM debian:12.6-slim

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        imagemagick \
        php \
        php-sqlite3 \
        php-imagick; \
    rm -rf /var/cache/apt/archives /var/lib/apt/lists/*

RUN mkdir /app
RUN mkdir /app/data
WORKDIR /app

COPY . /app

COPY --from=build /app/build/sqlite3 /usr/bin/sqlite3
COPY --from=build /app/vendor /app/vendor
