<?php 
    namespace Router\Helpers;

    use Router\Lib;
    use Router\Helpers\Config;
    use PHPHtmlParser\Dom\Node\HtmlNode;
    use Router\Helpers\Dom;
    use Router\Helpers\Client;

    class ClientPreamble {
        public static function toNode(string $preamble_code): HtmlNode {
            if(empty($preamble_code)) return new HtmlNode('root');
            return (new Dom($preamble_code))->root;
        }

        public static function getHeadCode(): string {
            switch(Config::get('client.developmentModeEnabled')) {
                case true:
                    if(count(Client::$includedScripts) || count(Client::$includedStylesheets))
                        return self::getOne('dev_vite_refresh_runtime');
                    break;
                default:
                    return self::getOne('vite_plugin_legacy_head');
            }

            return '';
        }

        public static function getBodyCode(): string {
            switch(Config::get('client.developmentModeEnabled')) {
                case true:
                    if(count(Client::$includedScripts) || count(Client::$includedStylesheets))
                        return self::getOne('dev_check_client_status');
                    break;
                default:
                    return self::getOne('vite_plugin_legacy_body');
            }

            return '';
        }

        protected static function getMany(array $names) {
            $code = '';

            foreach ($names as $name) {
                $code .= self::getOne($name);
            }

            return $code;
        }

        /**
         * Returns the file contents for a given preamble.
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