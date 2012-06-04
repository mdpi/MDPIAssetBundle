MDPIAssetBundle
===================

In order to get rid of the global assets version in Symfony2.
The bundle provided a command "mdpi:assets:versions" to generate a list of assets with its unique version, which then can be used by the twig helper function "asset()"

Install
-------

1. add the bundle into your deps:

[MDPIAssetBundle]
    git=git@git://github.com/mdpi/MDPIAssetBundle.git
    version=origin/master
    target=/bundles/MDPI/AssetBundle

2. run "bin/vendors install"

3. update your app/autoload.php
```php
<?php
// ...
$loader->registerNamespaces(array(
    'Symfony'          => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
    // ...
    'MDPI'             => __DIR__.'/../vendor/bundles',
));
// ...
?>
```

4. update your app/AppKernel.php
```php
<?php
// ...
public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),
            // ...
            new MDPI\AssetBundle\MDPIAssetBundle(),
        );

// ...
```

5. remove the global assets version from your config file if it's used.
