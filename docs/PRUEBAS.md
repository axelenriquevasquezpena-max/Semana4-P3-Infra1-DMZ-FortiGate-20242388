# Comandos de validación

Estas pruebas se ejecutan en el laboratorio autorizado. Los comandos no representan nuevas ejecuciones efectuadas al preparar el repositorio.

## Clientes Kali

```bash
ip -br -4 addr
ip route
bash scripts/probar-ssh.sh
```

Esperado: TCP/22 abierto en los tres servidores desde VLAN 20, sin conexión desde VLAN 10. Para autenticar desde administración:

```bash
ssh axel@10.23.88.130
ssh axel@10.23.88.131
ssh axel@10.23.88.132
```

En el navegador, comprobar Caja e Inventario. Desde VLAN 10 Caja debe funcionar e Inventario mostrar el bloqueo de FortiGate. Desde VLAN 20 ambas aplicaciones deben estar accesibles. Una petición HEAD (`curl -I`) no sustituye la verificación de la página real: en una prueba previa se obtuvo 200 con HEAD mientras el navegador mostró la página de bloqueo.

## Servidores de DMZ

```bash
nslookup archive.ubuntu.com 10.23.88.129
bash scripts/probar-actualizaciones.sh
bash scripts/probar-salida-dmz.sh
```

En `probar-salida-dmz.sh`, se espera respuesta del repositorio Ubuntu y bloqueo de example.com y de nuevas conexiones hacia las VLAN. Ajustar las IP de los clientes si cambiaron por DHCP. Correlacionar intentos con **Log & Report → Forward Traffic**: DMZ-DENY-INTERNET (8), DMZ-DENY-VLAN10 (5), DMZ-DENY-VLAN20 (6). El timeout por sí solo no prueba una regla concreta.

## Switches Cisco

```text
enable
terminal length 0
show vlan brief
show port-security
show interfaces trunk
show running-config
copy running-config startup-config
```

En SW-USUARIOS, Gi0/0 transporta VLAN 10 y 20, activas y forwarding. En SW-DMZ, Gi0/0 es acceso VLAN 30 y no se espera un troncal. Port-security debe mostrar una MAC en cada puerto de host y cero violaciones para las máquinas autorizadas; esto comprueba estado, no una prueba de ataque de suplantación.

## Aplicaciones y base de datos

Registrar un producto desde Inventario, vender una cantidad disponible desde Caja y verificar la actualización del stock. No se incluyen datos de ventas en el esquema SQL; deben crearse al reproducir el entorno. Revisar los resultados sin mostrar las contraseñas del archivo privado de conexión.
