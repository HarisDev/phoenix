<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static $db_inited = false;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Setup test class before running tests
     *
     * @beforeClass
     * @return void
     */

    public function setUp(): void
    {
        parent::setUp();

        if (!static::$db_inited) {
            static::$db_inited = true;
            $path = __DIR__ . '../../../sqlite.testing.database';
            file_put_contents($path, '');
            Artisan::call('laraone:install');
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            // '--disable-gpu',
            // '--headless',
            '--window-size=1220,1080',
            '--log-level=3',
            // '--silent'
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
