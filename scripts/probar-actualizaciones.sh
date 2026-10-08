#!/usr/bin/env bash
# Ejecutar en cada Ubuntu. Actualiza indices, no instala paquetes.
set -o pipefail
hostname
resolvectl status ens33
getent ahostsv4 archive.ubuntu.com
sudo apt-get -o Acquire::ForceIPv4=true -o Acquire::Retries=0 -o Acquire::http::Timeout=15 update 2>&1 | tee /tmp/apt-update-lab.log
status=${PIPESTATUS[0]}
grep -E -A 3 '^(Err:|W:|E:)' /tmp/apt-update-lab.log || true
exit "$status"
