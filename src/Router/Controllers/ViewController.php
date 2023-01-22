<?php 
    namespace Router\Controllers;

    use Router\Controllers\ComponentController;
    use Router\Models\ViewModel;

    class ViewController extends ComponentController {
        protected static string $dir = '/resources/views';
        protected static string $model = ViewModel::class;
        
        const TYPE_NAME = 'view';
    }
?>