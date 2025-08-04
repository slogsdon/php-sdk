FROM mcr.microsoft.com/devcontainers/php:8.2

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    php8.2-intl \
    && rm -rf /var/lib/apt/lists/*
