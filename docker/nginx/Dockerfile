FROM node:22-alpine AS frontend-assets

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js .
RUN npm run build

FROM nginx:1.29-alpine

RUN apk upgrade --no-cache --available || apk upgrade --no-cache --available --repository=https://dl-cdn.alpinelinux.org/alpine/v3.20/main --repository=https://dl-cdn.alpinelinux.org/alpine/v3.20/community

RUN mkdir -p /var/cache/nginx /var/run/nginx /tmp/client_temp /tmp/proxy_temp \
    /tmp/fastcgi_temp /tmp/uwsgi_temp /tmp/scgi_temp \
    && chown -R nginx:nginx /var/cache/nginx /var/run/nginx /etc/nginx /tmp/*_temp

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY public /var/www/html/public
COPY --from=frontend-assets /var/www/html/public/build /var/www/html/public/build

RUN chown -R nginx:nginx /var/www/html/public

USER nginx
EXPOSE 8080
