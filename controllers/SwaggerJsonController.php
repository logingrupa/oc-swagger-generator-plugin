<?php namespace Logingrupa\GenerateSwaggerAPI\Controllers;

use Cms\Classes\Theme;
use Cms\Classes\Page;
use Backend\Classes\Controller;

use Illuminate\Support\Facades\Log;
use Response;

class SwaggerJsonController extends Controller
{

public function fnSwaggerJson()
    {
        // Initialize the main Swagger array
        $aSwagger = [
            "openapi" => "3.0.0",
            "info" => [
                "version" => "1.0.0",
                "title" => "User Calibration API",
                "description" => "API endpoint for managing user display calibration settings",
                "contact" => [
                    "name" => "Rolands Zeltins",
                    "email" => "hi@logingrupa.lv"
                ],
                "license" => [
                    "name" => "Apache 2.0",
                    "url" => "http://www.apache.org/licenses/LICENSE-2.0.html"
                ]
            ],
            "servers" => [
                [
                "url" => "http://eyedoo.test/api/v1",
                "description" => "Production server"
                ]
            ],
            "paths" => [],
            "components" => []
        ];

        // Get the active theme
        $oActiveTheme = Theme::getActiveTheme();

        // Get all CMS pages from the active theme
        $aCmsPages = Page::listInTheme($oActiveTheme);

        // Iterate through each page and process if it has is_api = "true"
        foreach ($aCmsPages as $oPage) {
            if (isset($oPage->settings['is_api']) && $oPage->settings['is_api'] == true) {
                
                // Extract the PHP section
                $aPhpSection = $oPage->getAttributes()['content'];

                // Evaluate the PHP section to get the $this['swagger'] variable
                $aVars = $this->evaluatePhpSection($aPhpSection);
                // Check if the 'swagger' variable exists; if not, skip this page
           
                // Merge the paths from the current page into the main Swagger array
                $aSwagger['paths'] = array_merge_recursive($aSwagger['paths'], $aVars['paths'] ?? []);
                $aSwagger['components'] = array_merge_recursive($aSwagger['components'], $aVars['components'] ?? []);
            }
        }

        // Return the merged Swagger JSON
        return Response::json($aSwagger);
    }




    /**
 * Extracts the PHP section and returns the JSON data.
 *
 * @param string $sContent
 * @return array|null
 */
private function evaluatePhpSection(string $sContent)
{
    
    // Step 1: Extract PHP section after the `==` separator
    if (preg_match('/==\s*(.*)$/s', $sContent, $arMatches)) {
        $sPhpSection = $arMatches[1];
        $arJsonData = $this->extractJsonData($sPhpSection);
        
        return $arJsonData;
    }
    
}

/**
 * Extracts the JSON object defined in a PHP string and returns it as an array.
 *
 * @param string $sContent
 * @return array|null
 */
private function extractJsonData(string $sContent)
{
    // Find the JSON block enclosed in backticks
    preg_match('/json_data\s*=\s*`(.*?)`;/s', $sContent, $matches);
    
    if (!isset($matches[1])) {
        return null; // JSON block not found
    }
    
    // Decode the JSON
    $sJsonString = $matches[1];
    $arJsonData = json_decode($sJsonString, true);
  

    if (json_last_error() === JSON_ERROR_NONE) {
        return $arJsonData;
    } else {
        throw new \Exception('Invalid JSON: ' . json_last_error_msg());
    }
}




}
