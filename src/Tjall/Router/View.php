<?php
    namespace Tjall\Router;

    use Tjall\Router\Config;
    use Tjall\Router\Lib;
    use Tjall\Router\Context;
    use Exception;

    class View {
        public string $filepath;
        public array $context;

        function __construct(string $filepath, array $context) {
            $this->filepath = $filepath;
            $this->context = $context;
        }
        
        static function get(string $name, ?array $context = []) {
            $dir = Lib::joinPaths(Config::get('rootDir'), Config::get('views.dir'));
            $pattern = Lib::joinPaths($dir, $name.'.{'.join(',', static::FILE_EXTENSIONS).'}');

            $filepath = @realpath(glob($pattern, GLOB_BRACE)[0] ?? '');
            if(!$filepath || !is_file($filepath))
                throw new Exception("Cannot find view '$name' in directory '$dir'.");

            return new static($filepath, $context);
        }

        function render() {
            $file_extension = pathinfo($this->filepath, PATHINFO_EXTENSION);

            switch($file_extension) {
                case 'php':
                    Context::store($this->context);
                    $result = (function() {
                        ob_start();
                        include($this->filepath);
                        return ob_get_clean();
                    })();
                    Context::clear();

                    return $result;
                default:
                    return file_get_contents($this->filepath);
            }
        }

        const FILE_EXTENSIONS = [ 'html', 'php' ];
    }