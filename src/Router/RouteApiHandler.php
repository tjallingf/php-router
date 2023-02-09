<?php
    namespace Router;

    use Router\Exceptions\ResponseException;
    use Router\Route;
    use Router\Models\UrlPathTemplateModel;

    class RouteApiHandler {
        protected string|object $handler;
        protected array $options;
        protected string $pathTemplateBase;
        protected string $idParam;

        public function __construct(string $path_template_base, string|object $handler, array $options = []) {
            $this->pathTemplateBase = $path_template_base;
            $this->handler = $handler;
            $this->options = array_replace(static::DEFAULT_OPTIONS, $options);
            $this->idParam = $this->options['id_param_name'];
        }

        public function callHandler(string $method, array $args = []) {
            $callable = [ $this->handler, $this->options['methods'][$method]];
            return call_user_func_array($callable, $args);
        }

        public function registerRoutes() {
            $index_path_template = $this->pathTemplateBase;
            $item_path_template = $this->getNewPathTemplate('{'.$this->idParam.'}');
            
            if(is_string(@$this->options['methods']['index']))
                Route::get($index_path_template, [ $this, 'handleIndex' ], $this->options);

            if(is_string(@$this->options['methods']['find']))
                Route::get($item_path_template, [ $this, 'handleFind' ], $this->options);

            if(is_string(@$this->options['methods']['create']))
                Route::post($item_path_template, [ $this, 'handleCreate' ], $this->options);

            if(is_string(@$this->options['methods']['update']))
                Route::put($item_path_template, [ $this, 'handleUpdate' ], $this->options);

            if(is_string(@$this->options['methods']['edit']))
                Route::patch($item_path_template, [ $this, 'handleEdit' ], $this->options);
        }
        
        protected function getNewPathTemplate(string $append): string {
            return (new UrlPathTemplateModel($this->pathTemplateBase.'/'.$append))->__toString();
        }
                
        public function handleIndex($req, $res): void {
            $res->sendJson($this->callHandler('index'));
        }
                
        public function handleFind($req, $res): void {
            $value = $this->callHandler('find', [ $req->getParam($this->idParam) ]);
            if(!isset($value)) throw new ResponseException(null, 404);
            $res->sendJson($value);
        }
                
        public function handleUpdate($req, $res): void {
            $this->callHandler('update', [ $req->getParam($this->idParam), $req->getBody() ]);
            $res->sendJson($this->handleFind($req, $res));
        }
         
        public function handleEdit($req, $res): void {
            $this->callHandler('edit', [ $req->getParam($this->idParam), $req->getBody() ]);
            $res->sendJson($this->handleFind($req, $res));
        }

        public function handleCreate($req, $res): void {
            $this->callHandler('create', [ $req->getBody() ]);
            $res->sendJson($this->handleFind($req, $res));
        }

        protected const DEFAULT_OPTIONS = [
            'methods' => [
                'index'  => 'index',
                'find'   => 'find',
                'update' => 'update',
                'edit'   => 'edit',
                'create' => 'create'
            ],
            'id_param_name' => 'model_id'
        ];
    }