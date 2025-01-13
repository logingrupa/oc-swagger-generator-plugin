<?php namespace Logingrupa\GenerateSwaggerAPI;

use System\Classes\PluginBase;
use Illuminate\Support\Facades\App;
use L5Swagger\L5SwaggerServiceProvider;

/**
 * GenerateSwaggerAPI Plugin for integrating Swagger API documentation in OctoberCMS.
 *
 * @package Logingrupa\GenerateSwaggerAPI
 */
class Plugin extends PluginBase
{
    /**
     * Boot method, called during the plugin's initialization phase.
     *
     * This method registers the L5Swagger Service Provider and merges the custom configuration.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register L5Swagger Service Provider
        App::register(L5SwaggerServiceProvider::class);

        // Optionally override configuration values
        
        $this->setCustomSwaggerConfig();
    }

    /**
     * Merges the custom Swagger configuration.
     *
     * This method reads the custom configuration file and merges it with the existing configuration.
     *
     * @return void
     */
    private function setCustomSwaggerConfig(): void
    {
        // Load the custom configuration file
        $customConfig = require __DIR__ . '/config/l5-swagger.php';

        // Merge the custom configuration with the existing configuration
        config()->set('l5-swagger', array_replace_recursive(config('l5-swagger'), $customConfig));
    }
}
