<?php
    // TODO: Make routeModel->useBefore() and RouteModel->useAfter()
    // also support global middlewares.

    namespace Router\Models;

    use Router\Request;
    use Router\Response;
    use Router\Lib;
    use Router\Middleware;
    use Router\Router;
    use Router\Message;
    use Router\Overrides;
    use Router\Models\UrlModel;

    class RouteModel {
        protected string $method;
        protected UrlTemplateModel $urlTemplate;
        protected $callback;
        protected array $middlewares = [];
        
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
            return $url->matchesTemplate($this->urlTemplate);
        }

        public function getParams(UrlModel $url): array {
            $params = [];
            
            foreach($this->urlTemplate->getPartsMap() as $index => $part) {
                if($part['type'] != 'parameter') continue;

                $value = $url->getValue($index);
                $params[$part['parameter_name']] = $value;
            }

            return $params;
        }

        public function use(RouteMiddlewareModel $middleware) {
            return $this->addMiddleware($middleware);
        }

        // public function useBefore(string $before_id, RouteMiddlewareModel $middleware) {
        //     return $this->addMiddleware($middleware, $before_id, -1);
        // }

        // public function useAfter(string $after_id, RouteMiddlewareModel $middleware) {
        //     return $this->addMiddleware($middleware, $after_id, 1);
        // }

        protected function addMiddleware(
            RouteMiddlewareModel $middleware,
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

        public function handle(Request &$req, Response &$res) {
            try {
                // Allow middleware to modify the request before passing it to the handler.
                $this->callMiddlewares(Middleware::MAP_REQUEST, $req, $res);
                
                call_user_func($this->callback, $req, $res);

                // Allow middleware to modify the response before passing it to the handler.
                $this->callMiddlewares(Middleware::MAP_RESPONSE, $req, $res);
            } catch(\Exception $e) {
                return Lib::throwIfDev($e, $e);
            }
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
            return array_merge((Overrides::get(Router::class))::$globalMiddlewares, $this->middlewares);
        }

        protected function callMiddlewares(string $method, Request &$req, Response &$res) {
            foreach ($this->getAllMiddlewares() as $middleware) {               
                $result = $middleware->handle($method, $req, $res);
            }
        }
    }