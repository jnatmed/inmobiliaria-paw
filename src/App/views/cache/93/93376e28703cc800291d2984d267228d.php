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

/* login.view.html */
class __TwigTemplate_a5a525990ddee29eeb7a4cc2b4edf73a extends Template
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
        yield from         $this->loadTemplate("parts/head.view.html", "login.view.html", 5)->unwrap()->yield($context);
        // line 6
        yield "</head>

<body class=\"login_bg\">

    <main class=\"login_form_container\">

        <section class=\"login_form_header\">
            <a href=\"/\"><h1 class=\"icono-header-pawperties\">PawPerties</h1></a>
            <h1 class=\"login_title\">INICIO SESIÓN</h1>
        </section>

        <section class=\"login_form\">
            <form action=\"/iniciar-sesion\" method=\"post\">
                <label for=\"email\">Email</label>
                <input required type=\"email\" name=\"email\" id=\"email\" placeholder=\"Ingrese su correo electrónico\">
                
                <label for=\"contrasenia\">Contraseña</label>
                <input required type=\"password\" name=\"contrasenia\" id=\"contrasenia\" placeholder=\"Ingrese su contraseña\">
                
                <input type=\"submit\" value=\"INICIAR SESION\">
            </form>

            <form action=\"/registrarse\" method=\"get\">
                <input type=\"submit\" value=\"REGISTRARSE\">
            </form>

            <a href=\"/recuperar-contrasenia\">¿Olvidaste tu contraseña?</a>

            ";
        // line 34
        if ((array_key_exists("resultado", $context) &&  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["resultado"] ?? null), "error", [], "any", false, false, false, 34)))) {
            // line 35
            yield "                <p class=\"error-registro\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["resultado"] ?? null), "error", [], "any", false, false, false, 35));
            yield "</p>
            ";
        }
        // line 37
        yield "
        </section>

    </main>

</body>

</html>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "login.view.html";
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
        return array (  84 => 37,  78 => 35,  76 => 34,  46 => 6,  44 => 5,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html lang=\"es\">

<head>
    {% include 'parts/head.view.html' %}
</head>

<body class=\"login_bg\">

    <main class=\"login_form_container\">

        <section class=\"login_form_header\">
            <a href=\"/\"><h1 class=\"icono-header-pawperties\">PawPerties</h1></a>
            <h1 class=\"login_title\">INICIO SESIÓN</h1>
        </section>

        <section class=\"login_form\">
            <form action=\"/iniciar-sesion\" method=\"post\">
                <label for=\"email\">Email</label>
                <input required type=\"email\" name=\"email\" id=\"email\" placeholder=\"Ingrese su correo electrónico\">
                
                <label for=\"contrasenia\">Contraseña</label>
                <input required type=\"password\" name=\"contrasenia\" id=\"contrasenia\" placeholder=\"Ingrese su contraseña\">
                
                <input type=\"submit\" value=\"INICIAR SESION\">
            </form>

            <form action=\"/registrarse\" method=\"get\">
                <input type=\"submit\" value=\"REGISTRARSE\">
            </form>

            <a href=\"/recuperar-contrasenia\">¿Olvidaste tu contraseña?</a>

            {% if resultado is defined and resultado.error is not empty %}
                <p class=\"error-registro\">{{ resultado.error|e }}</p>
            {% endif %}

        </section>

    </main>

</body>

</html>
", "login.view.html", "D:\\BackUps\\Documentos\\UNLu\\Programacion Web\\inmobiliaria-paw\\src\\App\\views\\login.view.html");
    }
}
