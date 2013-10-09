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
 * Usage: app/console mdpi:assets:versions [BUNDLE NAME]
 *
 * e.g. 
 *    # generate all of assets from all of bundles
 *    app/console -e=prod mdpi:assets:versions
 *
 *    # generate assets of MDPIMainBundle
 *    app/console -e=prod mdpi:assets:versions MDPIMain
 *
 *    # generate assets of MDPIMainBundle, and remove "mdpimain" from the assets path
 *    app/console -e=prod mdpi:assets:versions MDPIMain --trim-bundlename
 */
class GenerateAssetsVersionsCommand extends ContainerAwareCommand 
{
    protected function configure()
    {
        $this->setName("mdpi:assets:versions")->
            setDescription("generate version number for each asset file, it's based on the file's content")->
            addArgument("bundle", InputArgument::OPTIONAL, "bundle name, generate only for the specified bundle if set")->
            addOption(
                'trim-bundlename', 
                't', 
                InputOption::VALUE_NONE, 
                'If set, bundle names will be removed from the generated assets path'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $bundleName = str_replace('bundle', '', strtolower($input->getArgument('bundle')));
        $assetsFile = $container->getParameter('kernel.cache_dir') . '/' . 
            AssetsExtension::ASSETS_FILE;

        $bundlesDir = realpath(
            $container->getParameter('kernel.root_dir') . 
            '/../web/bundles'
        );
        $trimBundlename = $input->getOption('trim-bundlename');

        if ($bundleName) {
            $webBundleDirs = array($bundlesDir . '/' . $bundleName);
            if (!is_dir($webBundleDirs[0])) {
                throw new \Exception('Hmmmm, it seems that the bundle "' . 
                    $bundleName . '" does not exists.');
            }
        } else {
            $webBundleDirs = glob($bundlesDir . '/*');
            if (count($webBundleDirs) == 0) {
                throw new \Exception('No bundles in the web directory found? 
                    Try do "app/console assets:install" first.');
            }
        }

        $fp = fopen($assetsFile, 'w');
        flock($fp, LOCK_EX);
        fwrite($fp, "<?php\nreturn array(");

        foreach ($webBundleDirs as $webBundleDir) {
            $webBundleDirSize = $trimBundlename ? 
                strlen($webBundleDir) :
                strlen(dirname($webBundleDir));

            foreach (Finder::create()->files()->name('*')->in($webBundleDir) as $file) {
                fwrite($fp, 
                    '"' . substr($file, $webBundleDirSize + 1) . 
                    '" => "' . 
                    substr(md5_file($file), 0, 16) . "\",\n"
                );
            }
        }

        fwrite($fp, ");");
        flock($fp, LOCK_UN);
        fclose($fp);

        $output->writeln('Cool, an assets file is generated: ' . $assetsFile);
    }
}
