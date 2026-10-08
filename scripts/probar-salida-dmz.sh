#!/usr/bin/env bash
# Correlacionar los intentos con los registros de FortiGate.
hostname
curl -4 --noproxy '*' -I --connect-timeout 5 --max-time 20 http://archive.ubuntu.com/ubuntu/dists/noble/InRelease
curl -4 --noproxy '*' -I --connect-timeout 5 --max-time 10 http://example.com
ping -c 4 -W 2 10.23.88.21
ping -c 4 -W 2 10.23.89.10
