<?php 
    namespace Router\Controllers;

    use Router\Controllers\ComponentController;
    use Router\Models\ViewModel;

    class ViewController extends ComponentController {
        public static string $dir = '/resources/views';
        const TYPE = 'view';
        const MODEL = ViewModel::class;
    }
?>