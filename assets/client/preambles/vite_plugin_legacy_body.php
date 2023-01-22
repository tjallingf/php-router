<?php 
    use Router\Client;
    use Router\Config;

    $input_file_filename = pathinfo(Config::get('client.inputFile'), PATHINFO_FILENAME); 

    $legacy_polyfills_url = Client::resolveUrl('polyfills-legacy.js');
    $legacy_entry_url = Client::resolveUrl($input_file_filename.'-legacy.js');
?>
<script nomodule>!function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",(function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()}),!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();</script>

<?php if(isset($legacy_polyfills_url)) : ?>
<script nomodule crossorigin id="vite-legacy-polyfill" src="<?= $legacy_polyfills_url; ?>"></script>
<?php endif; ?>

<?php if(isset($legacy_entry_url)) : ?>
<script nomodule crossorigin id="vite-legacy-entry" data-src="<?= $legacy_entry_url; ?>">System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))</script>
<?php endif; ?>