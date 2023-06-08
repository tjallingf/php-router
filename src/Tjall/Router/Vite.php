<?php 
    namespace Tjall\Router;

    use Tjall\Router\Config;
    use Exception;

    class Vite {
        static function include() {
            if(Config::get('mode') === 'dev' && Config::get('vite.mode') === 'dev') {
                return static::includeDev();
            } else {
                return static::includeProd();
            }
        }

        protected static function includeDev() {
            $input = basename(Config::get('vite.input'));
            $port = Config::get('vite.devPort');
            $host = "http://localhost:$port";

            $html_snippet = <<<HTML
                <script type="module">
                    import RefreshRuntime from "$host/@react-refresh"
                    RefreshRuntime.injectIntoGlobalHook(window)
                    window.__vite_plugin_react_preamble_installed__ = true
                </script>
                <script type="module" src="$host/$input"></script>
            HTML;

            return $html_snippet;
        }

        protected static function includeProd() {
            $out_dir = Lib::joinPaths(Config::get('rootDir'), Config::get('vite.outDir'));
            $input = basename(Config::get('vite.input'));
            $input_name = pathinfo($input, PATHINFO_FILENAME);

            $manifest_file = Lib::joinPaths($out_dir, 'manifest.json');
            if(!is_file($manifest_file))
                throw new Exception("Build manifest not found in directory '$out_dir'.");
            
            $manifest = json_decode(file_get_contents($manifest_file), true);
            $entries = static::parseBuildManifest($manifest, $out_dir);

            if(!isset($entries['js'][$input_name]))
                throw new Exception("Build manifest does not contain entry '$input'.");

            $html_snippet = <<<HTML
                <script type="module" crossorigin src="{$entries['js'][$input_name]}"></script>
                <script type="module">try{import.meta.url;import("_").catch(()=>1);}catch(e){}window.__vite_is_modern_browser=true;</script>
                <script type="module">!function(){if(window.__vite_is_modern_browser)return;console.warn("vite: loading legacy build because dynamic import or import.meta.url is unsupported, syntax error above should be ignored");var e=document.getElementById("vite-legacy-polyfill"),n=document.createElement("script");n.src=e.src,n.onload=function(){System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))},document.body.appendChild(n)}();</script>
                <script nomodule>!function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",(function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()}),!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();</script>
                <script nomodule crossorigin id="vite-legacy-polyfill" src="{$entries['js']['polyfills-legacy']}"></script>
                <script nomodule crossorigin id="vite-legacy-entry" data-src="{$entries['js'][$input_name.'-legacy']}">System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))</script>
            HTML;

            foreach ($entries['css'] as $url) {
                $html_snippet .= <<<HTML
                    <link rel="stylesheet" href="{$url}">
                HTML;
            }
            
            return $html_snippet;
        }

        protected static function getFileUrl(string $file, string $out_dir): string {
            $relative_to = Lib::joinPaths(Config::get('rootDir'), 'public');
            $path = Lib::joinPaths($out_dir, $file);
            $relative_out_dir = Lib::relativePath($relative_to, $path);
            // $url = Lib::formatUrlPath(Lib::getProjectDir().'/'.$relative_out_dir); 
            $url = Lib::formatUrlPath(Config::get('routes.basePath').'/'.$relative_out_dir);

            return $url;
        }
        
        protected static function createNode(string $tag, array $attributes = [], string $content = '') {
            $node = "<$tag ";

            foreach ($attributes as $key => $value) {
                $node .= $key.'="'.htmlspecialchars($value).'" ';
            }

            $node = rtrim($node, ' ').'>'.$content."</$tag>";

            return $node;
        }

        protected static function parseBuildManifest(array $manifest, string $out_dir): array {
            $entries = ['js' => [], 'css' => []];

            foreach ($manifest as $field => $data) {
                // Skip if file is not an entry
                if(@$data['isEntry'] !== true) continue;

                // Get the url to the entry
                $url = static::getFileUrl($data['file'], $out_dir);

                // Get the rel of the entry ('polyfills-legacy', 'main', 'main-legacy')
                $rel = strtok(basename($data['file']), '.');

                // Store the entry
                $entries['js'][$rel] = $url;

                // Store CSS entries
                if(@is_array($data['css'])) {
                    foreach ($data['css'] as $file) {
                        $url = static::getFileUrl($file, $out_dir);
                        array_push($entries['css'], $url);
                    }
                }
            }

            return $entries;
        }
    }
?>