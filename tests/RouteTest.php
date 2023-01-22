<?php
    use Router\Tests\Extensions\TestCase;
    use Router\Router;
    use Router\Tests\Data\Pictures;
    use Router\Response;

    final class RouteTest extends TestCase {
        private Response $res;

        public function setUp(): void {
            $this->res = Response::getOverride();
        }

        public function testApiGet() {
            Router::handleRequest('get', '/api/pictures');
            $expected = json_encode(Pictures::get());

            TestCase::assertEquals($expected, $this->res->getBody(), 'Response body does not match.');
        }

        public function testApiGetOne() {
            $id = Pictures::get()[0]['id'];
            Router::handleRequest('get', "/api/pictures/$id");
            $expected = json_encode(Pictures::getOne($id));

            TestCase::assertEquals($expected, $this->res->getBody(), 'Response body does not match.');
        }

        public function testApiPatchOne() {
            $id = Pictures::get()[0]['id'];
            Router::handleRequest('patch', "/api/pictures/$id");
            $expected = json_encode(Pictures::getOne($id));

            TestCase::assertEquals($expected, $this->res->getBody(), 'Response body does not match.');
        }
    }