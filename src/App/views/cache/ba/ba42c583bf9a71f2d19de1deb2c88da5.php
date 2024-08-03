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

/* parts/footer.view.html */
class __TwigTemplate_60fef3b4a44ccf6028bb00db8beb502e extends Template
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
        yield "<footer>
    <section class=\"footer_secciones\">
        <nav>
            <ul>
                <li><a href=\"#Ofertas\">Ofertas Imperdibles</a></li>
                <li><a href=\"#Ofertas\">Destinos</a></li>
            </ul>
        </nav>

        <nav>
            <ul>
                <li><a href=\"#Ofertas\">Gestiona tu alojamiento</a></li>
                <li><a href=\"#Ofertas\">Nosotros</a></li>
                <li><a href=\"#Ofertas\">Contacto</a></li>
            </ul>
        </nav>
    </section>
    <section class=\"footer_redes\">
        <p>PAWPERTIES</p>
        <nav>
            <ul class=\"pie_pagina link_externos\">
                <li>
                    <ul class=\"redes_sociales\">
                        <li class=\"red_social facebook\">
                            <a href=\"https://www.facebook.com/\" target=\"_blank\" rel=\"noopener\">
                                <img src=\"/assets/imgs/svg/icono-facebook.svg\" alt=\"facebook\">
                            </a>
                        </li>
                        <li class=\"red_social instagram\">
                            <a href=\"https://www.instagram.com/\" target=\"_blank\" rel=\"noopener\">
                                <img src=\"/assets/imgs/svg/icono-instagram.svg\" alt=\"instagram\">
                            </a>
                        </li>
                        <li class=\"red_social twitter\">
                            <a href=\"https://twitter.com/\" target=\"_blank\" rel=\"noopener\">
                                <img src=\"/assets/imgs/svg/icono-x.svg\" alt=\"twitter\">
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </section>
</footer>";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "parts/footer.view.html";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array ();
    }

    public function getSourceContext()
    {
        return new Source("<footer>
    <section class=\"footer_secciones\">
        <nav>
            <ul>
                <li><a href=\"#Ofertas\">Ofertas Imperdibles</a></li>
                <li><a href=\"#Ofertas\">Destinos</a></li>
            </ul>
        </nav>

        <nav>
            <ul>
                <li><a href=\"#Ofertas\">Gestiona tu alojamiento</a></li>
                <li><a href=\"#Ofertas\">Nosotros</a></li>
                <li><a href=\"#Ofertas\">Contacto</a></li>
            </ul>
        </nav>
    </section>
    <section class=\"footer_redes\">
        <p>PAWPERTIES</p>
        <nav>
            <ul class=\"pie_pagina link_externos\">
                <li>
                    <ul class=\"redes_sociales\">
                        <li class=\"red_social facebook\">
                            <a href=\"https://www.facebook.com/\" target=\"_blank\" rel=\"noopener\">
                                <img src=\"/assets/imgs/svg/icono-facebook.svg\" alt=\"facebook\">
                            </a>
                        </li>
                        <li class=\"red_social instagram\">
                            <a href=\"https://www.instagram.com/\" target=\"_blank\" rel=\"noopener\">
                                <img src=\"/assets/imgs/svg/icono-instagram.svg\" alt=\"instagram\">
                            </a>
                        </li>
                        <li class=\"red_social twitter\">
                            <a href=\"https://twitter.com/\" target=\"_blank\" rel=\"noopener\">
                                <img src=\"/assets/imgs/svg/icono-x.svg\" alt=\"twitter\">
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </section>
</footer>", "parts/footer.view.html", "D:\\BackUps\\Documentos\\UNLu\\Programacion Web\\inmobiliaria-paw\\src\\App\\views\\parts\\footer.view.html");
    }
}
