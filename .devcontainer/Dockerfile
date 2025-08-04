FROM mcr.microsoft.com/devcontainers/php

RUN RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    php-intl \
    && rm -rf /var/lib/apt/lists/*
