<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* publicacion.details.view.html */
class __TwigTemplate_b995e96a5c6f2b6926584b7b4ea9e7ee extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html lang=\"es\">

<head>
    ";
        // line 5
        yield from         $this->loadTemplate("parts/head.view.html", "publicacion.details.view.html", 5)->unwrap()->yield($context);
        // line 6
        yield "    <link rel=\"stylesheet\" href=\"/assets/css/reservas-calendario.css\">
    <script src=\"/assets/js/reservas-calendario.js\"></script>
    <script src=\"/assets/js/publicacion.details.js\"></script>
</head>

<body class=\"home\">
    ";
        // line 12
        yield from         $this->loadTemplate("parts/header.view.html", "publicacion.details.view.html", 12)->unwrap()->yield($context);
        // line 13
        yield "
    <main>
        <!-- imagenes del departamento en alquiler-->
        <section class=\"container-imagenes-publicacion\">
            <h2 class=\"titulo-imagenes-detail\">Detalles Publicacion</h2>
            <ul class=\"ul-imagenes-publicacion\">
                ";
        // line 19
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "imagenes", [], "any", false, false, false, 19));
        foreach ($context['_seq'] as $context["_key"] => $context["imagen"]) {
            // line 20
            yield "                    <li class=\"li-imagen-publicacion\">
                        <img class=\"carousel-img\"
                             src=\"/publicacion?id_pub=";
            // line 22
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "id", [], "any", false, false, false, 22), "html", null, true);
            yield "&id_img=";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["imagen"], "id_imagen", [], "any", false, false, false, 22), "html", null, true);
            yield "\"
                             alt=\"";
            // line 23
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["imagen"], "nombre_imagen", [], "any", false, false, false, 23), "html", null, true);
            yield "\">
                    </li>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['imagen'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 26
        yield "            </ul>
        </section>
        <!-- detalles de la publicacion -->
        <section class=\"details-depto\">
            <h3 class=\"titulo-details\">
                Bernardo de Irigoyen
                <p class=\"precio-publicacion\">
                    USD 1.200
                </p>
            </h3>
            <article class=\"article-details-texto\">
                <h3 class=\"titulo-details-texto\">
                    ";
        // line 38
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "nombre_alojamiento", [], "any", false, false, false, 38), "html", null, true);
        yield "
                </h3>
                <p class=\"p-details-texto\">
                    ";
        // line 41
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "descripcion_alojamiento", [], "any", false, false, false, 41), "html", null, true);
        yield "
                </p>
            </article>
            <!-- formulario de contacto  -->
            <article class=\"form-contacto-publicaciom\">
                <h4 class=\"h4-titulo-form\">
                    Contactá al Dueño
                </h4>
                <form action=\"/publicacion/contactar-al-duenio-form?id_pub=";
        // line 49
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "id", [], "any", false, false, false, 49), "html", null, true);
        yield "\" method=\"post\">
                    <input type=\"text\" value=\"";
        // line 50
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["app"] ?? null), "request", [], "any", false, false, false, 50), "uri", [], "any", false, false, false, 50), "html", null, true);
        yield "\" name=\"urlPublicacion\" hidden>
                    <input type=\"text\" value=\"";
        // line 51
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "email", [], "any", false, false, false, 51), "html", null, true);
        yield "\" name=\"emailDuenio\" hidden>
                    <input type=\"email\" class=\"input-form\" placeholder=\"Email: *\" name=\"email-interesado\" required>
                    <input type=\"tel\" placeholder=\"Telefono: *\" name=\"telefono-interesado\" required>
                    <textarea name=\"texto-consulta\" placeholder=\"Mensaje *\">\"Hola, vi esta propiedad en PawProperties y quiero que me contacten. Gracias.\"</textarea>
                    <input type=\"submit\" value=\"Contactar\" class=\"btn-contactar\">
                </form>
                <a class=\"whatsapp-link\" href=\"https://wa.me/";
        // line 57
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "telefono", [], "any", false, false, false, 57));
        yield "/?text=Hola, vi esta propiedad en Pawproperties y quiero más información por WhatsApp. ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["app"] ?? null), "request", [], "any", false, false, false, 57), "uri", [], "any", false, false, false, 57), "html", null, true);
        yield "\" target=\"_blank\">
                    <img src=\"/assets/imgs/svg/whatsapp-icon.png\" alt=\"WhatsApp\">
                </a>
                <a class=\"whatsapp-link\" href=\"https://api.whatsapp.com/send/?phone=";
        // line 60
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "telefono", [], "any", false, false, false, 60));
        yield "&text=Hola%2C+vi+esta+propiedad+en+Pawproperties+y+quiero+m%C3%A1s+informaci%C3%B3n+por+WhatsApp.+";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["app"] ?? null), "request", [], "any", false, false, false, 60), "uri", [], "any", false, false, false, 60), "html", null, true);
        yield "&type=phone_number&app_absent=0\" target=\"_blank\">
                    <img src=\"/assets/imgs/svg/whatsapp-icon.png\" alt=\"WhatsApp\">
                </a>

                <p>Al enviar estas aceptando los
                    <a href=\"/publicacion/terminos-y-condiciones\">términos y condiciones</a>
                </p>
                <hr>
                <article class=\"publicaciones-duenio\">
                    <h5 class=\"h5-nombre-duenia\">";
        // line 69
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "nombre", [], "any", false, false, false, 69), "html", null, true);
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "apellido", [], "any", false, false, false, 69), "html", null, true);
        yield "</h5>
                    <a href=\"/publicaciones/lista?id-duenio\" class=\"link-avisos-duenio\"></a>
                    <p>Ver teléfono</p>
                </article>
            </article>
            <!-- mapa donde figura la ubicacion del alquiler -->
            <article class=\"ubicacion-publicacion\">
                <h3 class=\"titulo-ubicacion\">Ubicacion</h3>
                <p>Bernardo de Irigoyen 700, Piso 18
                    San Telmo, Capital Federal
                </p>
                <input type=\"text\" id=\"latitud\" value=\"";
        // line 80
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "latitud", [], "any", false, false, false, 80), "html", null, true);
        yield "\" hidden>
                <input type=\"text\" id=\"longitud\" value=\"";
        // line 81
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "longitud", [], "any", false, false, false, 81), "html", null, true);
        yield "\" hidden>
                <div id=\"mapid\"></div>
            </article>
        </section>


        <details>
            ";
        // line 88
        if (($context["isUserLoggedIn"] ?? null)) {
            // line 89
            yield "                ";
            yield from             $this->loadTemplate("parts/reservas-propiedad.view.html", "publicacion.details.view.html", 89)->unwrap()->yield($context);
            // line 90
            yield "            ";
        }
        // line 91
        yield "        </details>
    </main>

    ";
        // line 94
        yield from         $this->loadTemplate("parts/footer.view.html", "publicacion.details.view.html", 94)->unwrap()->yield($context);
        // line 95
        yield "</body>

</html>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "publicacion.details.view.html";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  202 => 95,  200 => 94,  195 => 91,  192 => 90,  189 => 89,  187 => 88,  177 => 81,  173 => 80,  157 => 69,  143 => 60,  135 => 57,  126 => 51,  122 => 50,  118 => 49,  107 => 41,  101 => 38,  87 => 26,  78 => 23,  72 => 22,  68 => 20,  64 => 19,  56 => 13,  54 => 12,  46 => 6,  44 => 5,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html lang=\"es\">

<head>
    {% include 'parts/head.view.html' %}
    <link rel=\"stylesheet\" href=\"/assets/css/reservas-calendario.css\">
    <script src=\"/assets/js/reservas-calendario.js\"></script>
    <script src=\"/assets/js/publicacion.details.js\"></script>
</head>

<body class=\"home\">
    {% include 'parts/header.view.html' %}

    <main>
        <!-- imagenes del departamento en alquiler-->
        <section class=\"container-imagenes-publicacion\">
            <h2 class=\"titulo-imagenes-detail\">Detalles Publicacion</h2>
            <ul class=\"ul-imagenes-publicacion\">
                {% for imagen in publicacion.imagenes %}
                    <li class=\"li-imagen-publicacion\">
                        <img class=\"carousel-img\"
                             src=\"/publicacion?id_pub={{ publicacion.id }}&id_img={{ imagen.id_imagen }}\"
                             alt=\"{{ imagen.nombre_imagen }}\">
                    </li>
                {% endfor %}
            </ul>
        </section>
        <!-- detalles de la publicacion -->
        <section class=\"details-depto\">
            <h3 class=\"titulo-details\">
                Bernardo de Irigoyen
                <p class=\"precio-publicacion\">
                    USD 1.200
                </p>
            </h3>
            <article class=\"article-details-texto\">
                <h3 class=\"titulo-details-texto\">
                    {{ publicacion.nombre_alojamiento }}
                </h3>
                <p class=\"p-details-texto\">
                    {{ publicacion.descripcion_alojamiento }}
                </p>
            </article>
            <!-- formulario de contacto  -->
            <article class=\"form-contacto-publicaciom\">
                <h4 class=\"h4-titulo-form\">
                    Contactá al Dueño
                </h4>
                <form action=\"/publicacion/contactar-al-duenio-form?id_pub={{ publicacion.id }}\" method=\"post\">
                    <input type=\"text\" value=\"{{ app.request.uri }}\" name=\"urlPublicacion\" hidden>
                    <input type=\"text\" value=\"{{ publicacion.email }}\" name=\"emailDuenio\" hidden>
                    <input type=\"email\" class=\"input-form\" placeholder=\"Email: *\" name=\"email-interesado\" required>
                    <input type=\"tel\" placeholder=\"Telefono: *\" name=\"telefono-interesado\" required>
                    <textarea name=\"texto-consulta\" placeholder=\"Mensaje *\">\"Hola, vi esta propiedad en PawProperties y quiero que me contacten. Gracias.\"</textarea>
                    <input type=\"submit\" value=\"Contactar\" class=\"btn-contactar\">
                </form>
                <a class=\"whatsapp-link\" href=\"https://wa.me/{{ publicacion.telefono | e }}/?text=Hola, vi esta propiedad en Pawproperties y quiero más información por WhatsApp. {{ app.request.uri }}\" target=\"_blank\">
                    <img src=\"/assets/imgs/svg/whatsapp-icon.png\" alt=\"WhatsApp\">
                </a>
                <a class=\"whatsapp-link\" href=\"https://api.whatsapp.com/send/?phone={{ publicacion.telefono | e }}&text=Hola%2C+vi+esta+propiedad+en+Pawproperties+y+quiero+m%C3%A1s+informaci%C3%B3n+por+WhatsApp.+{{ app.request.uri }}&type=phone_number&app_absent=0\" target=\"_blank\">
                    <img src=\"/assets/imgs/svg/whatsapp-icon.png\" alt=\"WhatsApp\">
                </a>

                <p>Al enviar estas aceptando los
                    <a href=\"/publicacion/terminos-y-condiciones\">términos y condiciones</a>
                </p>
                <hr>
                <article class=\"publicaciones-duenio\">
                    <h5 class=\"h5-nombre-duenia\">{{ publicacion.nombre }} {{ publicacion.apellido }}</h5>
                    <a href=\"/publicaciones/lista?id-duenio\" class=\"link-avisos-duenio\"></a>
                    <p>Ver teléfono</p>
                </article>
            </article>
            <!-- mapa donde figura la ubicacion del alquiler -->
            <article class=\"ubicacion-publicacion\">
                <h3 class=\"titulo-ubicacion\">Ubicacion</h3>
                <p>Bernardo de Irigoyen 700, Piso 18
                    San Telmo, Capital Federal
                </p>
                <input type=\"text\" id=\"latitud\" value=\"{{ publicacion.latitud }}\" hidden>
                <input type=\"text\" id=\"longitud\" value=\"{{ publicacion.longitud }}\" hidden>
                <div id=\"mapid\"></div>
            </article>
        </section>


        <details>
            {% if isUserLoggedIn  %}
                {% include 'parts/reservas-propiedad.view.html' %}
            {% endif %}
        </details>
    </main>

    {% include 'parts/footer.view.html' %}
</body>

</html>
", "publicacion.details.view.html", "D:\\BackUps\\Documentos\\UNLu\\Programacion Web\\inmobiliaria-paw\\src\\App\\views\\publicacion.details.view.html");
    }
}
