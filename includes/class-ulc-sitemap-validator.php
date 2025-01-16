<?php
namespace ULC_Sitemap\Core;

class Validator {
    private const SCHEMA_LOCATIONS = [
        'sitemap' => 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd',
        'sitemapindex' => 'http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd'
    ];

    public function validate_sitemap(string $type, string $content): array {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadXML($content);

        $schema_file = $type === 'index' ? 
            self::SCHEMA_LOCATIONS['sitemapindex'] : 
            self::SCHEMA_LOCATIONS['sitemap'];

        if (!$doc->schemaValidate($schema_file)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return $this->format_validation_errors($errors);
        }

        return [];
    }

    public function check_urls_health(array $urls): array {
        $results = [];
        
        foreach ($urls as $url) {
            $response = wp_remote_head($url);
            
            if (is_wp_error($response)) {
                $results[$url] = [
                    'status' => 'error',
                    'message' => $response->get_error_message()
                ];
                continue;
            }

            $status = wp_remote_retrieve_response_code($response);
            $results[$url] = [
                'status' => $status === 200 ? 'ok' : 'error',
                'code' => $status,
                'message' => wp_remote_retrieve_response_message($response)
            ];
        }

        return $results;
    }

    private function format_validation_errors(array $errors): array {
        $formatted = [];
        
        foreach ($errors as $error) {
            $formatted[] = [
                'line' => $error->line,
                'column' => $error->column,
                'message' => trim($error->message),
                'level' => $this->get_error_level($error->level)
            ];
        }

        return $formatted;
    }

    private function get_error_level(int $level): string {
        return match ($level) {
            LIBXML_ERR_WARNING => 'warning',
            LIBXML_ERR_ERROR => 'error',
            LIBXML_ERR_FATAL => 'fatal',
            default => 'unknown'
        };
    }
}