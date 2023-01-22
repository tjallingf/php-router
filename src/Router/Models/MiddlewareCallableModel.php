<?php
    namespace Router\Models;

    use Router\Models\MiddlewareModel;

    class MiddlewareCallableModel extends MiddlewareModel {
        protected string $id;
        protected $callback;
        protected string $asMethod;

        public function __construct(string $id, callable $callback, string $as_method) {
            $this->id       = $id;
            $this->callback = $callback;
            $this->asMethod = $as_method;
        }

        public function handle(array $args): void {
            call_user_func_array($this->callback, $args);
        }

        public function matchesMethod(string $method): bool {
            return ($this->asMethod === $method);
        }
    }