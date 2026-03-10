#!/bin/bash
# Print proxy สำหรับ Dev บน Mac — ส่งงานไป bin/print-relay ที่รันบน host (port 6631)
FILE="${@: -1}"
exec curl -sf -X POST --data-binary @"$FILE" -H "Content-Type: image/jpeg" "http://host.docker.internal:6631/print"
