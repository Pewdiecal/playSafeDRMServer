FROM bitnami/laravel:latest
RUN apt-get update
RUN apt-get install -y curl libc-ares-dev build-essential git python python3 ffmpeg cron
WORKDIR /app

