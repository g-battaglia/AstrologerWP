<?php
/**
 * Main plugin bootstrap class.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api;

use Astrologer\Api\Blocks\SpikeBlocksRegistry;
use Astrologer\Api\Capabilities\CapabilityManager;
use Astrologer\Api\PostType\AstrologerChartPostType;
use Astrologer\Api\PostType\ChartTypeTaxonomy;
use Astrologer\Api\Repository\BirthDataRepository;
use Astrologer\Api\Repository\ChartRepository;
use Astrologer\Api\Rest\SpikeController;
use Astrologer\Api\Support\Contracts\Bootable;

/**
 * Plugin singleton. Instantiates the container and boots Bootable modules.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Whether boot() has already run.
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * Private constructor — use instance() instead.
	 */
	private function __construct() {
		$this->container = new Container();
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the service container.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Bootstrap the plugin. Registers and boots all Bootable modules.
	 *
	 * Safe to call multiple times — only runs once.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		/**
		 * List of Bootable class names to register and boot.
		 * Modules are added here as phases progress.
		 *
		 * @var list<class-string<Bootable>> $modules
		 */
		$modules = array(
			Admin\AdminMenu::class,
			AstrologerChartPostType::class,
			ChartTypeTaxonomy::class,
			CapabilityManager::class,
			ChartRepository::class,
			BirthDataRepository::class,
			SpikeBlocksRegistry::class,
			SpikeController::class,
		);

		foreach ( $modules as $module_class ) {
			$factory = self::create_factory( $module_class );
			$this->container->set( $module_class, $factory );

			$module = $this->container->get( $module_class );

			if ( $module instanceof Bootable ) {
				$module->boot();
			}
		}
	}

	/**
	 * Create a factory closure for a Bootable class.
	 *
	 * PhpStan needs this to infer the return type correctly.
	 *
	 * @param class-string<Bootable> $fqcn Fully qualified class name.
	 * @return \Closure(Container): Bootable
	 */
	private static function create_factory( string $fqcn ): \Closure {
		// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Parameter required by the Closure signature for the Container contract.
		return static fn ( Container $container ): Bootable => new $fqcn();
	}
}
