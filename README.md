# PROYECTO FINAL CURSADA 2024: Inmobiliaria PAW

 Proyecto Final de Cursada PAW-2024

- El proyecto que se propone es la Web para una inmobiliaria que tenga como principal función la compra, venta y alquiler de inmuebles, incorporando como experiencia de usuario un mapa que le permita al mismo ver las propiedades que estan cerca de la ubicacion en la que se encuentre o que se defina. Además, se busca trabajar con cookies a los efectos de dar una mejor experincia de usuario.

## [Site MAP del sitio](https://www.figma.com/file/f7et6OtnD4UQtiVNiBge5e/wireframe-%2F-inmobiliaria-paw?type=design&node-id=10%3A2&mode=dev&t=ifRSzAKGyPJI4I4V-1)

## [Wireframe del Sitio](https://www.figma.com/file/f7et6OtnD4UQtiVNiBge5e/wireframe-%2F-inmobiliaria-paw?type=design&node-id=0-1&mode=design&t=eMePNkVMlsDYcH7P-0)

## [REQUISITOS FUNCIONAL DEL PROYECTO](requisitos-y-funcionalidades.md)

## [PASOS PARA SU INSTALACION](Instalacion.md)

## Roles principales

El sistema contempla tres tipos de usuario:

- **Propietario / usuario común:** puede publicar propiedades, administrar sus publicaciones y realizar reservas sobre propiedades de otros usuarios.
- **Empleado:** puede moderar las publicaciones enviadas por los usuarios y decidir si son aceptadas o rechazadas.
- **Inquilino:** tipo de usuario contemplado en el modelo de datos. El sistema comparte distintas funcionalidades de publicación y reserva entre los usuarios comunes de acuerdo con los permisos implementados.

## Tecnologías utilizadas

- PHP.
- MySQL.
- Twig.
- JavaScript vanilla.
- HTML.
- CSS.
- Leaflet.
- Nominatim.
- Composer.
- Phinx.
- Monolog.
- PHPMailer.
- Dotenv.
- Whoops.

## Datos de prueba

Los seeders incluyen usuarios que permiten probar los principales flujos de la aplicación.

### Usuario común

```text
Email: usuario1@example.com
Contraseña: password1
```

### Empleado

```text
Email: usuario2@example.com
Contraseña: password2
```

### Segundo usuario común

```text
Email: usuario3@example.com
Contraseña: password3
```

Para obtener las instrucciones completas de instalación consultar:

[Instalacion.md](Instalacion.md)