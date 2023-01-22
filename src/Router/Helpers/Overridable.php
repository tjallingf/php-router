<?php
    namespace Router\Helpers;

    use Router\Helpers\Overrides;

    abstract class Overridable {
        public static function getOverride(): static|string {
            return Overrides::get(static::class);
        }
    }