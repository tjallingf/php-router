<?php
    namespace Router\Middleware;

    use Router\Request;
    use Router\Response;

    interface MapRequest {
        public function mapRequest(Request $req, Response $res);
    }