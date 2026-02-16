FROM php:8.3-cli-alpine
RUN addgroup -S sandbox -g 10001 \
    && adduser -S sandbox -G sandbox -u 10001 \
    && mkdir -p /sandbox \
    && chown -R sandbox:sandbox /sandbox
USER 10001:10001
WORKDIR /sandbox
