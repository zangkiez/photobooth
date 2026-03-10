#!/bin/bash
# lp proxy — forwards print job to bin/print-relay running on Mac host
# Compatible with the `lp FILE` interface used by photobooth
# curl is already installed in this DDEV container
FILE="${@: -1}"
exec curl -sf -X POST --data-binary @"$FILE" http://host.docker.internal:6631/print
