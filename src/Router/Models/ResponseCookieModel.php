<?php
    namespace Router\Models;

    use Router\Models\CookieModel;

    class ResponseCookieModel extends CookieModel {
        public function getExpires(): int|null {
            return $this->data['expires'];
        }

        public function setExpires(int $timestamp): self {
            $this->data['expires'] = $timestamp;
            return $this;
        }

        public function getDomain(): string|null {
            return $this->data['domain'];
        }

        public function setDomain(string $domain): self {
            $this->data['domain'] = $domain;
            return $this;
        }

        public function getPath(): string|null {
            return $this->data['path'];
        }
        
        public function setPath(string $path): self {
            $this->data['path'] = $path;
            return $this;
        }
    }