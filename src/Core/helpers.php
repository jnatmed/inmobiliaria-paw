<?php


/**
 * Require a view.
 *
 * @param  string $name
 * @param  array  $data
 */
function view_error($name, $data = [])
{
    global $log, $twig;

    try {
        // $log->debug('Datos en la vista', [$name, $data, $twig]);
        echo $twig->render("errors/{$name}.html", $data);
    } catch (\Twig\Error\Error $e) {
        $log->error('Error al renderizar la plantilla', ['exception' => $e]);
        echo 'Error al renderizar la plantilla: ' . $e->getMessage();
    }

}


/**
 * Render a view and return the output as a string.
 *
 * @param  string $name
 * @param  array  $data
 * @return string
 */
function render_view($name, $data = [])
{
    global $log, $twig;

    try {
        return $twig->render("{$name}.html", $data);
    } catch (\Twig\Error\Error $e) {
        $log->error('Error al renderizar la plantilla', ['exception' => $e]);
        return 'Error al renderizar la plantilla: ' . $e->getMessage();
    }
}

/**
 * Require a view.
 *
 * @param  string $name
 * @param  array  $data
 */
function view($name, $data = [])
{
    global $log, $twig;

    try {
        // $log->debug('Datos en la vista', [$name, $data, $twig]);
        echo $twig->render("{$name}.html", $data);
    } catch (\Twig\Error\Error $e) {
        $log->error('Error al renderizar la plantilla', ['exception' => $e]);
        echo 'Error al renderizar la plantilla: ' . $e->getMessage();
    }

}

/**
 * Redirect to a new page.
 *
 * @param  string $path
 */
function redirect($path)
{
    header("Location: /{$path}");
}


function limpiarEntrada($textoConsulta, $esHTML = false)
{
    if (!$esHTML) {
        // Solo escapar caracteres especiales si el contenido no es HTML
        return htmlspecialchars($textoConsulta, ENT_QUOTES, 'UTF-8');
    }
    
    // Si es HTML, decodificar cualquier entidad HTML existente
    return html_entity_decode($textoConsulta, ENT_QUOTES, 'UTF-8');
}