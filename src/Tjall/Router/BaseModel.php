<?php
    namespace Tjall\Router;

    use Tjall\Router\Storage;
    use Tjall\Router\RouteException;

    abstract class BaseModel {
        public string|int $id;
        protected \stdClass $data;

        public function __construct($id, array $data) {
            $this->id = $id;
            $this->setData($data);
        }

        public function toArray() {
            return (array) $this->data;
        }

        public function update(array $data = null) {
            if($data) $this->setData($data);
            return Storage::set([static::dir(), $this->id], $this->toArray());
        }

        public function delete() {
            $filepath = Storage::filepath([static::dir(), $this->id]);
            if(!file_exists($filepath)) return false;
            return unlink($filepath);
        }

        protected function setData(array $data) {
            if(!isset($this->data)) {
                $this->data = (object) $data;
            } else {
                $this->data = (object) array_replace((array) $this->data, $data);
            }
        }

        protected static function dir() {
            preg_match('/\\\\(\w+)Model$/', static::class, $matches);
            return strtolower($matches[1]).'s/';
        }

        static function first() {
            return @static::slice(0, 1)[0] ?? null;
        }

        static function slice(int $offset, int $length = null) {
            return array_slice(static::index(), $offset, $length);
        }

        static function index() {
            return Storage::scan(static::dir(), function($id) {
                return static::find($id);
            });
        }

        public static function create($id, array $data) {
            Storage::set([static::dir(), $id], []);
            $model = static::find($id);
            $model->update($data);
            return $model;
        }

        static function findFromRequest($req) {
            return static::find(urldecode($req->params['id']));
        }

        static function find($id) {
            $data = Storage::get([static::dir(), $id]);
            if($data === null) throw new RouteException("Model '{$id}' not found.", 404);
            return new static($id, $data);
        }
    }