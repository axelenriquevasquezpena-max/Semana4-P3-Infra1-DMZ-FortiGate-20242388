# Configuración de la infraestructura

## Conmutación

SW-USUARIOS Gi0/0 es un troncal 802.1Q que permite únicamente VLAN 10 y 20. Gi0/1 pertenece a VLAN 10 y Gi0/2 a VLAN 20. SW-DMZ utiliza VLAN 30 en Gi0/0–Gi0/3; su enlace a port3 de FortiGate es de acceso.

Los puertos de los equipos finales tienen port-security con MAC sticky, máximo observado de una dirección y acción `restrict`, además de PortFast y BPDU Guard. Los puertos sin uso están administrativamente apagados. Las MAC guardadas pertenecen a las máquinas del laboratorio; al recrearlo con otras NIC deben revisarse. Port-security no sustituye autenticación de dispositivos.

Los archivos `*-running.cfg` reproducen la configuración aportada, con banners y líneas vacías omitidos. Los archivos `*-vlans.cfg` reconstruyen las VLAN observadas en `show vlan brief`, que no aparecían en el running-config. Los switches no tienen aquí una configuración completa de administración SSH; las pruebas SSH corresponden a los servidores Ubuntu.

## FortiGate: configuración por GUI

1. **Network → Interfaces:** configurar las VLAN 10 y 20 sobre port2, sus direcciones /25 y DHCP; port3 con `10.23.88.129/28`; port4 recibe la WAN por DHCP. Revisar la ruta por defecto hacia la WAN y el monitor de rutas.
2. **Policy & Objects → Addresses:** crear WEB-CAJA `.130/32`, WEB-INVENTARIO `.131/32`, DB-SERVER `.132/32`; reunirlos en SERVIDORES-DMZ. Los objetos `VLAN10-USUARIOS address` y `VLAN20-ADMIN address` corresponden a las subredes de interfaz.
3. Crear los objetos FQDN UPDATE-UBUNTU-ARCHIVE, UPDATE-UBUNTU-DO y UPDATE-UBUNTU-SECURITY para los tres dominios del README, y el grupo ENDPOINTS-ACTUALIZACION.
4. **Security Profiles → Web Filter:** BLOQUEO-INVENTARIO-VLAN10 usa bloqueo wildcard `*`. DMZ-SOLO-ACTUALIZACIONES permite las tres rutas `dominio/ubuntu/*` y termina con wildcard `*` en Block. Mantener el orden de las entradas.
5. **Policy & Objects → Firewall Policy:** reproducir las ocho políticas del README en el orden indicado, con registro de tráfico. NAT habilitado únicamente en la salida de actualizaciones de estas ocho reglas.
6. **Network → DNS / DNS Servers:** configurar resolución de FortiGate y servicio DNS recursivo en port3. Los servidores Ubuntu consultan `10.23.88.129`.
7. **Log & Report → Forward Traffic / Web Filter:** comprobar las coincidencias y los bloqueos. Exportar un respaldo desde el menú de administración al finalizar.

La política VLAN20-WEB-DMZ conserva el objeto real RED-VLAN20-PRUEBA (`10.23.89.0/25`), equivalente a la subred de administración. No debe confundirse su nombre con una red adicional. La regla de bloqueo de Inventario tiene inspección de certificados y perfil web; las demás reglas internas de permiso tienen no-inspection.

El archivo [fortigate-sanitizado.conf](../configs/fortigate-sanitizado.conf) es la referencia de la configuración exportada. Los campos con `REDACTED_REPLACE_LOCALLY` no son credenciales válidas. La configuración y la demostración de FortiGate se realizan desde la GUI.

## DNS y actualizaciones

Los servidores inicialmente tenían DNS `192.168.1.1`; se corrigió a `10.23.88.129`. La validación usa IPv4 y los repositorios HTTP configurados en Ubuntu. Una actualización puede fallar si se añade un repositorio o una redirección fuera de la lista autorizada: revisar su necesidad y los registros antes de ampliar destinos.

El laboratorio utilizó 2 GB de RAM para FortiGate después de presentar problemas de conectividad. Esto documenta el entorno final, sin atribuir una causa definitiva a los fallos anteriores.
