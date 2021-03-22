<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    protected static $migrated = false;

    protected function setUp(): Void
    {
        parent::setUp();

        if (!self::$migrated) {
            self::$migrated = true;

            // Resetting database by laravel
            $uses = array_flip(class_uses_recursive(static::class));
            if (isset($uses[RefreshDatabase::class]) || isset($uses[DatabaseMigrations::class])) {
                return;
            }

            DB::transaction(function () {
                $dir = __DIR__ . '/misc/sql/';
                if (is_dir($dir)) {
                    if ($dh = opendir($dir)) {
                        while (($file = readdir($dh)) !== false) {
                            if (is_file($dir . $file)) {
                                Log::info("setUp: " . $file . " を実行");
                                DB::statement(file_get_contents($dir . $file));
                            }
                        }
                        closedir($dh);
                    }
                }
            });
        }
    }
}
