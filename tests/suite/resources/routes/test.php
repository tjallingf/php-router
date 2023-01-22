<?php
    use Router\Route;
    use Router\Response;
    use Router\Request;
    
    Route::post('/headers', function(Request $req, Response $res) {
        $res->sendHeader(' x-CounTer', intval($req->getHeaderLine('X-cOunter  ')) + 1);
    });

    Route::post('/cookies', function(Request $req, Response $res) {
        $res->sendCookie('counter', );
    });