<?php

namespace Seat\Tests\Services\InjectedRelations;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Seat\Services\Exceptions\InjectedRelationConflictException;
use Seat\Services\Services\InjectedRelationRegistry;
use Seat\Services\ServicesServiceProvider;
use Seat\Tests\Services\InjectedRelations\Extensions\ModelAExtension;
use Seat\Tests\Services\InjectedRelations\Models\ModelA;
use Seat\Tests\Services\InjectedRelations\Models\ModelB;


class InjectedRelationsTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('database.redis.client', 'mock');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array|string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            ServicesServiceProvider::class
        ];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Test if InjectedRelationRegistry works
     * @throws \Seat\Services\Exceptions\InjectedRelationConflictException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testRegistry(){
        ModelA::injectRelationsFrom(ModelAExtension::class);
        $registry = app()->make(InjectedRelationRegistry::class);

        $this->assertEquals(ModelAExtension::class,$registry->getExtensionClassFor(ModelA::class,'modelBInjected'));
        $this->assertEquals(null,$registry->getExtensionClassFor(ModelA::class,'doesntexist'));
    }

    /**
     * Test if registering the same relation twice errors
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Seat\Services\Exceptions\InjectedRelationConflictException
     */
    public function testInjectionConflict(){
        $this->expectException(InjectedRelationConflictException::class);
        ModelA::injectRelationsFrom(ModelAExtension::class);
        ModelA::injectRelationsFrom(ModelAExtension::class);
    }

    /**
     * Test if injected relations are working
     * @return void
     * @throws InjectedRelationConflictException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testInjectedRelations(){
        $a = ModelA::factory()->create();
        ModelB::factory()
            ->for($a)
            ->create();

        ModelA::injectRelationsFrom(ModelAExtension::class);

        // factory might already trigger the cache, therefore make sure we have new models
        $a = ModelA::first();
        $b = ModelB::first();

        // ensure normal relations are still working
        // b->a
        $this->assertNotEquals(null, $b->modelA->id);
        $this->assertEquals($a->id, $b->modelA->id);
        // a->b
        $this->assertNotEquals(null, $a->modelB->id);
        $this->assertEquals($b->id, $a->modelB->id);

        //test injected relationship
        // as attributes
        $this->assertNotEquals(null, $a->modelBInjected->id);
        $this->assertEquals($b->id, $a->modelBInjected->id);
        // as function calls
        $this->assertNotEquals(null, $a->modelBInjected()->first()->id);
        $this->assertEquals($b->id, $a->modelBInjected()->first()->id);
    }

    /**
     * Test if eager loading with 'with' works
     * @throws InjectedRelationConflictException
     * @throws BindingResolutionException
     */
    public function testEagerLoading(){
        $a = ModelA::factory()->create();
        $b = ModelB::factory()
            ->for($a)
            ->create();

        ModelA::injectRelationsFrom(ModelAExtension::class);

        $result = ModelA::with("modelBInjected")->first();
        $loaded_relations = $result->getRelations();

        $this->assertArrayHasKey('modelBInjected', $loaded_relations);

        $model_b = $loaded_relations['modelBInjected'];
        $this->assertInstanceOf(ModelB::class, $model_b);
        $this->assertEquals($model_b->id, $b->id);
    }
}