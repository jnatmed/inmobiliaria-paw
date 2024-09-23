<?php

namespace Paw\Core;

class Request 
{
    private $segments = [];

    public function uri($sacarBarras = false) 
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!$sacarBarras) {
            return $uri;
        } else {
            // Eliminar la barra inicial
            return ltrim($uri, '/');
        }
    }

    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function get($key)
    {
        return $_POST[$key] ?? $_GET[$key] ?? null;
    }

    public function all()
    {
        return $_POST;
    }
    public function getSegments($numeroSegmento)
    {
        // Obtener la URL sin los parámetros de consulta
        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
        // Dividir la URL en segmentos
        $this->segments = explode('/', trim($urlPath, '/'));
    
        // Devolver el segmento solicitado
        return isset($this->segments[$numeroSegmento]) ? $this->segments[$numeroSegmento] : null;
    }
        
    public function route()
    {
        return [
            $this->uri(),
            $this->method()
        ];
    }

    public function fullUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];

        return $protocol . $host . $uri;
    }

    public function referer()
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }    
    public function isUrlSafe($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $localHost = parse_url($this->fullUrl(), PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        // Verifica que la URL pertenezca al mismo dominio y no contenga patrones maliciosos
        return $host === $localHost && !preg_match('/[\r\n]/', $url) && strpos($path, '/publicacion/ver') === 0;
    }
        
    public function host()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
    
        return $protocol . $host;
    }    
}