<?php
    namespace Tjall\Router;

    use Tjall\Router\BaseModel;
    use Tjall\Router\Config;
    use Tjall\Router\Lib;
    use Jenssegers\Blade\Blade;

    function array_map_recursive(callable $callback, array $array) {
        return array_map(function ($item) use ($callback) {
            return is_array($item) ? array_map_recursive($callback, $item) : $callback($item);
        }, $array);
    }

    class View {
        public string $name;
        public array $context;

        function __construct(string $name, array $context) {
            $this->name = $name;
            $this->context = $this->serializeModels($context);
        }
        
        static function get(string $name, ?array $context = []) {
            return new static($name, $context);
        }

        protected function serializeModels(array $context) {
            $context = array_map_recursive(function($value) {
                if($value instanceof BaseModel) {
                    $methods = get_class_methods($value);
                    $data = $value->toArray();
                    $data['id'] = $value->id;

                    // call get-methods to extend data
                    foreach($methods as $method) {
                        if(!str_starts_with($method, 'get')) continue;
                        $data[lcfirst(substr($method, 3))] = $value->$method();
                    }

                    return $data;
                }
                return $value;
            }, $context);
            return $context;
        }

        protected function getBladeRenderer() {
            $blade_dir = Lib::joinPaths(Config::get('rootDir'), Config::get('blade.dir'));
            $cache_dir = Lib::joinPaths(Config::get('rootDir'), 'cache/views');
            $blade = new Blade($blade_dir, $cache_dir);

            $blade->directive('translate', function ($expression, $variables = null) {
                return "<?php echo(App\Locale::format($expression, $variables)); ?>";
            });

            $blade->directive('vite', function () {
                $a = \Tjall\Router\Vite::include();
                return "<?php echo(\Tjall\Router\Vite::include()); ?>";
            });

            return $blade;
        }

        function render() {
            $blade = self::getBladeRenderer();
            return $blade->make('views.'.$this->name, $this->context)->render();
        }

        const FILE_EXTENSIONS = [ 'html', 'php' ];
    }