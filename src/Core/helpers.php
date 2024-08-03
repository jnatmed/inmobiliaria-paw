<?php

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
