# GUÍA DE INSTALACIÓN

## 1. Requisitos

Para ejecutar el proyecto se necesita:

- PHP 8.1 o superior.
- MySQL.
- Composer.
- Git.
- Un navegador web moderno.

### Extensiones de PHP necesarias

En el archivo `php.ini` deben estar habilitadas al menos:

```ini
extension=fileinfo
extension=pdo_mysql
```

Para permitir la carga de imágenes del proyecto se recomienda:

```ini
upload_max_filesize = 10M
post_max_size = 12M
```

Después de modificar `php.ini`, reiniciar la terminal o el servidor PHP si corresponde.

---

## 2. Clonar el proyecto

```bash
git clone https://github.com/jnatmed/inmobiliaria-paw.git
cd inmobiliaria-paw
```

---

## 3. Instalar las dependencias

Ejecutar:

```bash
composer install
```

Composer instalará las dependencias necesarias para el proyecto, entre ellas:

- Twig.
- Phinx.
- Monolog.
- PHPMailer.
- Dotenv.
- Whoops.

No es necesario ejecutar `composer require` para instalar Phinx por separado, ya que forma parte de las dependencias del proyecto.

---

## 4. Configurar las variables de entorno

Copiar el archivo:

```text
.env.example
```

como:

```text
.env
```

En Linux o macOS puede utilizarse:

```bash
cp .env.example .env
```

En Windows también puede copiarse manualmente desde el explorador de archivos.

Luego editar `.env` con la configuración local de MySQL.

Ejemplo:

```env
DB_ADAPTER=mysql
DB_HOSTNAME=localhost
DB_DBNAME=mvc-pawperties
DB_USERNAME=root
DB_PASSWORD=
DB_PORT=3306
DB_CHARSET=utf8
```

Las credenciales de correo solamente son necesarias para probar las funcionalidades que envían emails, como la recuperación de contraseña.

---

## 5. Crear la base de datos

Crear en MySQL una base de datos llamada:

```text
mvc-pawperties
```

Si se utiliza otro nombre, modificar en `.env`:

```env
DB_DBNAME=
```

para que coincida con la base creada.

---

## 6. Ejecutar las migrations

Desde la raíz del proyecto ejecutar:

```bash
vendor/bin/phinx migrate -e development
```

En Windows, dependiendo de la terminal utilizada, también puede ejecutarse:

```bash
vendor\bin\phinx migrate -e development
```

Las migrations crean y actualizan las tablas necesarias para la aplicación.

---

## 7. Cargar los datos de prueba

Ejecutar:

```bash
vendor/bin/phinx seed:run -e development
```

En Windows también puede utilizarse:

```bash
vendor\bin\phinx seed:run -e development
```

Los seeders generan usuarios, publicaciones y otros datos necesarios para realizar pruebas del sistema.

### Usuarios principales de prueba

#### Usuario común

```text
Email: usuario1@example.com
Contraseña: password1
```

#### Empleado

```text
Email: usuario2@example.com
Contraseña: password2
```

#### Segundo usuario común

```text
Email: usuario3@example.com
Contraseña: password3
```

Estos usuarios permiten probar los diferentes flujos de publicación, moderación y reservas.

---

## 8. Carpeta de imágenes

Verificar que exista la carpeta:

```text
uploads/
```

Si no existe, crearla.

Esta carpeta se utiliza para almacenar las imágenes cargadas por los usuarios.

La carpeta está excluida del repositorio Git mediante `.gitignore`, por lo que las imágenes subidas durante las pruebas no se versionan.

---

## 9. Ejecutar el proyecto

Desde la raíz del proyecto ejecutar:

```bash
php -S localhost:8080 -t public/
```

Luego abrir en el navegador:

```text
http://localhost:8080/
```

---

## 10. Entorno de ejecución

Durante el desarrollo puede utilizarse:

```env
APP_ENV=development
```

En un entorno publicado debería utilizarse:

```env
APP_ENV=production
```

De esta manera se evita mostrar información técnica detallada sobre errores a los usuarios finales.

---

## 11. Roles del sistema

El proyecto contempla tres tipos de usuario en la base de datos:

```text
1 = Propietario
2 = Empleado
3 = Inquilino
```

En el flujo actual de la aplicación, los usuarios comunes pueden realizar acciones relacionadas con publicaciones y reservas según los permisos implementados.

El empleado tiene acceso a las funciones de moderación de publicaciones.

Los usuarios incluidos en los datos de prueba permiten comprobar los principales flujos del sistema.

---

## 12. Opcional: ngrok

Si se necesita acceder temporalmente al servidor local desde Internet puede utilizarse ngrok.

Con el proyecto ejecutándose en el puerto `8080`:

```bash
ngrok http 8080
```

Ngrok no es necesario para ejecutar normalmente el proyecto.