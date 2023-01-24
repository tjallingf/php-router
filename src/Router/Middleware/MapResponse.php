<?php
    namespace Router\Middleware;

    interface MapResponse {
        public function mapResponse($req, $res);
    }