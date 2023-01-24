<?php
    // TODO: Make routeModel->useBefore() and RouteModel->useAfter()
    // also support global middlewares.

    namespace Router\Models;

    use Router\Request;
    use Router\Response;
    use Router\Middleware;
    use Router\Router;
    use Router\Config;
    use Router\Models\Model;
    use Router\Models\UrlPathModel;
    use Router\Models\MiddlewareModel;

    class RouteModel extends Model {
        protected string $method;
        protected UrlPathTemplateModel $urlTemplate;
        protected $callback;
        protected array $middlewares = [];
        
        public function __construct(string $method, UrlPathTemplateModel $url_template, callable $callback) {
            $this->method = $method;
            $this->urlTemplate = $url_template;
            $this->callback = $callback;
        }
        
        public function __toString() {
            return strtoupper($this->method).' '.$this->urlTemplate->__toString();
        }
        
        public function handle(Request $req, Response $res): void {
            // Allow middleware to modify the request before passing it to the handler.
            $this->callMiddlewares($req, $res, Middleware::MAP_REQUEST);
            
            if(is_callable($this->callback))
                call_user_func($this->callback, $req, $res);

            // Allow middleware to modify the response before passing it to the handler.
            $this->callMiddlewares($req, $res, Middleware::MAP_RESPONSE);
        }

        public function use(MiddlewareModel $middleware) {
            return $this->addMiddleware($middleware);
        }

        // public function useBefore(string $before_id, RouteMiddlewareModel $middleware) {
        //     return $this->addMiddleware($middleware, $before_id, -1);
        // }

        // public function useAfter(string $after_id, RouteMiddlewareModel $middleware) {
        //     return $this->addMiddleware($middleware, $after_id, 1);
        // }

        protected function addMiddleware(
            MiddlewareModel $middleware,
            ?string $rel_id = null, 
            ?int $rel_offset = 0
        ): self {
            // if(isset($rel_id)) {
            //     $rel_index = self::getMiddlewareIndex($rel_id, $middleware->type);
            //     if(!isset($rel_index))
            //         throw new \Exception("Cannot find middleware with id '$rel_id'.");
            // }

            // $insert_at_index = isset($rel_index) ? ($rel_index + $rel_offset) : null;

            // if($insert_at_index <= 0) {
            //     array_unshift($this->middlewares, $middleware);
            // } else if($insert_at_index < count($this->middlewares)) {
            //     array_splice($this->middlewares, $insert_at_index, 0, [ $middleware ]);
            // } else {
            //     array_push($this->middlewares, $middleware);
            // }

            array_push($this->middlewares, $middleware);

            return $this;
        }

        protected function callMiddlewares(Request $req, Response $res, string $method) {
            foreach ($this->getAllMiddlewares() as $middleware) {               
                if($middleware instanceof MiddlewareObjectModel) {
                    $middleware->handle([ $req, $res ], $method);
                } else if ($middleware instanceof MiddlewareCallableModel) {
                    if(!$middleware->matchesMethod($method)) continue;
                    $middleware->handle([ $req, $res ]);
                }
            }
        }

        public function matchesMethod(string $method): bool {
            if($this->method == 'any') return true;
            return (strtolower($this->method) == strtolower($method));
        }

        public function matchesUrl(UrlPathModel $url): bool {
            return $url->matchesTemplate($this->urlTemplate);
        }

        public function matchesRelativeUrl(string $url): bool {
            $url = new UrlPathModel(Config::get('router.baseUrl').$url);
            return $url->matchesTemplate($this->urlTemplate);
        }

        public function getParams(UrlPathModel $url): array {
            $params = [];
            
            foreach($this->urlTemplate->getPartsMap() as $index => $part) {
                if($part['type'] != 'parameter') continue;

                $value = $url->getValue($index);
                $params[$part['parameter_name']] = $value;
            }

            return $params;
        }

        protected function getMiddlewareIndex(string $id, int $type): int|null {
            $ids = [];

            foreach ($this->middlewares as $middleware) {
                if($middleware->type != $type) continue;
                array_push($ids, $middleware->id);
            }

            return @array_flip($ids)[$id];
        }

        protected function getAllMiddlewares(): array {
            return array_merge((Router::getOverride())::$globalMiddlewares, $this->middlewares);
        }
    }