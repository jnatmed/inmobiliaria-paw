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

/* parts/header.view.html */
class __TwigTemplate_83df287f7d59dd06bd0d2b4a326342df extends Template
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
        yield "<header> <!--block-->
    <a href=\"/\"><h1 class=\"icono-header-pawperties\">PawPerties</h1></a>
    <nav class=\"nav-index\">
        <ul class=\"lista-nav-main\">
            ";
        // line 5
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["menu"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 6
            yield "            <li>
                <a class=\"anchor-item-main\" href=\"";
            // line 7
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "href", [], "any", false, false, false, 7), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "name", [], "any", false, false, false, 7), "html", null, true);
            yield "</a>
            </li>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 10
        yield "        </ul>
    </nav>
    <nav class=\"nav-sesion\">
        <ul class=\"lista-opciones-sesion\">
            <li class=\"item-opcion-sesion\">
                ";
        // line 15
        if (($context["isUserLoggedIn"] ?? null)) {
            // line 16
            yield "                    ";
            yield from             $this->loadTemplate("button-logout.twig", "parts/header.view.html", 16)->unwrap()->yield($context);
            // line 17
            yield "                ";
        } else {
            // line 18
            yield "                    <a class=\"anchor-item-sesion\" href=\"/iniciar-sesion\">INICIAR SESION</a>
                    <img src=\"/assets/imgs/svg/alt-de-inicio-de-sesion.svg\" alt=\"icono-salida\" class=\"icono-salida\">
                ";
        }
        // line 21
        yield "            </li>
        </ul>
    </nav>
</header>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "parts/header.view.html";
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
        return array (  82 => 21,  77 => 18,  74 => 17,  71 => 16,  69 => 15,  62 => 10,  51 => 7,  48 => 6,  44 => 5,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<header> <!--block-->
    <a href=\"/\"><h1 class=\"icono-header-pawperties\">PawPerties</h1></a>
    <nav class=\"nav-index\">
        <ul class=\"lista-nav-main\">
            {% for item in menu %}
            <li>
                <a class=\"anchor-item-main\" href=\"{{ item.href }}\">{{ item.name }}</a>
            </li>
            {% endfor %}
        </ul>
    </nav>
    <nav class=\"nav-sesion\">
        <ul class=\"lista-opciones-sesion\">
            <li class=\"item-opcion-sesion\">
                {% if isUserLoggedIn %}
                    {% include 'button-logout.twig' %}
                {% else %}
                    <a class=\"anchor-item-sesion\" href=\"/iniciar-sesion\">INICIAR SESION</a>
                    <img src=\"/assets/imgs/svg/alt-de-inicio-de-sesion.svg\" alt=\"icono-salida\" class=\"icono-salida\">
                {% endif %}
            </li>
        </ul>
    </nav>
</header>
", "parts/header.view.html", "D:\\BackUps\\Documentos\\UNLu\\Programacion Web\\inmobiliaria-paw\\src\\App\\views\\parts\\header.view.html");
    }
}
