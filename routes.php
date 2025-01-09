<?php

use Logingrupa\GenerateSwaggerAPI\Controllers\SwaggerController;
use Logingrupa\GenerateSwaggerAPI\Controllers\SwaggerJsonController;

/*
|--------------------------------------------------------------------------
| Plugin Routes for Swagger Documentation
|--------------------------------------------------------------------------
|
| This file contains routes that serve the Swagger UI and provide the
| Swagger JSON documentation for your OctoberCMS application.
|
| All routes are prefixed with '/swagger' to organize and namespace
| the Swagger-related functionality.
|
*/

Route::group(['prefix' => 'swagger'], function () {

    // GET /swagger/ui
    // Route to render the Swagger UI page, which will load the interactive API documentation interface.
    Route::get('ui', [SwaggerController::class, 'fnRenderSwaggerUi'])
        ->name('swagger.ui');

    // GET /swagger/json
    // Route to provide the Swagger JSON data. This endpoint returns the API specification
    // in OpenAPI format (v3.0), which can be consumed by the Swagger UI or other tools.
    Route::get('json', [SwaggerJsonController::class, 'fnSwaggerJson'])
        ->name('swagger.json');
});
