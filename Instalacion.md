### 1) INSTALACION DE MYSQL 

* Descargar e Instalar MySQL del siguiente [link](https://dev.mysql.com/downloads/file/?id=526407). 
* Copiar las variables de ambiente del archivo `.env.example` a un nuevo archivo llamado `.env`.
    - `.env`
* Crear la base de datos `mvc-paw-power`.

### 2) INSTALACION DE PHINX 

- `composer require robmorgan/phinx`
Luego ejecutar `phinx` desde `vendor/bin/phinx`
- `vendor/bin/phinx migration `
* Ejecutar migrations: `vendor/bin/phinx migrate -e development`.

### 3) INSTALACION DE PHP: 

* Descargar PHP desde el siguiente [link](https://windows.php.net/downloads/releases/php-8.3.6-nts-Win32-vs16-x64.zip).
    - Descomprimir el archivo zip y cambiar el nombre a `php`.
    - Mover la carpeta a `c:\\php`.
    - Agregar dicha ruta a las variables de entorno.

#### 3.1) CONFIGURACION DEL ARCHIVO `php.ini`

* Descomentar las siguientes líneas en `php.ini`:
    - `extension=fileinfo`.
    - `extension=pdo_mysql`.
* Aumentar tamaño de `upload_max_filesize` a 10M:
    - `upload_max_filesize = 10M`.

### 4) INSTALACION DE COMPOSER

* Para sistemas Windows:
    - Descargar el instalador del siguiente [enlace](https://getcomposer.org/Composer-Setup.exe).

### 5) Instalacion y Ejecucion del Proyecto

* Clonar el Proyecto: `git clone https://github.com/lucasrueda01/PAW-2024.git`.
* `cd PAW-2024`.
* `composer install`.
* `cp .env.example .env` # Editar el .env con los valores deseados.
* Ejecutar migrations: `phinx migrate -e development`.
* Crear la carpeta `public/uploads/` para el guardado de las imágenes.
* Ejecutar `php -S localhost:8080 -t public/`.

### 6) Instalacion de Ngrok 

- Para presentar el sistema, se puede usar *ngrok*. Para su instalación es necesario tener `chocolatey` incorporado en el sistema, el cual se puede instalar siguiendo los pasos del [link de la página](https://chocolatey.org/install).

* Instalar ngrok desde el siguiente [link](https://ngrok.com/download).
    - Iniciar sesión en la página para obtener la llave.
* Para su uso, ejecutar el siguiente comando: `ngrok http 8080`. 
