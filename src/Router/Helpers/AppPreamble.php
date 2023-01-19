<?php 
    namespace Router\Helpers;

    use Router\Lib;
    use Router\Helpers\Config;
    use PHPHtmlParser\Dom\Node\HtmlNode;
    use Router\Helpers\Dom;

    class AppPreamble {
        public static function toNode(string $preamble_code): HtmlNode {
            if(empty($preamble_code)) return new HtmlNode('root');
            return (new Dom($preamble_code))->root;
        }

        public static function getHeadCode(): string {
            switch(Config::get('development')) {
                case true:
                    return self::getOne('dev_vite_refresh_runtime');
                default:
                    return self::getOne('vite_plugin_legacy_head');
            }
        }

        public static function getBodyCode(): string {
            switch(Config::get('development')) {
                case true:
                    return self::getOne('dev_check_client_status');
                default:
                    return self::getOne('vite_plugin_legacy_body');
            }
        }

        protected static function getMany(array $names) {
            $code = '';

            foreach ($names as $name) {
                $code .= self::getOne($name);
            }

            return $code;
        }

        /**
         * Calculates the filepath for the given preamble.
         * Preambles are looked for in the preambles/dev directory
         * if 'development' is set to true in the config,
         * otherwise the preambles/prod directory is used.
         * @param string $name - The name of the preamble to inject.
         * @return string - The preamble code.
         */
        protected static function getOne(string $name): string {
            $filepath = Lib::joinPaths(
                Lib::getPackageDir(), 
                'assets/client/preambles',
                "$name.php");

            if(!file_exists($filepath)) 
                return '';

            ob_start();
            include($filepath);
            $content = ob_get_clean();

            return $content; 
        }
    }
?>