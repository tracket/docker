FROM nginx:1.19.0-alpine

RUN rm /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/nginx.conf /etc/nginx/conf.d

ENV ROOT_DIR=/srv/src/public
COPY ./public $ROOT_DIR