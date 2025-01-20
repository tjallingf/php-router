<?php
    namespace Tjall\Router;

    use Tjall\Router\Storage;
    use Tjall\Router\RouteException;

    abstract class DatabaseModel {
        public string|int $id;
        protected \stdClass $data;
        protected static string $table;

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
            $this->data = (object) $data;
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
            Storage::set([static::dir(), $id], $data);
            return static::find($id);
        }

        static function findFromRequest($req) {
            $model = static::find(urldecode($req->params['id']));
            if(!$model) throw new RouteException('Not found.', 404);

            return $model;
        }

        static function find($id) {
            $stmt = Database::$mysqli->prepare("SELECT * FROM `".static::$table."` WHERE `id` = ?");
            $stmt->bind_param("s", $id);
            $result = $stmt->execute();
            var_dump($result);
            $data = null;
            if(!$data) return null;
            return new static($id, $data);
        }
    }