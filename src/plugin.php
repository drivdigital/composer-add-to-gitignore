<?php
namespace drivdigital;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;

class plugin implements PluginInterface, EventSubscriberInterface {
  /** @type Composer */
  protected $composer;

  /** @type IOInterface */
  protected $io;

  public function activate( Composer $composer, IOInterface $io ) {
    $this->composer = $composer;
    $this->io = $io;
    $this->io->write( __CLASS__ . '::' . __METHOD__ . "()" );
  }

  /**
  * Returns an array of event names this subscriber wants to listen to.
  * @return array The event names to listen to
  */
  public static function getSubscribedEvents() {
    return [
      PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackageUpdate',
      PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageInstall',
    ];
  }
  public function onPostPackageUpdate( PackageEvent $event ) {
    $this->io->write( __CLASS__ . '::' . __METHOD__ . "()" );
    $this->add_package_to_gitignore( $event );
  }
  public function onPostPackageInstall( PackageEvent $event ) {
    $this->io->write( __CLASS__ . '::' . __METHOD__ . "()" );
    $this->add_package_to_gitignore( $event );
  }
  public function add_package_to_gitignore( $event ) {
    $package = $event->getOperation()->getPackage();
    $installationManager = $event->getComposer()->getInstallationManager();
    $dir = $installationManager->getInstallPath($package);
    if ( strpos( $dir, 'vendor' ) === 0 )
      return;
    $content = '';
    if ( file_exists( '.gitignore' ) ) {
      $content = file_get_contents( '.gitignore' );
    }
    if ( false !== strpos( $content, $dir ) )
      return;

    $content =  trim( $content ) ."\n/$dir\n";
    file_put_contents( '.gitignore', $content );
    $this->io->write( "\033[1;35mAdded \"$dir\" to .gitignore\033[0m\n" );
  }
}
