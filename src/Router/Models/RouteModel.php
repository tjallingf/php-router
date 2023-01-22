<?php
    namespace Router\Models;

    use Router\Models\UrlModel;
    use Router\Helpers\Config;

    class RouteModel {
        protected string $method = 'get';
        protected UrlTemplateModel $urlTemplate;
        protected $callback;

        public function __construct(string $method, UrlTemplateModel $url_template, callable $callback) {
            $this->method = $method;
            $this->urlTemplate = $url_template;
            $this->callback = $callback;
        }
        
        public function matchesMethod(string $method): bool {
            if($this->method == 'any') return true;
            return (strtolower($this->method) == strtolower($method));
        }

        public function matchesUrl(UrlModel $url): bool {
            return $url->matchesTemplate($this->getUrlTemplate());
        }

        public function resolveUrlParameters(UrlModel $url): array {
            $params = [];
            
            foreach($this->getUrlTemplate()->getPartsMap() as $index => $part) {
                if($part['type'] != 'parameter') continue;

                $value = $url->getValue($index);
                $params[$part['parameter_name']] = $value;
            }

            return $params;
        }

        public function getUrlTemplate(): UrlTemplateModel {
            return $this->urlTemplate;
        }

        public function handle(...$args) {
            try {
                call_user_func($this->callback, ...$args);
            } catch(\Exception $e) {
                if(APP_MODE_DEV)
                    throw $e;

<<<<<<< HEAD
        protected function getMiddlewareIndex(string $id, int $type): int|null {
            $ids = [];

            foreach ($this->middlewares as $middleware) {
                if($middleware->type != $type) continue;
                array_push($ids, $middleware->id);
            }

            return @array_flip($ids)[$id];
        }

        protected function getAllMiddlewares(): array {
            return array_merge((Overrides::get(Router::class))::$globalMiddlewares, $this->middlewares);
        }

        protected function callMiddlewares(string $method, Request &$req, Response &$res) {
            foreach ($this->getAllMiddlewares() as $middleware) {               
                $result = $middleware->handle($method, $req, $res);
=======
                return $e;
>>>>>>> 6d3a16e6d2c6fa1d91005236ee3a47a39de7d8a3
            }
        }
    }