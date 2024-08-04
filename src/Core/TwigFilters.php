<?php 

try {
    $twig->addFilter(new \Twig\TwigFilter('format_estado', function ($estado) {
        // Reemplaza guiones bajos por espacios
        $estado = str_replace('_', ' ', $estado);

        // Lista de palabras que no se deben capitalizar
        $no_capitalize_words = ['de', 'ante', 'con', 'por', 'en', 'a', 'el', 'la', 'los', 'las', 'desde', 'hasta', 'para', 'entre', 'sobre'];

        // Capitaliza cada palabra importante
        $words = explode(' ', $estado);
        $formatted_words = [];

        foreach ($words as $index => $word) {
            $lowercase_word = strtolower($word);
            if ($index == 0 || $index == count($words) - 1 || !in_array($lowercase_word, $no_capitalize_words)) {
                $formatted_words[] = ucfirst($lowercase_word);
            } else {
                $formatted_words[] = $lowercase_word;
            }
        }

        return implode(' ', $formatted_words);
    }));

    // Agregar el filtro personalizado a Twig
    $twig->addFilter(new \Twig\TwigFilter('int', function ($value) {
        return (int) $value;
    }));

    $twig->addFilter(new \Twig\TwigFilter('format', function ($value, $format) {
        return sprintf($format, $value);
    }));

} catch (Exception $e) {
    $log->error('Error al agregar el filtro de Twig: ' . $e->getMessage());
    exit;
}

$twig->addFilter(new \Twig\TwigFilter('date_create', function ($dateString) {
    try {
        return new DateTime($dateString);
    } catch (Exception $e) {
        return null; // Maneja el error de creación de la fecha si es necesario
    }
}));
