#!/usr/bin/env bash
# Ejecutar en cada Kali. Prueba TCP/22, no autentica.
ip -br -4 addr
for ip in 10.23.88.130 10.23.88.131 10.23.88.132; do
  nc -n -zv -w 3 "$ip" 22
done
