FROM php:7.4-fpm AS base

WORKDIR /var/www/web.twohoursonelife.com

RUN docker-php-ext-install mysqli
RUN echo "date.timezone=UTC" > /usr/local/etc/php/conf.d/timezone.ini

# Faces
RUN apt-get update && apt-get install -y git && \
    git clone --depth 1 https://github.com/twohoursonelife/OneLifeData7.git /tmp/OneLifeData7 && \
    mkdir -p /var/www/web.twohoursonelife.com/web/public/lineageServer/faces && \
    cp -r /tmp/OneLifeData7/faces/* web/public/lineageServer/faces/ && \
    rm -rf /tmp/OneLifeData7 && \
    apt-get purge -y --auto-remove git && \
    rm -rf /var/lib/apt/lists/*

FROM base AS runtime

RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

COPY nginx-prod.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

COPY web ./web
COPY data ./data

RUN chown -R www-data:www-data /var/www/web.twohoursonelife.com

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
