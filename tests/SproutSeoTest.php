<?php
namespace Craft;

use \Mockery as m;

class SproutSeoTest extends BaseTest
{
	protected $config;
	protected $settings;
	protected $sproutSeoMetaService;

	public function setUp()
	{
		$this->config = m::mock('Craft\ConfigService');
		$this->config->shouldReceive('getIsInitialized')->andReturn(true);
		$this->config->shouldReceive('usePathInfo')->andReturn(true)->byDefault();

		$this->config->shouldReceive('get')->with('usePathInfo')->andReturn(true)->byDefault();
		$this->config->shouldReceive('get')->with('cpTrigger')->andReturn('admin')->byDefault();
		$this->config->shouldReceive('get')->with('pageTrigger')->andReturn('p')->byDefault();
		$this->config->shouldReceive('get')->with('actionTrigger')->andReturn('action')->byDefault();
		$this->config->shouldReceive('get')->with('translationDebugOutput')->andReturn(false)->byDefault();

		$this->config->shouldReceive('getLocalized')->with('loginPath')->andReturn('login')->byDefault();
		$this->config->shouldReceive('getLocalized')->with('logoutPath')->andReturn('logout')->byDefault();
		$this->config->shouldReceive('getLocalized')->with('setPasswordPath')->andReturn('setpassword')->byDefault();

		$this->config->shouldReceive('getCpLoginPath')->andReturn('login')->byDefault();
		$this->config->shouldReceive('getCpLogoutPath')->andReturn('logout')->byDefault();
		$this->config->shouldReceive('getCpSetPasswordPath')->andReturn('setpassword')->byDefault();
		$this->config->shouldReceive('getResourceTrigger')->andReturn('resource')->byDefault();

		$this->setComponent(craft(), 'config', $this->config);
		$this->setEnvironment();
		$this->loadServices();
	}

	/*
	 * @endTestSuite
	 */

	protected function setEnvironment()
	{
		$this->settings         = m::mock('Craft\Model');
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	protected function loadServices()
	{
		require_once __DIR__ . '/../services/SproutSeo_MetaService.php';

		$this->sproutSeoMetaService = new SproutSeo_MetaService(true, true);
	}

	protected function inspect($data)
	{
		fwrite(STDERR, print_r($data));
	}
}
