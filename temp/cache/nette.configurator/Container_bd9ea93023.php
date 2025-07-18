<?php
// source: D:\_coding\nette\fakturacni-system/config/common.neon
// source: D:\_coding\nette\fakturacni-system/config/services.neon
// source: array

/** @noinspection PhpParamsInspection,PhpMethodMayBeStaticInspection */

declare(strict_types=1);

class Container_bd9ea93023 extends Nette\DI\Container
{
	protected array $aliases = [
		'application' => 'application.application',
		'cacheStorage' => 'cache.storage',
		'database.default' => 'database.default.connection',
		'database.default.context' => 'database.default.explorer',
		'httpRequest' => 'http.request',
		'httpResponse' => 'http.response',
		'nette.cacheJournal' => 'cache.journal',
		'nette.database.default' => 'database.default',
		'nette.database.default.context' => 'database.default.explorer',
		'nette.httpRequestFactory' => 'http.requestFactory',
		'nette.latteFactory' => 'latte.latteFactory',
		'nette.mailer' => 'mail.mailer',
		'nette.presenterFactory' => 'application.presenterFactory',
		'nette.templateFactory' => 'latte.templateFactory',
		'nette.userStorage' => 'security.userStorage',
		'session' => 'session.session',
		'user' => 'security.user',
	];

	protected array $wiring = [
		'Nette\DI\Container' => [['container']],
		'Nette\Application\Application' => [['application.application']],
		'Nette\Application\IPresenterFactory' => [['application.presenterFactory']],
		'Nette\Application\LinkGenerator' => [['application.linkGenerator']],
		'Nette\Caching\Storages\Journal' => [['cache.journal']],
		'Nette\Caching\Storage' => [['cache.storage']],
		'Nette\Database\Connection' => [['database.default.connection']],
		'Nette\Database\IStructure' => [['database.default.structure']],
		'Nette\Database\Structure' => [['database.default.structure']],
		'Nette\Database\Conventions' => [['database.default.conventions']],
		'Nette\Database\Conventions\DiscoveredConventions' => [['database.default.conventions']],
		'Nette\Database\Explorer' => [['database.default.explorer']],
		'Nette\Http\RequestFactory' => [['http.requestFactory']],
		'Nette\Http\IRequest' => [['http.request']],
		'Nette\Http\Request' => [['http.request']],
		'Nette\Http\IResponse' => [['http.response']],
		'Nette\Http\Response' => [['http.response']],
		'Nette\Bridges\ApplicationLatte\LatteFactory' => [['latte.latteFactory']],
		'Nette\Application\UI\TemplateFactory' => [['latte.templateFactory']],
		'Nette\Bridges\ApplicationLatte\TemplateFactory' => [['latte.templateFactory']],
		'Nette\Mail\Mailer' => [['mail.mailer']],
		'App\Mail\TestMailer' => [['mail.mailer']],
		'Nette\Security\Passwords' => [['security.passwords']],
		'Nette\Security\UserStorage' => [['security.userStorage']],
		'Nette\Security\User' => [['security.user']],
		'Nette\Http\Session' => [['session.session']],
		'Tracy\ILogger' => [['tracy.logger']],
		'Tracy\BlueScreen' => [['tracy.blueScreen']],
		'Tracy\Bar' => [['tracy.bar']],
		'Nette\Routing\RouteList' => [['router']],
		'Nette\Routing\Router' => [['router']],
		'ArrayAccess' => [
			2 => [
				'router',
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\Application\Routers\RouteList' => [['router']],
		'App\Model\ClientsManager' => [['01']],
		'App\Model\InvoicesManager' => [['02']],
		'App\Model\CompanyManager' => [['03']],
		'App\Model\QrPaymentService' => [['04']],
		'App\Security\SecurityLogger' => [['05']],
		'App\Model\AresService' => [['06']],
		'App\Model\ModuleManager' => [['07']],
		'App\Model\EmailService' => [['08']],
		'App\Model\TenantManager' => [['09']],
		'App\Security\SecurityValidator' => [['010']],
		'App\Security\RateLimiter' => [['011']],
		'App\Security\RateLimitCleaner' => [['012']],
		'Nette\Security\Authenticator' => [['authenticator']],
		'Nette\Security\IAuthenticator' => [['authenticator']],
		'App\Model\UserManager' => [['authenticator']],
		'App\Presentation\BasePresenter' => [
			2 => [
				'application.1',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\Application\UI\Presenter' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\Application\UI\Control' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\Application\UI\Component' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\ComponentModel\Container' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\ComponentModel\Component' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\ComponentModel\IComponent' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\ComponentModel\IContainer' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\Application\UI\SignalReceiver' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\Application\UI\StatePersistent' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\Application\UI\Renderable' => [
			2 => [
				'application.1',
				'application.2',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
			],
		],
		'Nette\Application\IPresenter' => [
			2 => [
				'application.1',
				'application.2',
				'application.3',
				'application.4',
				'application.5',
				'application.6',
				'application.7',
				'application.8',
				'application.9',
				'application.10',
				'application.11',
				'application.12',
				'application.13',
			],
		],
		'App\Presentation\Clients\ClientsPresenter' => [2 => ['application.1']],
		'App\Presentation\Error\Error4xx\Error4xxPresenter' => [2 => ['application.2']],
		'App\Presentation\Error\Error5xx\Error5xxPresenter' => [2 => ['application.3']],
		'App\Presentation\Home\HomePresenter' => [2 => ['application.4']],
		'App\Presentation\Invoices\InvoicesPresenter' => [2 => ['application.5']],
		'App\Presentation\Modules\DetailPresenter' => [2 => ['application.6']],
		'App\Presentation\ModuleAdmin\ModuleAdminPresenter' => [2 => ['application.7']],
		'App\Presentation\Settings\SettingsPresenter' => [2 => ['application.8']],
		'App\Presentation\Sign\SignPresenter' => [2 => ['application.9']],
		'App\Presentation\Tenants\TenantsPresenter' => [2 => ['application.10']],
		'App\Presentation\Users\UsersPresenter' => [2 => ['application.11']],
		'NetteModule\ErrorPresenter' => [2 => ['application.12']],
		'NetteModule\MicroPresenter' => [2 => ['application.13']],
		'App\Core\RouterFactory' => [['013']],
		'Modules\Financial_reports\FinancialReportsService' => [['014']],
	];


	public function __construct(array $params = [])
	{
		parent::__construct($params);
	}


	public function createService01(): App\Model\ClientsManager
	{
		return new App\Model\ClientsManager($this->getService('database.default.explorer'));
	}


	public function createService02(): App\Model\InvoicesManager
	{
		return new App\Model\InvoicesManager($this->getService('database.default.explorer'));
	}


	public function createService03(): App\Model\CompanyManager
	{
		return new App\Model\CompanyManager($this->getService('database.default.explorer'));
	}


	public function createService04(): App\Model\QrPaymentService
	{
		return new App\Model\QrPaymentService;
	}


	public function createService05(): App\Security\SecurityLogger
	{
		return new App\Security\SecurityLogger($this->getService('tracy.logger'), $this->getService('http.request'));
	}


	public function createService06(): App\Model\AresService
	{
		return new App\Model\AresService($this->getService('tracy.logger'));
	}


	public function createService07(): App\Model\ModuleManager
	{
		return new App\Model\ModuleManager($this->getService('tracy.logger'), $this->getService('database.default.explorer'));
	}


	public function createService08(): App\Model\EmailService
	{
		return new App\Model\EmailService($this->getService('mail.mailer'), $this->getService('application.linkGenerator'));
	}


	public function createService09(): App\Model\TenantManager
	{
		return new App\Model\TenantManager(
			$this->getService('database.default.explorer'),
			$this->getService('05'),
			$this->getService('security.passwords'),
		);
	}


	public function createService010(): App\Security\SecurityValidator
	{
		return new App\Security\SecurityValidator;
	}


	public function createService011(): App\Security\RateLimiter
	{
		return new App\Security\RateLimiter($this->getService('database.default.explorer'), $this->getService('05'));
	}


	public function createService012(): App\Security\RateLimitCleaner
	{
		return new App\Security\RateLimitCleaner($this->getService('database.default.explorer'), $this->getService('05'));
	}


	public function createService013(): App\Core\RouterFactory
	{
		return new App\Core\RouterFactory;
	}


	public function createService014(): Modules\Financial_reports\FinancialReportsService
	{
		return new Modules\Financial_reports\FinancialReportsService(
			$this->getService('02'),
			$this->getService('03'),
			$this->getService('database.default.explorer'),
		);
	}


	public function createServiceApplication__1(): App\Presentation\Clients\ClientsPresenter
	{
		$service = new App\Presentation\Clients\ClientsPresenter(
			$this->getService('01'),
			$this->getService('database.default.explorer'),
			$this->getService('06'),
			$this->getService('tracy.logger'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__10(): App\Presentation\Tenants\TenantsPresenter
	{
		$service = new App\Presentation\Tenants\TenantsPresenter(
			$this->getService('09'),
			$this->getService('06'),
			$this->getService('tracy.logger'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__11(): App\Presentation\Users\UsersPresenter
	{
		$service = new App\Presentation\Users\UsersPresenter($this->getService('authenticator'));
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__12(): NetteModule\ErrorPresenter
	{
		return new NetteModule\ErrorPresenter($this->getService('tracy.logger'));
	}


	public function createServiceApplication__13(): NetteModule\MicroPresenter
	{
		return new NetteModule\MicroPresenter($this, $this->getService('http.request'), $this->getService('router'));
	}


	public function createServiceApplication__2(): App\Presentation\Error\Error4xx\Error4xxPresenter
	{
		$service = new App\Presentation\Error\Error4xx\Error4xxPresenter;
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__3(): App\Presentation\Error\Error5xx\Error5xxPresenter
	{
		return new App\Presentation\Error\Error5xx\Error5xxPresenter($this->getService('tracy.logger'));
	}


	public function createServiceApplication__4(): App\Presentation\Home\HomePresenter
	{
		$service = new App\Presentation\Home\HomePresenter(
			$this->getService('02'),
			$this->getService('01'),
			$this->getService('03'),
			$this->getService('authenticator'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__5(): App\Presentation\Invoices\InvoicesPresenter
	{
		$service = new App\Presentation\Invoices\InvoicesPresenter(
			$this->getService('02'),
			$this->getService('01'),
			$this->getService('03'),
			$this->getService('04'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__6(): App\Presentation\Modules\DetailPresenter
	{
		$service = new App\Presentation\Modules\DetailPresenter($this->getService('07'));
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__7(): App\Presentation\ModuleAdmin\ModuleAdminPresenter
	{
		$service = new App\Presentation\ModuleAdmin\ModuleAdminPresenter(
			$this->getService('07'),
			$this->getService('tracy.logger'),
			$this->getService('02'),
			$this->getService('03'),
			$this->getService('database.default.explorer'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__8(): App\Presentation\Settings\SettingsPresenter
	{
		$service = new App\Presentation\Settings\SettingsPresenter($this->getService('03'));
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__9(): App\Presentation\Sign\SignPresenter
	{
		$service = new App\Presentation\Sign\SignPresenter(
			$this->getService('authenticator'),
			$this->getService('05'),
			$this->getService('08'),
			$this->getService('09'),
			$this->getService('011'),
			$this->getService('database.default.explorer'),
		);
		$service->injectPrimary(
			$this->getService('http.request'),
			$this->getService('http.response'),
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('session.session'),
			$this->getService('security.user'),
			$this->getService('latte.templateFactory'),
		);
		$service->injectSecurityLogger($this->getService('05'));
		$service->injectRateLimiter($this->getService('011'));
		$service->injectModuleManager($this->getService('07'));
		$service->injectDatabase($this->getService('database.default.explorer'));
		$service->invalidLinkMode = 5;
		return $service;
	}


	public function createServiceApplication__application(): Nette\Application\Application
	{
		$service = new Nette\Application\Application(
			$this->getService('application.presenterFactory'),
			$this->getService('router'),
			$this->getService('http.request'),
			$this->getService('http.response'),
		);
		Nette\Bridges\ApplicationDI\ApplicationExtension::initializeBlueScreenPanel(
			$this->getService('tracy.blueScreen'),
			$service,
		);
		$this->getService('tracy.bar')->addPanel(new Nette\Bridges\ApplicationTracy\RoutingPanel(
			$this->getService('router'),
			$this->getService('http.request'),
			$this->getService('application.presenterFactory'),
		));
		return $service;
	}


	public function createServiceApplication__linkGenerator(): Nette\Application\LinkGenerator
	{
		return new Nette\Application\LinkGenerator(
			$this->getService('router'),
			$this->getService('http.request')->getUrl()->withoutUserInfo(),
			$this->getService('application.presenterFactory'),
		);
	}


	public function createServiceApplication__presenterFactory(): Nette\Application\IPresenterFactory
	{
		$service = new Nette\Application\PresenterFactory(new Nette\Bridges\ApplicationDI\PresenterFactoryCallback(
			$this,
			5,
			'D:\_coding\nette\fakturacni-system/temp/cache/nette.application/touch',
		));
		$service->setMapping(['*' => 'App\Presentation\*\**Presenter']);
		return $service;
	}


	public function createServiceAuthenticator(): App\Model\UserManager
	{
		return new App\Model\UserManager(
			$this->getService('database.default.explorer'),
			$this->getService('security.passwords'),
			$this->getService('05'),
		);
	}


	public function createServiceCache__journal(): Nette\Caching\Storages\Journal
	{
		return new Nette\Caching\Storages\SQLiteJournal('D:\_coding\nette\fakturacni-system/temp/cache/journal.s3db');
	}


	public function createServiceCache__storage(): Nette\Caching\Storage
	{
		return new Nette\Caching\Storages\FileStorage(
			'D:\_coding\nette\fakturacni-system/temp/cache',
			$this->getService('cache.journal'),
		);
	}


	public function createServiceContainer(): Nette\DI\Container
	{
		return $this;
	}


	public function createServiceDatabase__default__connection(): Nette\Database\Connection
	{
		$service = new Nette\Database\Connection(
			'mysql:host=127.0.0.1;dbname=fakturacni_system',
			/*sensitive{*/'root'/*}*/,
			null,
			['lazy' => true],
		);
		Nette\Bridges\DatabaseTracy\ConnectionPanel::initialize(
			$service,
			true,
			'default',
			true,
			$this->getService('tracy.bar'),
			$this->getService('tracy.blueScreen'),
		);
		return $service;
	}


	public function createServiceDatabase__default__conventions(): Nette\Database\Conventions\DiscoveredConventions
	{
		return new Nette\Database\Conventions\DiscoveredConventions($this->getService('database.default.structure'));
	}


	public function createServiceDatabase__default__explorer(): Nette\Database\Explorer
	{
		return new Nette\Database\Explorer(
			$this->getService('database.default.connection'),
			$this->getService('database.default.structure'),
			$this->getService('database.default.conventions'),
			$this->getService('cache.storage'),
		);
	}


	public function createServiceDatabase__default__structure(): Nette\Database\Structure
	{
		return new Nette\Database\Structure($this->getService('database.default.connection'), $this->getService('cache.storage'));
	}


	public function createServiceHttp__request(): Nette\Http\Request
	{
		return $this->getService('http.requestFactory')->fromGlobals();
	}


	public function createServiceHttp__requestFactory(): Nette\Http\RequestFactory
	{
		$service = new Nette\Http\RequestFactory;
		$service->setProxy([]);
		return $service;
	}


	public function createServiceHttp__response(): Nette\Http\Response
	{
		$service = new Nette\Http\Response;
		$service->cookieSecure = $this->getService('http.request')->isSecured();
		return $service;
	}


	public function createServiceLatte__latteFactory(): Nette\Bridges\ApplicationLatte\LatteFactory
	{
		return new class ($this) implements Nette\Bridges\ApplicationLatte\LatteFactory {
			public function __construct(
				private Container_bd9ea93023 $container,
			) {
			}


			public function create(): Latte\Engine
			{
				$service = new Latte\Engine;
				$service->setTempDirectory('D:\_coding\nette\fakturacni-system/temp/cache/latte');
				$service->setAutoRefresh(true);
				$service->setStrictTypes(true);
				$service->setStrictParsing(true);
				$service->enablePhpLinter(null);
				$service->setLocale(null);
				func_num_args() && $service->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(func_get_arg(0)));
				$service->addExtension(new Nette\Bridges\CacheLatte\CacheExtension($this->container->getService('cache.storage')));
				$service->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);
				$service->addExtension(new App\Presentation\Accessory\LatteExtension);
				return $service;
			}
		};
	}


	public function createServiceLatte__templateFactory(): Nette\Bridges\ApplicationLatte\TemplateFactory
	{
		$service = new Nette\Bridges\ApplicationLatte\TemplateFactory(
			$this->getService('latte.latteFactory'),
			$this->getService('http.request'),
			$this->getService('security.user'),
			$this->getService('cache.storage'),
			null,
		);
		Nette\Bridges\ApplicationDI\LatteExtension::initLattePanel($service, $this->getService('tracy.bar'), false);
		return $service;
	}


	public function createServiceMail__mailer(): App\Mail\TestMailer
	{
		return new App\Mail\TestMailer('D:\_coding\nette\fakturacni-system/temp');
	}


	public function createServiceRouter(): Nette\Application\Routers\RouteList
	{
		return App\Core\RouterFactory::createRouter();
	}


	public function createServiceSecurity__passwords(): Nette\Security\Passwords
	{
		return new Nette\Security\Passwords;
	}


	public function createServiceSecurity__user(): Nette\Security\User
	{
		$service = new Nette\Security\User($this->getService('security.userStorage'), $this->getService('authenticator'));
		$this->getService('tracy.bar')->addPanel(new Nette\Bridges\SecurityTracy\UserPanel($service));
		return $service;
	}


	public function createServiceSecurity__userStorage(): Nette\Security\UserStorage
	{
		return new Nette\Bridges\SecurityHttp\SessionStorage($this->getService('session.session'));
	}


	public function createServiceSession__session(): Nette\Http\Session
	{
		$service = new Nette\Http\Session($this->getService('http.request'), $this->getService('http.response'));
		$service->setExpiration('20 minutes');
		$service->setOptions(['name' => 'fakturacni_system_session', 'cookieHttponly' => true, 'cookieSamesite' => 'Strict']);
		return $service;
	}


	public function createServiceTracy__bar(): Tracy\Bar
	{
		return Tracy\Debugger::getBar();
	}


	public function createServiceTracy__blueScreen(): Tracy\BlueScreen
	{
		return Tracy\Debugger::getBlueScreen();
	}


	public function createServiceTracy__logger(): Tracy\ILogger
	{
		return Tracy\Debugger::getLogger();
	}


	public function initialize(): void
	{
		// di.
		(function () {
			$this->getService('tracy.bar')->addPanel(new Nette\Bridges\DITracy\ContainerPanel($this));
		})();
		// http.
		(function () {
			$response = $this->getService('http.response');
			$response->setHeader('X-Powered-By', 'Nette Framework 3');
			$response->setHeader('Content-Type', 'text/html; charset=utf-8');
			$response->setHeader('X-Frame-Options', 'SAMEORIGIN');
			Nette\Http\Helpers::initCookie($this->getService('http.request'), $response);
		})();
		// session.
		(function () {
			$this->getService('session.session')->start();
		})();
		// tracy.
		(function () {
			if (!Tracy\Debugger::isEnabled()) { return; }
			$logger = $this->getService('tracy.logger');
			if ($logger instanceof Tracy\Logger) $logger->mailer = [
				new Tracy\Bridges\Nette\MailSender(
					$this->getService('mail.mailer'),
					null,
					$this->getByType('Nette\Http\Request', false)?->getUrl()->getHost(),
				),
				'send',
			];
		})();
	}
}
