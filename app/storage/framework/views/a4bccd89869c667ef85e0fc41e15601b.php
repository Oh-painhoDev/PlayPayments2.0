<!-- PWA/SEO Tags e Manifest -->
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <meta name="theme-color" content="#161616">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo e(config('app.name', 'PlayPayments')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <link rel="apple-touch-startup-image" href="<?php echo e(asset('images/logo.png')); ?>">
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('PWA ServiceWorker registered successfuly with scope:', registration.scope);
                }, function(err) {
                    console.log('PWA ServiceWorker registration failed:', err);
                });
            });
        }
    </script>
<?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/components/pwa-head.blade.php ENDPATH**/ ?>