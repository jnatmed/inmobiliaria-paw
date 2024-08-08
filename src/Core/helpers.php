<?php

/**
 * Require a view.
 *
 * @param  string $name
 * @param  array  $data
 */
function view($name, $data = [], $returnView = false)
{
    global $log, $twig;

    try {
        /**
         * si returnView esta en verdadero 
         * se devuelve la vista en vez de mostrarla
         * Esta funcionalidad permite usar la funcion view 
         * para cuando se envian los correos
         */
        if ( $returnView ) {
            return $twig->render("{$name}.html", $data);
        }else{
            // $log->debug('Datos en la vista', [$name, $data, $twig]);
            echo $twig->render("{$name}.html", $data);
        }
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


/**
 * Filtra un menú eliminando elementos cuyo 'href' esté presente en un array de exclusión.
 *
 * @param  array $menu       El array de elementos del menú a filtrar.
 * @param  array $array_list El array que contiene los 'href' de los elementos que se deben excluir.
 * @return array             El menú filtrado con los elementos excluidos.
 */
function sacarDelMenu($menu, $array_list)
{
    return array_filter($menu, function ($item) use ($array_list) {
        return !in_array($item['href'], $array_list);
    });
}
