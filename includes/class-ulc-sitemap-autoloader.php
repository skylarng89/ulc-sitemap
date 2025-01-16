<?php
namespace ULC_Sitemap\Core;

class Autoloader {
    public function __construct() {
        spl_autoload_register([$this, 'autoload']);
    }

    private function autoload(string $class): void {
        // Check if the class uses our namespace
        if (str_starts_with($class, 'ULC_Sitemap\\')) {
            // Convert namespace to path
            $file = str_replace('ULC_Sitemap\\', '', $class);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $file = strtolower(
                preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $file)
            );
            
            $path = ULC_SITEMAP_PLUGIN_DIR . 'includes/class-' . $file . '.php';
            
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
}

new Autoloader();