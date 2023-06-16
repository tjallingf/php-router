<?php
    namespace Tjall\Router;

    use Tjall\Router\Config;
    use Tjall\Router\Lib;
    use Jenssegers\Blade\Blade;

    class View {
        public string $name;
        public array $context;

        function __construct(string $name, array $context) {
            $this->name = $name;
            $this->context = $context;
        }
        
        static function get(string $name, ?array $context = []) {
            return new static($name, $context);
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