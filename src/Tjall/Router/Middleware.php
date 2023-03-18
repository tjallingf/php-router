<?php
    namespace Tjall\Router;

    class Middleware {
        protected $handler;

        function __construct(callable $handler) {
            $this->handler = $handler;
        }

        function call(array $args) {
            return call_user_func_array($this->handler, $args);
        }
    }