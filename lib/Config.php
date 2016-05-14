<?php
namespace Hrgruri\Icd3;

class Config
{
    const CONFIG_FILE_PATH = '../data/config.json';
    private static $instance;
    private $config;

    private function __construct()
    {
        $this->load();
    }

    private function load()
    {
        $config_file_path = __DIR__ . '/' . self::CONFIG_FILE_PATH;
        if (!file_exists($config_file_path)) {
            throw new \Exception('Configuration file does not exist');
        }
        $config = json_decode(file_get_contents($config_file_path));
        if (is_null($config)) {
            throw new \Exception('Configuration file format is broken.');
        }
        $this->config = $config;
    }
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key)
    {
        if (isset($this->config->{$key})){
            return $this->config->{$key};
        }
        throw new \Exception("Configuration {$key} does not exist");
    }
}
