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

/* parts/reservas-propiedad.view.html */
class __TwigTemplate_2f5ef88166d35bd0631b0fba54434db6 extends Template
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
        if ((array_key_exists("resultadoReserva", $context) && CoreExtension::getAttribute($this->env, $this->source, ($context["resultadoReserva"] ?? null), "exito", [], "any", true, true, false, 1))) {
            // line 2
            yield "    ";
            if (CoreExtension::getAttribute($this->env, $this->source, ($context["resultadoReserva"] ?? null), "exito", [], "any", false, false, false, 2)) {
                // line 3
                yield "        <p class=\"msj_reserva msj_exito\">
            ";
                // line 4
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["resultadoReserva"] ?? null), "mensaje", [], "any", false, false, false, 4), "html", null, true);
                yield "
        </p>
    ";
            } else {
                // line 7
                yield "        <p class=\"msj_reserva msj_error\">
            ";
                // line 8
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["resultadoReserva"] ?? null), "mensaje", [], "any", false, false, false, 8), "html", null, true);
                yield "
        </p>
    ";
            }
        }
        // line 12
        yield "
<section class=\"container-reserva\">
    <article class=\"container-form\">
        <form action=\"/publicacion/reservar\" class=\"form-reserva\" method=\"POST\"> 
            <input type=\"text\" name=\"id_publicacion\" value=\"";
        // line 16
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["publicacion"] ?? null), "id", [], "any", false, false, false, 16), "html", null, true);
        yield "\" hidden>
            <label for=\"input-desde\" class=\"lbl-desde\">Desde</label>
            <input type=\"date\" name=\"input-desde\" id=\"input-desde\" class=\"input-form-reserva\">
            <label for=\"input-hasta\" class=\"lbl-hasta\">Hasta</label>
            <input type=\"date\" name=\"input-hasta\" id=\"input-hasta\" class=\"input-form-reserva\">
            <input type=\"submit\" value=\"Reservar Periodo\" class=\"btn-form reservar\">
            <input type=\"submit\" value=\"Cancelar\" class=\"btn-form cancelar\">
        </form>
    </article>
    <article id=\"calendarContainer\" class=\"calendarContainer\">
        <input type=\"text\" id=\"periodos\" value=\"";
        // line 26
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["periodos_json"] ?? null), "html", null, true);
        yield "\" hidden>
        <input type=\"text\" id=\"monthInput\" placeholder=\"Introduce el mes (ej: marzo)\" hidden>
        <button id=\"showCalendarButton\" hidden>Mostrar Calendario</button>
        <div id=\"calendarNavigation\">
            <button id=\"prevMonthButton\" disabled>←</button>
            <button id=\"nextMonthButton\">→</button>
        </div>
        <h3 id=\"calendarTitle\" class=\"calendarTitle\"></h3>
        <table id=\"calendarTable\" class=\"greenTable\">
            <thead>
                <tr>
                    <th>Lunes</th>
                    <th>Martes</th>
                    <th>Miércoles</th>
                    <th>Jueves</th>
                    <th>Viernes</th>
                    <th>Sábado</th>
                    <th>Domingo</th>
                </tr>
            </thead>
            <tbody>
                ";
        // line 47
        $context["currentYear"] = $this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "Y");
        // line 48
        yield "                ";
        $context["currentMonth"] = ($this->env->getFilter('int')->getCallable()($this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "m")) - 1);
        // line 49
        yield "                
                ";
        // line 51
        yield "                ";
        $context["firstDay"] = $this->env->getFilter('date_create')->getCallable()((((($context["currentYear"] ?? null) . "-") . $this->env->getFilter('format')->getCallable()((($context["currentMonth"] ?? null) + 1), "%02d")) . "-01"));
        // line 52
        yield "                ";
        $context["firstDayOfWeek"] = ($this->env->getFilter('int')->getCallable()($this->extensions['Twig\Extension\CoreExtension']->formatDate(($context["firstDay"] ?? null), "N")) - 1);
        // line 53
        yield "                ";
        $context["daysInMonth"] = $this->extensions['Twig\Extension\CoreExtension']->formatDate(($context["firstDay"] ?? null), "t");
        // line 54
        yield "
                ";
        // line 55
        $context["day"] = 1;
        // line 56
        yield "                ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(range(0, 5));
        foreach ($context['_seq'] as $context["_key"] => $context["i"]) {
            // line 57
            yield "                    <tr>
                        ";
            // line 58
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(range(0, 6));
            foreach ($context['_seq'] as $context["_key"] => $context["j"]) {
                // line 59
                yield "                            ";
                if ((($context["i"] == 0) && ($context["j"] < ($context["firstDayOfWeek"] ?? null)))) {
                    // line 60
                    yield "                                <td></td>
                            ";
                } elseif ((                // line 61
($context["day"] ?? null) <= ($context["daysInMonth"] ?? null))) {
                    // line 62
                    yield "                                ";
                    $context["highlight"] = "";
                    // line 63
                    yield "                                ";
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(($context["reservas"] ?? null));
                    foreach ($context['_seq'] as $context["_key"] => $context["intervalo"]) {
                        // line 64
                        yield "                                    ";
                        $context["start"] = $this->env->getFilter('date_create')->getCallable()((($__internal_compile_0 = $context["intervalo"]) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0[0] ?? null) : null), "d/m/Y");
                        // line 65
                        yield "                                    ";
                        $context["end"] = $this->env->getFilter('date_create')->getCallable()((($__internal_compile_1 = $context["intervalo"]) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1[1] ?? null) : null), "d/m/Y");
                        // line 66
                        yield "                                    ";
                        $context["current"] = ((((($context["currentYear"] ?? null) . "-") . (($context["currentMonth"] ?? null) + 1)) . "-") . $this->env->getFilter('date_create')->getCallable()(($context["day"] ?? null)));
                        // line 67
                        yield "                                    ";
                        if (((($context["current"] ?? null) >= ($context["start"] ?? null)) && (($context["current"] ?? null) <= ($context["end"] ?? null)))) {
                            // line 68
                            yield "                                        ";
                            $context["highlight"] = "highlight";
                            // line 69
                            yield "                                    ";
                        }
                        // line 70
                        yield "                                ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['intervalo'], $context['_parent'], $context['loop']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 71
                    yield "                                <td class=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["highlight"] ?? null), "html", null, true);
                    yield "\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["day"] ?? null), "html", null, true);
                    yield "</td>
                                ";
                    // line 72
                    $context["day"] = (($context["day"] ?? null) + 1);
                    // line 73
                    yield "                            ";
                } else {
                    // line 74
                    yield "                                <td></td>
                            ";
                }
                // line 76
                yield "                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['j'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 77
            yield "                    </tr>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['i'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 79
        yield "            </tbody>
        </table>
    </article>
</section>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "parts/reservas-propiedad.view.html";
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
        return array (  208 => 79,  201 => 77,  195 => 76,  191 => 74,  188 => 73,  186 => 72,  179 => 71,  173 => 70,  170 => 69,  167 => 68,  164 => 67,  161 => 66,  158 => 65,  155 => 64,  150 => 63,  147 => 62,  145 => 61,  142 => 60,  139 => 59,  135 => 58,  132 => 57,  127 => 56,  125 => 55,  122 => 54,  119 => 53,  116 => 52,  113 => 51,  110 => 49,  107 => 48,  105 => 47,  81 => 26,  68 => 16,  62 => 12,  55 => 8,  52 => 7,  46 => 4,  43 => 3,  40 => 2,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% if resultadoReserva is defined and resultadoReserva.exito is defined %}
    {% if resultadoReserva.exito %}
        <p class=\"msj_reserva msj_exito\">
            {{ resultadoReserva.mensaje }}
        </p>
    {% else %}
        <p class=\"msj_reserva msj_error\">
            {{ resultadoReserva.mensaje }}
        </p>
    {% endif %}
{% endif %}

<section class=\"container-reserva\">
    <article class=\"container-form\">
        <form action=\"/publicacion/reservar\" class=\"form-reserva\" method=\"POST\"> 
            <input type=\"text\" name=\"id_publicacion\" value=\"{{ publicacion.id }}\" hidden>
            <label for=\"input-desde\" class=\"lbl-desde\">Desde</label>
            <input type=\"date\" name=\"input-desde\" id=\"input-desde\" class=\"input-form-reserva\">
            <label for=\"input-hasta\" class=\"lbl-hasta\">Hasta</label>
            <input type=\"date\" name=\"input-hasta\" id=\"input-hasta\" class=\"input-form-reserva\">
            <input type=\"submit\" value=\"Reservar Periodo\" class=\"btn-form reservar\">
            <input type=\"submit\" value=\"Cancelar\" class=\"btn-form cancelar\">
        </form>
    </article>
    <article id=\"calendarContainer\" class=\"calendarContainer\">
        <input type=\"text\" id=\"periodos\" value=\"{{ periodos_json }}\" hidden>
        <input type=\"text\" id=\"monthInput\" placeholder=\"Introduce el mes (ej: marzo)\" hidden>
        <button id=\"showCalendarButton\" hidden>Mostrar Calendario</button>
        <div id=\"calendarNavigation\">
            <button id=\"prevMonthButton\" disabled>←</button>
            <button id=\"nextMonthButton\">→</button>
        </div>
        <h3 id=\"calendarTitle\" class=\"calendarTitle\"></h3>
        <table id=\"calendarTable\" class=\"greenTable\">
            <thead>
                <tr>
                    <th>Lunes</th>
                    <th>Martes</th>
                    <th>Miércoles</th>
                    <th>Jueves</th>
                    <th>Viernes</th>
                    <th>Sábado</th>
                    <th>Domingo</th>
                </tr>
            </thead>
            <tbody>
                {% set currentYear = \"now\"|date(\"Y\") %}
                {% set currentMonth = \"now\"|date(\"m\")|int - 1 %}
                
                {# Crear el primer día del mes para el cálculo #}
                {% set firstDay = (currentYear ~ '-' ~ (currentMonth + 1)|format('%02d') ~ '-01')|date_create %}
                {% set firstDayOfWeek = firstDay|date(\"N\")|int - 1 %}
                {% set daysInMonth = firstDay|date(\"t\") %}

                {% set day = 1 %}
                {% for i in 0..5 %}
                    <tr>
                        {% for j in 0..6 %}
                            {% if i == 0 and j < firstDayOfWeek %}
                                <td></td>
                            {% elseif day <= daysInMonth %}
                                {% set highlight = '' %}
                                {% for intervalo in reservas %}
                                    {% set start = intervalo[0]|date_create('d/m/Y') %}
                                    {% set end = intervalo[1]|date_create('d/m/Y') %}
                                    {% set current = currentYear ~ '-' ~ (currentMonth + 1) ~ '-' ~ day|date_create %}
                                    {% if current >= start and current <= end %}
                                        {% set highlight = 'highlight' %}
                                    {% endif %}
                                {% endfor %}
                                <td class=\"{{ highlight }}\">{{ day }}</td>
                                {% set day = day + 1 %}
                            {% else %}
                                <td></td>
                            {% endif %}
                        {% endfor %}
                    </tr>
                {% endfor %}
            </tbody>
        </table>
    </article>
</section>
", "parts/reservas-propiedad.view.html", "D:\\BackUps\\Documentos\\UNLu\\Programacion Web\\inmobiliaria-paw\\src\\App\\views\\parts\\reservas-propiedad.view.html");
    }
}
