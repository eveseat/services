<?php

namespace Seat\Tests\Services\InjectedRelations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;
use Seat\Services\Models\UserSetting;
use Seat\Services\Services\InjectedRelationRegistry;

class InjectedRelationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if InjectedRelationRegistry works
     * @throws \Seat\Services\Exceptions\InjectedRelationConflictException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testRegistry(){
        UserSetting::injectRelationsFrom(self::class);
        $registry = app()->make(InjectedRelationRegistry::class);

        $this->assertEquals(self::class,$registry->getExtensionClassFor(\Seat\Services\Models\UserSetting::class,'user'));
        $this->assertEquals(null,$registry->getExtensionClassFor(\Seat\Services\Models\UserSetting::class,'doesntexist'));

    }

    /**
     * Test if registering the same relation twice errors
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Seat\Services\Exceptions\InjectedRelationConflictException
     */
    public function testInjectionConflict(){
        \Seat\Services\Models\UserSetting::injectRelationsFrom(self::class);

        $failed = false;
        try {
            \Seat\Services\Models\UserSetting::injectRelationsFrom(self::class);
        } catch (\Seat\Services\Exceptions\InjectedRelationConflictException $e){
            $failed = true;
        }

        $this->assertTrue($failed);
    }

    public function user(UserSetting $model){
        return $model->belongsTo(User::class,"user_id","id");
    }

    protected function setUp(): void
    {
        parent::setUp();

        app()->singleton(InjectedRelationRegistry::class, function (){
            return new InjectedRelationRegistry();
        });
    }
}