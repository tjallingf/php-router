<?php 
    namespace Router\Controllers;

    use Router\Controllers\ComponentController;
    use Router\Models\ViewModel;

    class ViewController extends ComponentController {
        protected const DIR       = '/resources/views';
        protected const TYPE_NAME = 'view';
        protected const MODEL     = ViewModel::class;
        
        protected static array $data = [];
    }
?>