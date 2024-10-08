<?php

namespace Paw\Core;

class Config
{
    private array $configs;

    public function __construct() 
    {
        $this->configs['LOG_LEVEL'] = getenv(("LOG_LEVEL"), "INFO");
        $path = getenv("LOG_PATH", '/logs/app.log');
        $this->configs['LOG_PATH'] = $this->joinPaths('..', $path);

        $this->configs['DB_ADAPTER'] = getenv('DB_ADAPTER') ?? 'mysql';
        $this->configs['DB_HOSTNAME'] = getenv('DB_HOSTNAME') ?? 'localhost';
        $this->configs['DB_DBNAME']  = getenv('DB_DBNAME') ?? 'database_name';     
        $this->configs['DB_USERNAME'] = getenv('DB_USERNAME') ?? 'root';
        $this->configs['DB_PASSWORD'] = getenv('DB_PASSWORD') ?? '';   
        $this->configs['DB_PORT'] = getenv('DB_PORT') ?? '3306';
        $this->configs['DB_CHARSET'] = getenv('DB_CHARSET') ?? 'utf8';      
        $this->configs['OPENCAGEDATA_API_KEY'] = getenv('OPENCAGEDATA_API_KEY') ?? '';

        $this->configs['TEMPLATE_DIR'] =  getenv('TEMPLATE_DIR') ?? '';
        $this->configs['TEMPLATE_CACHE_DIR'] =  getenv('TEMPLATE_CACHE_DIR') ?? '';
        $this->configs['MAIL_FROM']= getenv('MAIL_FROM');
        $this->configs['MAIL_NAME']= getenv('MAIL_NAME');
        $this->configs['MAIL_PASS']= getenv('MAIL_PASS');
        $this->configs['MAIL_HOST']= getenv('MAIL_HOST');
        $this->configs['COMPANY_MAIL']= getenv('COMPANY_MAIL');
    }

    public function joinPaths()
    {
        $paths = array();
        foreach (func_get_args() as $arg) {
            if($arg != ''){
                $paths[] = $arg;
            }
        }

        $result = preg_replace("#/+#", "/", join("/", $paths));
        return $result;
    }

    public function get($name)
    {
        return $this->configs[$name] ?? null;
    }
}