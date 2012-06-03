<?php
namespace MDPI\AssetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    MDPI\AssetBundle\Extension\AssetsExtension,
    Symfony\Component\Finder\Finder;

/**
 * generate asset version number wth git log
 * Usage: app/console mdpi:assets:versions <BUNDLE NAME> 
 *
 * e.g. 
 *    app/console -e=prod mdpi:assets:versions MDPIMain 
 *
 */
class GenerateAssetsVersionsCommand extends ContainerAwareCommand 
{
    protected function configure()
    {
        $this->setName("mdpi:assets:versions")
            ->setDescription("generate version number (git log) for each asset file")
            ->addArgument("bundle", InputArgument::REQUIRED, "bundle name");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $bundleName = str_replace('bundle', '', strtolower($input->getArgument('bundle')));
        $assetsFile = $container->getParameter('kernel.cache_dir') . '/' . 
            AssetsExtension::ASSETS_FILE;

        $webBundleDir = realpath($container->getParameter('kernel.root_dir') . '/../web/bundles/' . $bundleName);
        if (!is_dir($webBundleDir)) {
            throw new \Exception('Bundle "' . $bundleName . '" does not exists.');
        }
        $webBundleDirSize = strlen($webBundleDir);

        $fp = fopen($assetsFile, 'w');
        flock($fp, LOCK_EX);
        fwrite($fp, "<?php\nreturn array(");
        foreach (Finder::create()->files()->name('*')->in($webBundleDir) as $file) {
            fwrite($fp, '"' . substr($file, $webBundleDirSize + 1) . '" => "' . substr(md5_file($file), 0, 16) . "\",\n");
        }
        fwrite($fp, ");");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

}
