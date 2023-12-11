<?php

namespace Bluecadet\Commands;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * @var \Composer\Composer $composer
   */
  protected $composer;

  /**
   * @var \Composer\IO\IOInterface $io
   */
  protected $io;

  public function activate(Composer $composer, IOInterface $io)
  {
      $this->composer = $composer;
      $this->io = $io;

      $this->io->write(["Hello World"], TRUE);
  }

  public function deactivate(Composer $composer, IOInterface $io)
  {
  }

  public function uninstall(Composer $composer, IOInterface $io)
  {
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   */
  public static function getSubscribedEvents() {
    return [
      ScriptEvents::POST_INSTALL_CMD => array('setupForPantheon'),
      ScriptEvents::POST_UPDATE_CMD => array('setupForPantheon'),
      // PackageEvents::POST_PACKAGE_UNINSTALL => array('updateManifest', 10),
    ];
  }

  public function setupForPantheon(Event $event) {
    $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
    $cwd = $vendorDir . "/..";

    $this->io->write(["", "<info>Starting settup for Pantheon deploy.</info>"], TRUE);

    // print_r($vendorDir);
    // $this->io->write($vendorDir, TRUE);
    // $this->io->write($cwd, TRUE);

    // Update git ignore.
    $source_gitignore = $vendorDir . "/bluecadet/bc_hgse_deployer/assets/.gitignore";
    $dest_gitignore = $cwd . "/.gitignore";
    copy($source_gitignore, $dest_gitignore);
    $this->io->write("  - Copied .gitignore file", TRUE);

    // Copy settings.pantheon.php.
    $source_gitignore = $vendorDir . "/bluecadet/bc_hgse_deployer/assets/settings.pantheon.php";
    $dest_gitignore = $cwd . "/web/sites/default/settings.pantheon.php";
    copy($source_gitignore, $dest_gitignore);
    $this->io->write("  - Copied Pantheon settings file", TRUE);

    // Attempt to commit changes.
    try {
      exec('git rm -r --cached .');
      exec('git add .');
      exec('git commit -am "Remove ignored files"');
      $this->io->write("  - Commited ignored files", TRUE);
    }
    catch(\Exception $e) {
      $this->io->writeError($e->getMessage(), TRUE);
    }

    $this->io->write(["", "<info>Finished settup for Pantheon deploy.</info>", ""], TRUE);

  }

}