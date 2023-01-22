<?php
    use Router\Tests\Extensions\TestCase;
    use Router\Controllers\ConfigController;
    use Router\Config;

    final class ConfigTest extends TestCase {
        public function testTopLevelFieldFromController() {
            TestCase::assertEquals('Test Suite', ConfigController::find('name'), "Config controller returns incorrect value for top-level path.");
        }

        public function testTopLevelFieldFromHelper() {
            TestCase::assertEquals('Test Suite', Config::get('name'), "Config helper returns incorrect value for top-level path.");
        }

        public function testMultiLevelFieldFromController() {
            TestCase::assertEquals('client', ConfigController::find('client.rootDir'), "Config controller returns incorrect value for multi-level path.");
        }

        public function testMultiLevelFieldFromHelper() {
            TestCase::assertEquals('client', Config::get('client.rootDir'), "Config helper returns incorrect value for multi-level path.");
        }
    }