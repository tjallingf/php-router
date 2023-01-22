<?php
    namespace Router\Interfaces;

    use Router\Request;
    use Router\Response;

    interface MiddlewareInterface {
        public function mapRequest(Request $req, Response $res);

        public function mapResponse(Request $req, Response $res);
    }