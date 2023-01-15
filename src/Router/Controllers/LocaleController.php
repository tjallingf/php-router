<?php 
    namespace Router\Controllers;

    use Router\Controllers\Controller;

    class LocaleController extends Controller {
        protected static array $data = [];
        protected static string $selectedLocale;

        public static function populate() {
            $files = glob(join_paths(root_dir(), 'resources/locale/*.json'));
            $locales = [];

            foreach ($files as $file) {
                $locale = pathinfo($file, PATHINFO_FILENAME);
                $locales[$locale] = @json_decode(file_get_contents($file), true);
            }

            self::$data = $locales;
        }

        public static function select(string $locale) {
            if(!isset(self::index()[$locale]))
                throw new \Exception("Invalid locale: '$locale'.");

            self::$selectedLocale = $locale;
        }

        public static function find(string $key) {
            if(!isset(self::$selectedLocale))
                self::select(array_key_first(self::index()) ?? '');

            return @self::index()[self::$selectedLocale][$key];
        }
    }
?>