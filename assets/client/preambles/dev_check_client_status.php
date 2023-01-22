<?php 
    use Router\Config; 
    use Router\Lib;

    function get_start_command() {
        $node_modules_dir = Lib::joinPaths(Lib::getRootDir(), 'node_modules');
        return (is_dir($node_modules_dir) 
            ? Config::get('client.startCommand')
            : Config::get('client.startCommandWithInstall'));
    }
?>
<script>
    (async function() {
        const url = '<?= 'http://localhost:'.Config::get('client.port').'/@react-refresh'; ?>';
        fetch(url).catch(function(err) {
            if(!(err instanceof TypeError)) return;

            alert(
                "Development mode is enabled in config, but the development server is not running. " + 
                "Try starting it by running \'<?= get_start_command(); ?>\'."
            );
        });
    })();
</script>