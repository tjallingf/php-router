<?php 
    namespace Router;

    use Router\Config;
    use Router\Lib;

    final class Client {
        public static $includedStylesheets = [];
        public static $includedScripts = [];

        public static function include(): string {
            return 
                static::includeScript(static::findMainScript()).
                static::includeStylesheet(static::findMainStylesheet());
        }

        public static function includeScript(string $filename, array $attributes = []): string {
            $url = static::resolveUrl($filename);
            if(!isset($url)) return '';

            array_push(static::$includedScripts, $filename);

            return (
                static::createNodeString('script', array_merge(
                    $attributes,
                    [ 'src' => $url, 'type' => 'module' ]
                ))
            );
        }

        public static function includeStylesheet(string $filename, array $attributes = []): string {
            // In development mode, styles are handled by the JavaScript file.
            if(Config::get('client.developmentModeEnabled') && APP_MODE_DEV) return '';

            $url = static::resolveUrl($filename);
            if(!isset($url)) return '';

            array_push(static::$includedStylesheets, $filename);

            return static::createNodeString('link', array_merge(
                $attributes,
                [ 'href' => $url, 'rel' => 'stylesheet' ]
            ));
        }

        public static function findMainScript() {
            return basename(config::get('client.inputFile'));
        }

        public static function findMainStylesheet() {
            if(Config::get('client.developmentModeEnabled') && APP_MODE_DEV) {
                $filepath = @glob(APP_CLIENT_SRC_DIR.'/*.{css,scss,sass}', GLOB_BRACE)[0];
                return basename($filepath);
            }

            $input_file = Config::get('client.inputFile');
            return pathinfo($input_file, PATHINFO_FILENAME).'.css';
        }

        public static function resolveUrl(string $filename): string|null {
            $filepath = static::resolveFilepath($filename); 
            if(!isset($filepath)) return null;

            if(Config::get('client.developmentModeEnabled') && APP_MODE_DEV) {
                $domain = 'http://localhost:'.Config::get('client.port');
                $url = ltrim(substr($filepath, strlen(APP_CLIENT_SRC_DIR)), '/');
            
                return "$domain/$url";
            } else {
                $url = Lib::joinPaths(
                    Lib::getRelativeRootDir(), 
                    substr($filepath, strlen(Lib::joinPaths(Lib::getRootDir(), 'public'))));
                
                return $url;
            }
        }

        public static function resolveFilepath(string $filename) {
            if(Config::get('client.developmentModeEnabled') && APP_MODE_DEV)
                return Lib::joinPaths(APP_CLIENT_SRC_DIR, $filename);

            // Generate pattern for file names to match,
            // because Vite puts a hash in the filename
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $pattern = Lib::joinPaths(APP_CLIENT_OUT_DIR, "{$basename}.*.{$extension}");

            $prod_files = glob($pattern, GLOB_NOSORT);

            if(count($prod_files) == 0) 
                return null;

            // Sort dest files by creation date, newest first
            usort($prod_files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            // Pick the newest file
            $dist_filename = $prod_files[0];

            return $dist_filename;
        }
        
        protected static function createNodeString(string $node, array $attributes = [], string $content = ''): string {
            return "<$node ".Lib::htmlBuildAttributes($attributes).">$content</$node>";
        }
    }
?>