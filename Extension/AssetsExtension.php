<?php
namespace MDPI\AssetBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AssetsExtension extends \Twig_Extension 
{
    const ASSETS_FILE = 'assets.php';

    private $container;
    private $assets = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $assetsFile = $container->getParameter("kernel.cache_dir") . '/' . self::ASSETS_FILE;
        if (file_exists($assetsFile)) {
            $this->assets = include($assetsFile);
        }
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'asset' => new \Twig_Function_Method($this, 'getAssetUrl'),
            'assets_version' => new \Twig_Function_Method($this, 'getAssetsVersion'),
        );
    }

    /**
     * Returns the public path of an asset.
     *
     * Absolute paths (i.e. http://...) are returned unmodified.
     *
     * @param string $path        A public path
     * @param string $packageName The name of the asset package to use
     *
     * @return string A public path which takes into account the base path and URL path
     */
    public function getAssetUrl($path, $packageName = null)
    {
        $assets = $this->assets;
        $url = $this->container->get('templating.helper.assets')->getUrl($path, $packageName);
        if (isset($assets[$path])) {
            $url = str_replace($path, $path . '?' . $assets[$path], $url);
        }
        return $url;
    }

    /**
     * Returns the version of the assets in a package.
     *
     * @param string $packageName
     *
     * @return int
     */
    public function getAssetsVersion($packageName = null)
    {
        return $this->container->get('templating.helper.assets')->getVersion($packageName);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'assets';
    }
}
