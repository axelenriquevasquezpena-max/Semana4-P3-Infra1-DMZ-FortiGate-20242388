# Procedencia y tratamiento de archivos

| Material | Procedencia / tratamiento |
|---|---|
| apps/caja.php, apps/inventario.php | Archivos entregados desde los servidores; copiados sin cambios |
| configs/nginx-*.conf | Sitios HTTP exportados y entregados; copiados sin cambios |
| configs/mariadb-dmz.cnf | Configuración entregada de MariaDB; sin cambios |
| configs/laboratorio_p2-esquema.sql | Exportación de estructura entregada; sin datos y sin cambios |
| configs/fortigate-sanitizado.conf | Respaldo FortiGate terminado en 202610072039; contraseñas y claves privadas sustituidas por marcadores |
| configs/sw-*-running.cfg | Transcripción normalizada del running-config aportado; banners y líneas vacías omitidos |
| configs/sw-*-vlans.cfg | Comandos reconstruidos desde show vlan brief; no son una exportación de vlan.dat |
| configs/netplan/*.yaml | Reconstrucción de IP, prefijo, gateway e interfaz mostrados, con el DNS final corregido |
| configs/*.example | Plantillas de reproducción sin credenciales del entorno |
| scripts/ y docs/PRUEBAS.md | Comandos organizados para repetir las pruebas; no son registros de nuevas ejecuciones |
| evidencias/*.png | Capturas originales aportadas, sin editar su contenido |
| evidencias/verificacion-switches.txt | Transcripción condensada de resultados aportados |
| docs/topologia.svg | Diagrama elaborado a partir del direccionamiento y las conexiones configuradas |

Las fechas visibles de las máquinas virtuales pueden diferir del equipo anfitrión. El prefijo `p2` en la base de datos y rutas web es un nombre interno existente; esta entrega corresponde a Práctica 3, Infraestructura 1.

El video se enlaza tal como fue facilitado por el autor. Las comprobaciones de preparación del repositorio se limitan a integridad de archivos, enlaces locales y exclusión de credenciales; no reemplazan las pruebas ejecutadas en las máquinas virtuales.
