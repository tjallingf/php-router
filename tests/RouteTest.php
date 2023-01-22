<?php
    use Router\Tests\Extensions\TestCase;
    use Router\Controllers\RouteController;
    use Router\Response;
    use Router\Tests\Data\Pictures;
    use Router\Models\RequestCookieModel;
    use Router\Middleware;

    final class RouteTest extends TestCase {
        private RouteController $routeController;
        private Response $res;

        public static function setUpBeforeClass(): void {
            self::$routeController = Middleware::routeController();
        }

        public function setUp(): void {
            // Reset Response
            $this->res = new Response();
        }

        public function testCanHandleGetRoute() {
            self::makeRequest('get', '/api/pictures');
            $expected = json_encode(Pictures::get());

            TestCase::assertEquals($expected, $this->res->getBodyAsString(), 
                'Response body does not match.');
        }

        public function testCanHandlePatchRouteWithRequiredUrlParam() {
            $id = Pictures::get()[0]['id'];
            $body = [ 'description' => 'New description!' ];

            self::makeRequest('patch', "/api/pictures/$id", [], $body);

            $expected = json_encode(array_replace(Pictures::getOne($id), $body));

            TestCase::assertEquals($expected, $this->res->getBodyAsString(), 
                'Response body does not match.');
        }

        public function testCanHandlePostRouteWithRequestHeaders() {
            $headers = [
                'X-Counter'  => 4
            ];

            self::makeRequest('post', '/headers', $headers);

            TestCase::assertEquals($headers['X-Counter']+1, $this->res->getHeaderLine('x-counter'),
                "Response header 'x-counter' is incorrect.");

            TestCase::assertEquals($headers['X-Counter']+1, $this->res->getHeaderLine('X-CouNtEr'),
                "Response header 'X-CouNtEr' is incorrect.");
        }

        public function testCanHandleGetRouteWithCookies() {
            self::makeRequest('get', '/cookies', [ 
                'Cookie' => implode('; ', [
                    new RequestCookieModel(['name' => 'counter', 'value' => 6]),
                    new RequestCookieModel(['name' => 'Counter', 'value' => 3]),
                ])
            ]);
        }

        public function testCanHandleComplexRoute() {
            self::makeRequest('get', '/api/pictures/', [
                'Authorization' => 'Bearer 287346'
            ]);
        }

        public function makeRequest(string $method, string $url, array $headers = [], $body = ''): void {
            $body_string = is_array($body) ? json_encode($body) : $body;
            self::$routeController->handleRequest($method, $url, $headers, $body_string);
        }
    }