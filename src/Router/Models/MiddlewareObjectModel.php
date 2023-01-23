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
            // Get the implements of the object
            $implements = array_change_key_case(class_implements($this->object, CASE_LOWER));
            $must_implement = static::INTERFACES_NAMESPACE.'\\'.ucfirst($method);

            if(!interface_exists($must_implement))
                throw new \Exception("Interface '$must_implement' does not exist");


            if(!method_exists($this->object, $method))
                return false;

            if(!array_key_exists(strtolower($must_implement), $implements)) {
                trigger_error("Class '".$this->object::class."' has method '".$method."' but does not implement '".$must_implement."'", E_USER_WARNING);
                return false;
            }

            return true;
        }

        protected const INTERFACES_NAMESPACE = 'Router\\Middleware';
    }