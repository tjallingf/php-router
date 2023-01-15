<?php 
    namespace Router\Helpers;

    use Router\Helpers\Config;

    class Client {
        protected static bool $preambleInjected = false;

        public static function includeScript(string $filename, array $attributes = []): string {
            $url = self::resolveUrl($filename);

            if(Config::get('development'))
                return self::getPreambleCode().self::createNodeString('script', array_merge(
                    $attributes,
                    [ 'src' => $url, 'type' => 'module' ]
                ));
            
            return self::createNodeString('script', array_merge(
                $attributes,
                [ 'src' => $url ]
            ));
        }

        public static function includeStylesheet(string $filename, array $attributes = []): string {
            $url = self::resolveUrl($filename);

            // In development mode, styles are handled by the main.js file.
            if(Config::get('development'))
                return '';

            return self::createNodeString('link', array_merge(
                $attributes,
                [ 'href' => $url, 'rel' => 'stylesheet' ]
            ));
        }

        protected static function getPreambleCode(): string {
            if(self::$preambleInjected) return '';
            self::$preambleInjected = true;
            
            return ('
                <script type="module">
                    import RefreshRuntime from "http://localhost:'.Config::get('vite.port').'/@react-refresh";
                    RefreshRuntime.injectIntoGlobalHook(window);
                    window.$RefreshReg$ = () => {};
                    window.$RefreshSig$ = () => (type) => type;
                    window.__vite_plugin_react_preamble_installed__ = true;
                </script>
            ');
        }

        protected static function resolveUrl(string $filename): string {
            $filepath = self::resolveFilepath($filename); 

            if(Config::get('development')) {
                $domain = 'http://localhost:'.Config::get('vite.port');
                $url = ltrim(str_replace(APP_VITE_SRC_DIR, '', $filepath), '/');
            
                return "$domain/$url";
            } else {
                $url = join_paths(relative_root_dir(), str_replace(join_paths(root_dir(), 'public'), '', $filepath));
                
                return $url;
            }
        }

        protected static function resolveFilepath(string $filename): string {
            if(Config::get('development'))
                return join_paths(APP_VITE_SRC_DIR, $filename);

            // Generate pattern for file names to match,
            // because Vite puts a hash in the filename
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $pattern = join_paths(APP_VITE_OUT_DIR, "{$basename}.*.{$extension}");

            $dist_files = glob($pattern, GLOB_NOSORT);

            if(count($dist_files) == 0)
                throw new \Exception("No distribution files found for file '{$filename}'");

            // Sort dest files by creation date, newest first
            usort($dist_files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            // Pick the newest file
            $dist_filename = $dist_files[0];

            return $dist_filename;
        }
        
        protected function createNodeString(string $node, array $attributes = [], string $content = ''): string {
            $node_string = "<$node ";

            foreach ($attributes as $key => $value) {
                $node_string .= "$key=\"$value\" ";
            }
            
            $node_string = rtrim($node_string, ' ').">$content</$node/>";

            return $node_string;
        }
    }
?>