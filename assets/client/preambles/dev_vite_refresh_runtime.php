<?php use Router\Helpers\Config; ?>
<script type="module">
    import RefreshRuntime from '<?= 'http://localhost:'.Config::get('client.port').'/@react-refresh'; ?>';
    RefreshRuntime.injectIntoGlobalHook(window);
    window.$RefreshReg$ = () => {};
    window.$RefreshSig$ = () => (type) => type;
    window.__vite_plugin_react_preamble_installed__ = true;
</script>