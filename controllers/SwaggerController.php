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

    /**
     * Return the Swagger JSON.
     *
     * @return JsonResponse
     */
    public function fnGetSwaggerJson()
    {
        $arrSwaggerData = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'OctoberCMS API',
                'version' => '1.0.0',
            ],
            'paths' => [
                '/api/example' => [
                    'get' => [
                        'summary' => 'Example endpoint',
                        'responses' => [
                            '200' => [
                                'description' => 'Successful response',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($arrSwaggerData);
    }
}
