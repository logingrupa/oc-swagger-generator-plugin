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
    Route::get('documentation', '\L5Swagger\Http\Controllers\SwaggerController@api');
});
