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
        protected UrlPathTemplateModel $urlPathTemplate;
        protected $callback;
        protected array $middlewares = [];
        protected array $options;
        
        public function __construct(string $method, string $url_path_template, callable $callback, array $options) {
            $this->method = $method;
            $this->callback = $callback;

            $url_path_template = new UrlPathTemplateModel(trim(Config::get('router.baseUrl').'/'.$url_path_template, '/'));
            $this->urlPathTemplate = $url_path_template;
            $this->options = $options;
        }
        
        public function __toString() {
            return strtoupper($this->method).' '.$this->urlPathTemplate->__toString();
        }
        
        public function handle(Request $req, Response $res): void {
            // Allow middleware to modify the request before passing it to the handler.
            $this->callMiddlewares($req, $res, $this->options, Middleware::MAP_REQUEST);
            
            if(is_callable($this->callback))
                call_user_func($this->callback, $req, $res);

            // Allow middleware to modify the response before passing it to the handler.
            $this->callMiddlewares($req, $res, $this->options, Middleware::MAP_RESPONSE);
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

        protected function callMiddlewares(Request $req, Response $res, array $options, string $method) {
            foreach ($this->getAllMiddlewares() as $middleware) {               
                if($middleware instanceof MiddlewareObjectModel) {
                    $middleware->handle([ $req, $res, $options ], $method);
                } else if ($middleware instanceof MiddlewareCallableModel) {
                    if(!$middleware->matchesMethod($method)) continue;
                    $middleware->handle([ $req, $res, $options ]);
                }
            }
        }

        public function matchesMethod(string $method): bool {
            if($this->method == 'any') return true;
            return (strtolower($this->method) == strtolower($method));
        }

        public function matchesUrlPath(UrlPathModel $url_path): bool {
            return $url_path->matchesTemplate($this->urlPathTemplate);
        }
        
        public function getParams(UrlPathModel $url): array {
            $params = [];
            
            foreach($this->urlPathTemplate->getPartsMap() as $index => $part) {
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