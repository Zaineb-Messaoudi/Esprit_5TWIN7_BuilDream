FROM nginx:1.29-alpine

RUN apk upgrade --no-cache --available || apk upgrade --no-cache --available --repository=https://dl-cdn.alpinelinux.org/alpine/v3.20/main --repository=https://dl-cdn.alpinelinux.org/alpine/v3.20/community

RUN mkdir -p /var/cache/nginx /var/run/nginx /tmp/client_temp /tmp/proxy_temp \
    /tmp/fastcgi_temp /tmp/uwsgi_temp /tmp/scgi_temp \
    && chown -R nginx:nginx /var/cache/nginx /var/run/nginx /etc/nginx /tmp/*_temp

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY public /var/www/html/public

RUN chown -R nginx:nginx /var/www/html/public

USER nginx
EXPOSE 8080
