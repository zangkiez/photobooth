ARG BASE_IMAGE
FROM $BASE_IMAGE

RUN apt-get update && \
    apt-get install -y --no-install-recommends git ca-certificates curl ffmpeg && \
    rm -rf /var/lib/apt/lists/*

# IPP print proxy — ใช้ Python3 ส่งงานพิมพ์ไปยัง Mac CUPS ผ่าน host.docker.internal
# ไม่ต้องลง cups-client (ซึ่ง post-install script ล้มเหลวใน DDEV container)
COPY lp-cups.py /usr/local/bin/lp
RUN chmod +x /usr/local/bin/lp

# Preinstall node 20 for consistency with project engines
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get update && apt-get install -y --no-install-recommends nodejs && \
    npm install -g npm@latest && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
