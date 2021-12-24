<?php

namespace N98\Magento\Command\Installer\SubCommand;

use GuzzleHttp\Client;
use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\OperatingSystem;

/**
 * Class InstallComposer
 * @package N98\Magento\Command\Installer\SubCommand
 */
class InstallComposer extends AbstractSubCommand
{
    /**
     * @var int
     */
    const EXEC_STATUS_OK = 0;

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function execute()
    {
        if (OperatingSystem::isProgramInstalled('composer.phar')) {
            $composerBin = 'composer.phar';
        } elseif (OperatingSystem::isProgramInstalled('composer')) {
            $composerBin = 'composer';
        }

        if (empty($composerBin)) {
            $composerBin = $this->downloadComposer();
        }

        if (empty($composerBin)) {
            throw new \Exception('Cannot find or install composer. Please try it manually. https://getcomposer.org/');
        }

        $this->output->writeln('<info>Found executable <comment>' . $composerBin . '</comment></info>');
        $this->config['composer_bin'] = [$composerBin];

        $composerUseSamePhpBinary = $this->hasFlagOrOptionalBoolOption('composer-use-same-php-binary', false);
        if ($composerUseSamePhpBinary) {
            $this->config['composer_bin'] = [
                OperatingSystem::getCurrentPhpBinary(),
                OperatingSystem::locateProgram($composerBin),
            ];
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function downloadComposer()
    {
        $this->output->writeln('<info>Could not find composer. Try to download it.</info>');

        $client = new Client();
        $response = $client->get('https://getcomposer.org/installer');
        $composerInstaller = (string) $response->getBody();

        $tempComposerInstaller = $this->config['initialFolder'] . '/_composer_installer.php';
        file_put_contents($tempComposerInstaller, $composerInstaller);

        $composerInstallerOptions = '--force --install-dir=' . $this->config['initialFolder'];

        if (OperatingSystem::isWindows()) {
            $installCommand = 'php ' . $tempComposerInstaller . ' ' . $composerInstallerOptions;
        } else {
            $installCommand = '/usr/bin/env php ' . $tempComposerInstaller . ' ' . $composerInstallerOptions;
        }

        $this->output->writeln('<comment>' . $installCommand . '</comment>');
        exec($installCommand, $installationOutput, $returnStatus);
        unlink($tempComposerInstaller);
        $installationOutput = implode(PHP_EOL, $installationOutput);
        if ($returnStatus !== self::EXEC_STATUS_OK) {
            throw new \Exception('Installation failed.' . $installationOutput);
        } else {
            $this->output->writeln('<info>Successfully installed composer to Magento root</info>');
        }

        return $this->config['initialFolder'] . '/composer.phar';
    }
}
