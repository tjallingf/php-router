<?php 
    namespace Router\Controllers;

    use Router\Controllers\ComponentController;
    use Router\Models\ViewModel;

    class ViewController extends ComponentController {
        const TYPE = 'view';
        const DIR = 'views';
        const MODEL = ViewModel::class;
    }
?>