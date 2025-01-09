<?php namespace Logingrupa\GenerateSwaggerAPI\Controllers;

use Cms\Classes\Theme;
use Cms\Classes\Page;
use Backend\Classes\Controller;
use Response;
use Illuminate\Support\Facades\Log;

class SwaggerJsonController extends Controller
{
    /**
     * Generates Swagger JSON documentation based on CMS pages.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fnSwaggerJson()
    {
        $aSwagger = [
            "openapi" => "3.0.0",
            "info" => [
                "title" => "EYEDOO Training API",
                "version" => "v1",
                "description" => "API Documentation generated with PHPDocs"
            ],
            "paths" => []
        ];

        // Get the active theme
        $oActiveTheme = Theme::getActiveTheme();

        // Get all CMS pages from the active theme
        $aCmsPages = Page::listInTheme($oActiveTheme);

        // Process each CMS page to find API-related pages
        foreach ($aCmsPages as $oPage) {
            if (isset($oPage->settings['is_api']) && $oPage->settings['is_api'] == true) {
                $this->processPage($oPage, $aSwagger);
            }
        }

        // Return the Swagger JSON
        return Response::json($aSwagger);
    }

    /**
     * Process a CMS page and extract PHPDocs to add to Swagger JSON.
     *
     * @param \Cms\Classes\Page $oPage CMS page object.
     * @param array &$aSwagger Swagger JSON array to update.
     */
    private function processPage($oPage, &$aSwagger)
    {
        // Get the file path of the CMS page
        $sFilePath = $oPage->getFilePath();

        // Read the content of the file
        $sContent = file_get_contents($sFilePath);

        // Match the PHPDocs in the file
        preg_match_all('/\/\*\*(.*?)\*\//s', $sContent, $aMatches);

        foreach ($aMatches[1] as $sPhpDoc) {
            $aParsed = $this->parsePhpDoc($sPhpDoc);

            if (!isset($aParsed['route']) || !isset($aParsed['method'])) {
                continue;
            }

            $sRoute = $aParsed['route'];
            $sMethod = strtolower($aParsed['method']);

            if (!isset($aSwagger['paths'][$sRoute])) {
                $aSwagger['paths'][$sRoute] = [];
            }

            $aSwagger['paths'][$sRoute][$sMethod] = [
                'tags' => $aParsed['tags'] ?? [],
                'summary' => $aParsed['summary'] ?? '',
                'description' => $aParsed['description'] ?? '',
                'parameters' => $aParsed['parameters'] ?? [],
                'requestBody' => $aParsed['requestBody'] ?? [],
                'responses' => $aParsed['responses'] ?? []
            ];
        }
    }

    /**
     * Parse PHPDoc comments to extract relevant annotations.
     *
     * @param string $sPhpDoc Doc comment string.
     * @return array Parsed PHPDoc data.
     */
    private function parsePhpDoc($sPhpDoc)
    {
        $aLines = explode("\n", $sPhpDoc);
        $aParsed = [];

        foreach ($aLines as $sLine) {
            $sLine = trim($sLine, "/* ");

            if (str_starts_with($sLine, '@route')) {
                $aParsed['route'] = trim(str_replace('@route', '', $sLine));
            } elseif (str_starts_with($sLine, '@method')) {
                $aParsed['method'] = trim(str_replace('@method', '', $sLine));
            } elseif (str_starts_with($sLine, '@tags')) {
                $aParsed['tags'][] = trim(str_replace('@tags', '', $sLine));
            } elseif (str_starts_with($sLine, '@summary')) {
                $aParsed['summary'] = trim(str_replace('@summary', '', $sLine));
            } elseif (str_starts_with($sLine, '@description')) {
                $aParsed['description'] = trim(str_replace('@description', '', $sLine));
            } elseif (str_starts_with($sLine, '@param')) {
                $aParsed['parameters'][] = $this->parseParamLine($sLine);
            } elseif (str_starts_with($sLine, '@requestBody')) {
                $aParsed['requestBody'] = $this->parseRequestBody($sPhpDoc);
            } elseif (str_starts_with($sLine, '@response')) {
                $this->parseResponseBlock($sPhpDoc, $aParsed['responses']);
            }
        }

        return $aParsed;
    }

private function parseParamLine($sLine)
{
    $aParts = preg_split('/\s+/', trim(str_replace('@param', '', $sLine)), 4);

    $sParamName = ltrim($aParts[1], '$');
    $sInLocation = 'query'; // Default to 'query'
    if (preg_match('/\[in:(path|query)\]/', $sLine, $aMatches)) {
        $sInLocation = $aMatches[1];
    }

    // Remove [in:path] or [in:query] from the description
    $sDescription = preg_replace('/\[in:(path|query)\]\s*/', '', $aParts[3] ?? '');

    // Remove 'Values: [...]' and 'Default: ...' from the description
    $sDescription = preg_replace('/Values:\s*\[.*?\]\s*/', '', $sDescription);
    $sDescription = preg_replace('/Default:\s*\S+/', '', $sDescription);

    // Detect enum values in the description (e.g., "Values: [value1, value2]")
    preg_match('/Values:\s*\[(.*?)\]/', $aParts[3] ?? '', $enumMatches);
    $aEnumValues = isset($enumMatches[1]) ? array_map('trim', explode(',', $enumMatches[1])) : [];

    // Determine default value from PHPDoc
    $sDefault = $this->getDefaultValue($sLine);

    // Only add 'schema' if 'enum' or 'default' is provided
    $aSchema = [];
    if (!empty($aEnumValues) || $sDefault !== null) {
        $aSchema = [
            'type' => $aParts[0] === 'int' ? 'integer' : 'string',
            'enum' => $aEnumValues ?: null,
            'default' => $sDefault
        ];
        // Remove null values from schema
        $aSchema = array_filter($aSchema, fn($value) => $value !== null);
    }

    return array_filter([
        'name' => $sParamName,
        'in' => $sInLocation,
        'description' => trim($sDescription),
        'required' => str_contains($sLine, '(required)'),
        'schema' => !empty($aSchema) ? $aSchema : null
    ]);
}





private function getDefaultValue($sLine)
{
    if (preg_match('/Default:\s*(\S+)/', $sLine, $aMatches)) {
        return $aMatches[1];
    }
    return null;
}





/**
 * Parse a @requestBody block into a requestBody object.
 *
 * @param string $sPhpDoc Full PHPDoc block.
 * @return array|null Parsed requestBody object or null if not found.
 */
private function parseRequestBody($sPhpDoc)
{
    // Match the JSON block between ---JSON--- and ---JSONEND---
    preg_match('/@requestBody.*?---JSON---\s*(.*?)\s*---JSONEND---/s', $sPhpDoc, $aMatches);
    if (!empty($aMatches[1])) {
        // Remove leading asterisks and whitespace from multiline JSON
        $requestBodyContent = preg_replace('/^\s*\*\s?/m', '', trim($aMatches[1]));
        
        // Decode the JSON example
        $example = json_decode($requestBodyContent, true);
        
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'example' => $example
                    ]
                ]
            ];
        } else {
            // Log an error if JSON decoding fails
            Log::error("parseRequestBody Invalid JSON in @requestBody: " . json_last_error_msg());
            Log::error("parseRequestBody Raw content: {$requestBodyContent}");
        }
    }

    return null;
}



/**
 * Parse the entire @response block into a responses array.
 *
 * @param string $sPhpDoc Full PHPDoc block.
 * @param array &$aResponses The responses array to update.
 */
private function parseResponseBlock($sPhpDoc, &$aResponses)
{
    // Match @response blocks with JSON examples between ---JSON--- and ---JSONEND--- delimiters
    preg_match_all('/@response\s+(\d{3})\s+.*?---JSON---\s*(.*?)\s*---JSONEND---/s', $sPhpDoc, $aMatches);

    Log::debug('parseResponseBlock Matched Responses:', $aMatches);

    foreach ($aMatches[1] as $index => $statusCode) {
        $responseContent = trim($aMatches[2][$index]);

        Log::debug("parseResponseBlock Processing @response {$statusCode} with content: ", [$responseContent]);

        // Remove leading asterisks and whitespace from multiline JSON
        $responseContent = preg_replace('/^\s*\*\s?/m', '', $responseContent);

        // Decode the JSON example
        $example = json_decode($responseContent, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // If valid JSON, use it directly
            $aResponses[$statusCode] = [
                'description' => "Response for status code {$statusCode}",
                'content' => [
                    'application/json' => [
                        'example' => $example
                    ]
                ]
            ];
            Log::debug("parseResponseBlock Decoded JSON for @response {$statusCode}: ", [$example]);
        } else {
            // Log the error if JSON is invalid
            Log::error("parseResponseBlock Invalid JSON for @response {$statusCode}: " . json_last_error_msg());
            Log::error("parseResponseBlock Raw content: {$responseContent}");
        }
    }
}






}
