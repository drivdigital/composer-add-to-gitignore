<?php
namespace drivdigital;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;

class composer_add_to_gitignore implements PluginInterface, EventSubscriberInterface {
  /** @type Composer */
  protected $composer;

  /** @type IOInterface */
  protected $io;

  public function activate( Composer $composer, IOInterface $io ) {
    $this->composer = $composer;
    $this->io = $io;
  }

  /**
  * Returns an array of event names this subscriber wants to listen to.
  * @return array The event names to listen to
  */
  public static function getSubscribedEvents() {
    return [
      PackageEvents::POST_PACKAGE_UPDATE => 'add_package_to_gitignore',
      PackageEvents::POST_PACKAGE_INSTALL => 'add_package_to_gitignore',
    ];
  }
  public function add_package_to_gitignore( $event ) {
    $package = $event->getOperation()->getPackage();
    $installationManager = $event->getComposer()->getInstallationManager();
    $dir = $installationManager->getInstallPath($package);
    // Avoid packages installed to vendor
    if ( strpos( $dir, 'vendor' ) === 0 )
      return;
    // Avoid absolute paths
    if ( '/' === $dir[0] )
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
