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

/* parts/head.view.html */
class __TwigTemplate_c124368ef002f471529112a04879b6de extends Template
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
        yield "<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
<link rel=\"stylesheet\" href=\"/assets/css/base.css\">
<link rel=\"stylesheet\" href=\"/assets/css/publicacion.css\">
<link rel=\"stylesheet\" href=\"/assets/css/detalle-publicacion.css\">
<link rel=\"stylesheet\" href=\"/assets/css/login.css\">
<link rel=\"stylesheet\" href=\"/assets/css/footer.css\">
<link rel=\"stylesheet\" href=\"/assets/css/logout.css\">
<link rel=\"stylesheet\" href=\"/assets/css/filtro.css\">
<link rel=\"stylesheet\" href=\"/assets/css/not-found.css\">
<link rel=\"stylesheet\" href=\"/assets/css/drag-drop.css\">


<link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.7.1/dist/leaflet.css\" />
<script src=\"https://unpkg.com/leaflet@1.7.1/dist/leaflet.js\"></script>

<link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.css\" integrity=\"sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=\" crossorigin=\"\" /> 
<script src=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.js\" integrity=\"sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=\" crossorigin=\"\"></script>

<!-- ICONOS DE FLATICON -->
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-solid-rounded/css/uicons-solid-rounded.css'>

<script src=\"/assets/js/components/paw.js\"></script>
<script src=\"/assets/js/components/boton-salir.js\"></script>

<title>";
        // line 26
        (((array_key_exists("titulo", $context) &&  !(null === ($context["titulo"] ?? null)))) ? (yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["titulo"] ?? null), "html", null, true)) : (yield "Proyecto PawPerties"));
        yield "</title>

";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "parts/head.view.html";
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
        return array (  65 => 26,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
<link rel=\"stylesheet\" href=\"/assets/css/base.css\">
<link rel=\"stylesheet\" href=\"/assets/css/publicacion.css\">
<link rel=\"stylesheet\" href=\"/assets/css/detalle-publicacion.css\">
<link rel=\"stylesheet\" href=\"/assets/css/login.css\">
<link rel=\"stylesheet\" href=\"/assets/css/footer.css\">
<link rel=\"stylesheet\" href=\"/assets/css/logout.css\">
<link rel=\"stylesheet\" href=\"/assets/css/filtro.css\">
<link rel=\"stylesheet\" href=\"/assets/css/not-found.css\">
<link rel=\"stylesheet\" href=\"/assets/css/drag-drop.css\">


<link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.7.1/dist/leaflet.css\" />
<script src=\"https://unpkg.com/leaflet@1.7.1/dist/leaflet.js\"></script>

<link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.css\" integrity=\"sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=\" crossorigin=\"\" /> 
<script src=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.js\" integrity=\"sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=\" crossorigin=\"\"></script>

<!-- ICONOS DE FLATICON -->
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-solid-rounded/css/uicons-solid-rounded.css'>

<script src=\"/assets/js/components/paw.js\"></script>
<script src=\"/assets/js/components/boton-salir.js\"></script>

<title>{{ titulo ?? \"Proyecto PawPerties\" }}</title>

", "parts/head.view.html", "D:\\BackUps\\Documentos\\UNLu\\Programacion Web\\inmobiliaria-paw\\src\\App\\views\\parts\\head.view.html");
    }
}
