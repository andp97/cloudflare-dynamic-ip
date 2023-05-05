FROM php:8.2-alpine
ARG APP_VERSION="v2.0.3"
ENV USER="appuser"
ENV APP_DIR="/app"
ENV APP_SOURCE="$APP_DIR/source"
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN addgroup -S $USER && adduser -S $USER -G $USER
RUN mkdir -p $APP_SOURCE && chown -R $USER:$USER $APP_DIR

USER $USER

COPY --chown=$USER:$USER . $APP_SOURCE
WORKDIR $APP_SOURCE

RUN composer install --no-dev --no-progress && \
    php cloudflare-ddns app:build --build-version=$APP_VERSION cloudflare-ddns

RUN chmod +x $APP_SOURCE/builds/cloudflare-ddns && \
    mv $APP_SOURCE/builds/cloudflare-ddns $APP_DIR/cloudflare-ddns && \
    rm -rf $APP_SOURCE

ENTRYPOINT ["/app/cloudflare-ddns"]