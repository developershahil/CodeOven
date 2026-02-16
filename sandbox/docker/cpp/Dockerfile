FROM gcc:13.2.0
RUN groupadd -g 10001 sandbox \
    && useradd -m -u 10001 -g 10001 sandbox \
    && mkdir -p /sandbox \
    && chown -R sandbox:sandbox /sandbox
USER 10001:10001
WORKDIR /sandbox
