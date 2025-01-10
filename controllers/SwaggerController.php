<?php namespace Logingrupa\GenerateSwaggerAPI\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use View;
/**
 * Class SwaggerController
 * @package Logingrupa\GenerateSwaggerAPI\Controllers
 */
class SwaggerController extends Controller
{
    /**
     * Render Swagger UI.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function fnRenderSwaggerUi()
    {
        return View::make('logingrupa.generateswaggerapi::swagger-ui');
    }
}
