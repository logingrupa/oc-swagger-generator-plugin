<?php

namespace Logingrupa\GenerateSwaggerAPI\Updates;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Seeder;

/**
 * Seeder to copy Swagger UI assets and generate Swagger documentation.
 */
class CopySwaggerUIAssets extends Seeder
{
    /**
     * Run the update process to copy Swagger UI assets and generate Swagger docs.
     *
     * @return void
     */
    public function run(): void
    {
        $sSourcePath = base_path('vendor/swagger-api/swagger-ui/dist');
        $sDestinationPath = base_path('plugins/logingrupa/generateswaggerapi/assets/swagger-ui');

        // Step 1: Copy Swagger UI assets
        if (File::isDirectory($sSourcePath)) {
            File::copyDirectory($sSourcePath, $sDestinationPath);
            echo "✅ Swagger UI assets copied successfully to: {$sDestinationPath}\n";
        } else {
            echo "❌ Source path does not exist: {$sSourcePath}\n";
        }

        // Step 2: Run the Swagger docs generation command
        echo "⚙️ Running: php artisan l5-swagger:generate\n";
        Artisan::call('l5-swagger:generate');
        echo "✅ Swagger documentation generated successfully.\n";
    }
}
