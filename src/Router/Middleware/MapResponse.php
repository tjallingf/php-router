<?php
    namespace Router\Middleware;

    use Router\Request;
    use Router\Response;

    interface MapResponse {
        public function mapResponse(Request $req, Response $res);
    }