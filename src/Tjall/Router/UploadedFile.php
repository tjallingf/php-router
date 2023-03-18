<?php 
    namespace Tjall\Router;

    use Exception;

    class UploadedFile {
        public string $name;
        public string $type;
        public string $tmpName;
        public int $size;

        function __construct(array $file) {
            $this->name = $file['name'];
            $this->type = $file['type'];
            $this->tmpName = $file['tmp_name'];
            $this->size = $file['size'];
        }

        function move(string $dest): void {           
            move_uploaded_file($this->tmpName, $dest);
        }

        function __toString(): string {
            return $this->tmpName;
        }
    }
?>