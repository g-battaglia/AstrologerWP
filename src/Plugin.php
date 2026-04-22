<?php
/**
 * Main plugin bootstrap class.
 *
 * @package Astrologer\Api
 */

declare( strict_types = 1 );

namespace Astrologer\Api;

use Astrologer\Api\Blocks\BlockCategory;
use Astrologer\Api\Blocks\BlocksRegistry;
use Astrologer\Api\Blocks\SpikeBlocksRegistry;
use Astrologer\Api\Capabilities\CapabilityManager;
use Astrologer\Api\Http\ApiClient;
use Astrologer\Api\Http\GeonamesClient;
use Astrologer\Api\PostType\AstrologerChartPostType;
use Astrologer\Api\PostType\ChartTypeTaxonomy;
use Astrologer\Api\Repository\BirthDataRepository;
use Astrologer\Api\Repository\ChartRepository;
use Astrologer\Api\Repository\SettingsRepository;
use Astrologer\Api\Rest\Controllers\BindingsController;
use Astrologer\Api\Rest\Controllers\BirthChartController;
use Astrologer\Api\Rest\Controllers\BirthDataController;
use Astrologer\Api\Rest\Controllers\ChartController;
use Astrologer\Api\Rest\Controllers\CompositeChartController;
use Astrologer\Api\Rest\Controllers\ContextController;
use Astrologer\Api\Rest\Controllers\GeonamesController;
use Astrologer\Api\Rest\Controllers\HealthController;
use Astrologer\Api\Rest\Controllers\LunarReturnChartController;
use Astrologer\Api\Rest\Controllers\McpController;
use Astrologer\Api\Rest\Controllers\MoonPhaseController;
use Astrologer\Api\Rest\Controllers\NatalChartController;
use Astrologer\Api\Rest\Controllers\NowChartController;
use Astrologer\Api\Rest\Controllers\RelationshipScoreController;
use Astrologer\Api\Rest\Controllers\SettingsController;
use Astrologer\Api\Rest\Controllers\SolarReturnChartController;
use Astrologer\Api\Rest\Controllers\SynastryAspectsController;
use Astrologer\Api\Rest\Controllers\SynastryChartController;
use Astrologer\Api\Rest\Controllers\TransitChartController;
use Astrologer\Api\Rest\RestServiceProvider;
use Astrologer\Api\Rest\SpikeController;
use Astrologer\Api\Services\ChartService;
use Astrologer\Api\Services\HooksRegistry;
use Astrologer\Api\Services\RateLimiter;
use Astrologer\Api\Services\SchoolPresetsService;
use Astrologer\Api\Support\Contracts\Bootable;
use Astrologer\Api\Support\Encryption\EncryptionService;

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

		// Register infrastructure services (non-Bootable, dependency graph).
		$this->register_services();

		/**
		 * List of Bootable class names to register and boot.
		 * Modules are added here as phases progress.
		 *
		 * @var list<class-string<Bootable>> $modules
		 */
		$modules = array(
			Admin\AdminMenu::class,
			Admin\SettingsPage::class,
			Admin\SetupWizardPage::class,
			Admin\HelpTabsProvider::class,
			Admin\DocumentationPage::class,
			AstrologerChartPostType::class,
			ChartTypeTaxonomy::class,
			CapabilityManager::class,
			ChartRepository::class,
			BirthDataRepository::class,
			BlockCategory::class,
			BlocksRegistry::class,
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

		// REST provider after modules — needs ChartRepository from container.
		$this->register_rest_provider();
	}

	/**
	 * Register infrastructure services in the container.
	 *
	 * These are non-Bootable services that form the dependency graph.
	 * Bootable modules receive them via constructor injection.
	 */
	private function register_services(): void {
		$this->container->set(
			EncryptionService::class,
			static fn (): EncryptionService => new EncryptionService(),
		);

		$this->container->set(
			SettingsRepository::class,
			function (): SettingsRepository {
				/** @var EncryptionService $encryption */
				$encryption = $this->container->get( EncryptionService::class );
				return new SettingsRepository( $encryption );
			},
		);

		$this->container->set(
			ApiClient::class,
			function (): ApiClient {
				/** @var SettingsRepository $settings */
				$settings = $this->container->get( SettingsRepository::class );
				return new ApiClient( $settings );
			},
		);

		$this->container->set(
			ChartService::class,
			function (): ChartService {
				/** @var ApiClient $client */
				$client = $this->container->get( ApiClient::class );
				return new ChartService( $client );
			},
		);

		$this->container->set(
			SchoolPresetsService::class,
			static fn (): SchoolPresetsService => new SchoolPresetsService(),
		);

		$this->container->set(
			HooksRegistry::class,
			static fn (): HooksRegistry => new HooksRegistry(),
		);

		$this->container->set(
			RateLimiter::class,
			static fn (): RateLimiter => new RateLimiter(),
		);

		$this->container->set(
			GeonamesClient::class,
			function (): GeonamesClient {
				/** @var SettingsRepository $settings */
				$settings = $this->container->get( SettingsRepository::class );
				return new GeonamesClient( $settings );
			},
		);
	}

	/**
	 * Build and register the RestServiceProvider with all 19 controllers.
	 *
	 * Each controller receives its dependencies from the container.
	 * The provider is booted immediately so routes are available on rest_api_init.
	 */
	private function register_rest_provider(): void {
		/** @var ChartService $chart_service */
		$chart_service = $this->container->get( ChartService::class );

		/** @var RateLimiter $rate_limiter */
		$rate_limiter = $this->container->get( RateLimiter::class );

		/** @var SettingsRepository $settings */
		$settings = $this->container->get( SettingsRepository::class );

		/** @var GeonamesClient $geonames */
		$geonames = $this->container->get( GeonamesClient::class );

		$provider = new RestServiceProvider(
			new NatalChartController( $chart_service, $rate_limiter ),
			new BirthChartController( $chart_service, $rate_limiter ),
			new SynastryChartController( $chart_service, $rate_limiter ),
			new SynastryAspectsController( $chart_service, $rate_limiter ),
			new TransitChartController( $chart_service, $rate_limiter ),
			new CompositeChartController( $chart_service, $rate_limiter ),
			new SolarReturnChartController( $chart_service, $rate_limiter ),
			new LunarReturnChartController( $chart_service, $rate_limiter ),
			new NowChartController( $chart_service, $rate_limiter ),
			new MoonPhaseController( $chart_service, $rate_limiter ),
			new RelationshipScoreController( $chart_service, $rate_limiter ),
			new ContextController( $chart_service, $rate_limiter ),
			new HealthController( $chart_service, $rate_limiter ),
			new McpController( $chart_service, $rate_limiter ),
			new BirthDataController( $chart_service, $rate_limiter ),
			new GeonamesController( $geonames, $rate_limiter ),
			new SettingsController( $settings, $rate_limiter ),
			new ChartController( $chart_service, $rate_limiter ),
			new BindingsController( $rate_limiter ),
		);

		$this->container->set( RestServiceProvider::class, fn (): RestServiceProvider => $provider );

		$provider->boot();
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
