FROM php:8.3-apache-bookworm@sha256:0540815262141e96282c4734c7c3b8b87733fd97e98d9688a9eadcaeb2adcf88

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && apt-get purge -y --auto-remove libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/* \
    && php -r 'exit(extension_loaded("curl") ? 0 : 1);'
