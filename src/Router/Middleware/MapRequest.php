<?php
    namespace Router\Middleware;

    interface MapRequest {
        public function mapRequest($req, $res);
    }