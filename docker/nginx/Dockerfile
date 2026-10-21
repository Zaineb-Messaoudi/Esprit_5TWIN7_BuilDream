FROM nginx:1.27-alpine

RUN mkdir -p /var/cache/nginx /var/run/nginx /tmp/client_temp /tmp/proxy_temp \
    /tmp/fastcgi_temp /tmp/uwsgi_temp /tmp/scgi_temp \
    && chown -R nginx:nginx /var/cache/nginx /var/run/nginx /etc/nginx /tmp/*_temp

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY public /var/www/html/public

RUN chown -R nginx:nginx /var/www/html/public

USER nginx
EXPOSE 8080
