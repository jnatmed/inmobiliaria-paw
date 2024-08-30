# Sistema para una Inmobiliaria

## Propuesta

Este sistema está diseñado para gestionar propiedades inmobiliarias con diferentes roles de usuario y funcionalidades específicas para mejorar la experiencia tanto de los inquilinos como de los propietarios y empleados.

## Funcionalidades

### 1. Gestión de Propiedades

- **Registro de Propiedades:** Permite registrar y almacenar detalles de propiedades como ubicación, tamaño, características, imágenes, precio, etc.
- **Categorías:** Las propiedades se pueden categorizar por tipo (casa, apartamento, etc.) y ubicación.
- **Búsqueda Avanzada:** Los clientes pueden buscar propiedades según criterios específicos como precio, ubicación, tipo, etc.
- **Etiquetado:** Las propiedades disponibles pueden ser etiquetadas para facilitar su identificación.

### 2. Gestión de Publicaciones

- **Roles de Usuario:**
  - **Inquilino:** Puede ver propiedades y solicitar reservas. Recibe notificaciones por correo electrónico sobre la solicitud de reserva, y tanto el inquilino como el propietario de la publicación son notificados mediante la API de correo de Google.
  - **Propietario:** Puede publicar propiedades y solicitar reservas en publicaciones de otros propietarios. También recibe notificaciones por correo electrónico sobre las solicitudes de reserva.
  - **Empleado:** Gestiona las propiedades que el propietario solicita dar de alta. La comunicación entre el propietario y el empleado se realiza por correo electrónico, y ambos usuarios son notificados cuando cambia el estado de una publicación.

### 3. Gestión de Usuarios

- **Usuarios por Defecto:** Hay tres usuarios predeterminados para pruebas. Para probar la comunicación por correo, cambia el seeder en cada tipo de usuario con correos individuales para realizar las pruebas. Se requiere al menos dos correos distintos para los perfiles de propietario y empleado.

### 4. Vistas

- **Publicaciones:** Vista general de todas las publicaciones.
- **Mis Publicaciones:** Vista específica para propietarios que muestra sus propias publicaciones.
- **Gestionar Reservas:** Vista para manejar las reservas solicitadas por otros propietarios o inquilinos.
- **Gestionar Propiedades:** Vista para empleados para cambiar el estado de aceptación de una publicación.
- **Reseteo de Contraseña:** Permite probar la funcionalidad de reseteo de contraseña. Asegúrate de haber ingresado una contraseña válida en el seeder.
- **Mapa:** Permite ingresar una dirección y ver las propiedades activas en el mapa.

## Técnicas de SEO

- **GEOIp:** Implementación para mejorar la localización geográfica de los usuarios.
- **Tema Cookies:** Manejo de sesiones para la última ubicación del usuario.

## Mapa del Sitio

1. Inicio
2. Unirte al Equipo
3. Contacto
4. Seguimiento (Usuario Empleado)
