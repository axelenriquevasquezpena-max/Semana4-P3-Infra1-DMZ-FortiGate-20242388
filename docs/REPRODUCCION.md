# Reproducción del laboratorio

## 1. Equipos y red

Preparar GNS3 con una VM FortiGate 7.0.9, dos switches IOSvL2, dos Kali y tres Ubuntu Noble. Las imágenes y licencias se obtienen por separado. Conectar los puertos como indica el README. Configurar VLAN y puertos en los switches usando los archivos de `configs/`, ajustando las MAC sticky si se usan otras máquinas.

Configurar FortiGate desde la GUI según [CONFIGURACION.md](CONFIGURACION.md). El respaldo saneado sirve como referencia; no es una restauración automática lista para usar. La IP WAN y la de gestión dependen de los DHCP del entorno.

En cada Ubuntu, comparar `ip -br link` con `ens33`. La configuración específica de cada host está en `configs/netplan/`. Integrarla en `/etc/netplan/50-cloud-init.yaml`, evitando definiciones duplicadas en otros YAML. Respaldar el archivo existente, aplicar con `sudo netplan try` desde consola y verificar gateway y DNS. Si cloud-init administra la red, revisar su configuración para que el cambio persista. No copiar la dirección de un servidor al otro.

## 2. Servicios

Los servidores necesitan los paquetes correspondientes instalados. La política de salida debe estar operativa para resolver nombres y acceder a los repositorios Ubuntu.

```bash
# En cada servidor web
sudo apt-get update
sudo apt-get install nginx php8.3-fpm php8.3-mysql

# En DB-P2
sudo apt-get update
sudo apt-get install mariadb-server
```

## 3. Base de datos (DB-P2)

Instalar `configs/mariadb-dmz.cnf` como `/etc/mysql/mariadb.conf.d/60-p2.cnf`. Contiene bind-address `10.23.88.132` y skip-name-resolve. Reiniciar MariaDB y comprobar el servicio.

**Importar el esquema únicamente en una base nueva o vacía:** la exportación incluye `DROP TABLE IF EXISTS` y reemplaza las tablas productos y ventas. No contiene los datos mostrados en las capturas.

```bash
sudo mariadb -e "CREATE DATABASE laboratorio_p2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mariadb laboratorio_p2 < configs/laboratorio_p2-esquema.sql
```

Adaptar `configs/usuarios-db.sql.example` en un archivo local privado con dos contraseñas nuevas y diferentes. Ejecutarlo en MariaDB. La plantilla limita cada usuario a la IP de su aplicación. No publicar ese archivo editado ni incluir contraseñas en comandos del historial.

## 4. Aplicaciones

En WEB-CAJA instalar `apps/caja.php` en `/var/www/p2/caja.php`. En WEB-INVENTARIO instalar `apps/inventario.php` en `/var/www/p2/inventario.php`.

En cada servidor crear `/etc/lab-app/database.php` a partir de la plantilla. Configurar `caja_app` en Caja e `inventario_app` en Inventario, con su contraseña correspondiente. Mantener el archivo fuera de `/var/www` y legible únicamente para root y el grupo del servicio PHP (www-data en este laboratorio): directorio root:www-data 750 y archivo root:www-data 640.

Instalar cada archivo `nginx-*.conf` en `/etc/nginx/sites-available/` y habilitar su enlace en `sites-enabled`. Revisar sitios existentes para evitar conflictos de listen/server_name. Verificar que PHP FPM exponga `/run/php/php8.3-fpm.sock`.

```bash
sudo nginx -t
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

Los archivos exportados describen los sitios HTTP del laboratorio. No son una exportación completa de toda la configuración de Nginx ni deshabilitan por sí solos otros sitios existentes.

## 5. Datos y comprobación funcional

Desde Kali de administración abrir `http://10.23.88.131/inventario.php` y registrar un producto con precio y existencias. Abrir `http://10.23.88.130/caja.php`, registrar una venta y revisar la venta y la reducción de existencias. Caja ejecuta el descuento y el registro dentro de una transacción.

Repetir las [pruebas de seguridad](PRUEBAS.md), guardar los switches con `copy running-config startup-config` y exportar un respaldo de FortiGate desde la GUI. Conservar los respaldos privados originales fuera del repositorio público.
