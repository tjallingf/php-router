<?php 
    namespace Tjall\Router\Http;

    use Tjall\Router\Config;

    class Cookie {
        protected string $name;
        protected $value;
        protected ?int $expires;
        protected ?string $path;
        protected ?string $domain;
        protected ?bool $secure;
        protected ?bool $httpOnly;
        
        function __construct(
            string $name = null, 
            $value = '', 
            ?int $expires = null, 
            ?string $path = null, 
            ?string $domain = null, 
            ?bool $secure = false, 
            ?bool $http_only = false
        ) {
            $this->name = $name;
            $this->value = $value;
            $this->expires = $expires;
            $this->path = $path;
            $this->domain = $domain;
            $this->secure = $secure;
            $this->httpOnly = $http_only;
        }

        function setExpires(?int $expires = null): self {
            $this->expires = $expires;
            return $this;
        }

        function setPath(?string $path = null): self {
            $this->path = $path;
            return $this;
        }

        function setDomain(?string $domain = null): self {
            $this->domain = $domain;
            return $this;
        }

        function setSecure(?bool $secure = null): self {
            $this->secure = $secure;
            return $this;
        }

        function setHttpOnly(?bool $http_only = null): self {
            $this->httpOnly = $http_only;
            return $this;
        }

        function toArray(): array {
            return [ 
                $this->name, 
                $this->value,
                $this->expires ?? time() + static::EXPIRES_IN_DEFAULT,
                $this->path ?? Config::get('routes.basePath'),
                $this->domain,
                $this->secure,
                $this->httpOnly
            ];
        }

        protected const EXPIRES_IN_DEFAULT = 365*24*60*60 ;
    }