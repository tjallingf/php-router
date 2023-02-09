<?php
    namespace Router\Models;

    use Router\Models\MiddlewareModel;

    class MiddlewareObjectModel extends MiddlewareModel {
        protected string $id;
        protected $object;

        public function __construct(string $id, object $object) {
            $this->id     = $id;
            $this->object = $object;
        }

        public function handle(array $args, string $method): void {
            if($this->canHandle($method)) {
                call_user_func_array([ $this->object, $method ], $args);
            }
        }

        public function canHandle(string $method): bool {
            return (method_exists($this->object, $method));
        }

        protected const INTERFACES_NAMESPACE = 'Router\\Middleware';
    }