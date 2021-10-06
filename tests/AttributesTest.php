<?php

namespace Spatie\BetterTypes\Tests;

use Attribute;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Spatie\BetterTypes\Attributes;

class AttributesTest extends TestCase
{
    /** @test */
    public function test_new_from_class_name()
    {
        $attribute = Attributes::new(AttributesTestClass::class)
            ->instanceOf(AttributesTestAttribute::class)
            ->first();

        $this->assertInstanceOf(AttributesTestAttribute::class, $attribute);
    }

    /** @test */
    public function test_new_from_reflection_class()
    {
        $attribute = Attributes::new(new ReflectionClass(AttributesTestClass::class))
            ->instanceOf(AttributesTestAttribute::class)
            ->first();

        $this->assertInstanceOf(AttributesTestAttribute::class, $attribute);
    }

    /** @test */
    public function test_new_from_object()
    {
        $attribute = Attributes::new(new AttributesTestClass())
            ->instanceOf(AttributesTestAttribute::class)
            ->first();

        $this->assertInstanceOf(AttributesTestAttribute::class, $attribute);
    }

    /** @test */
    public function test_new_from_reflection_method()
    {
        $attribute = Attributes::new(new ReflectionMethod(AttributesTestClass::class, 'test'))
            ->instanceOf(AttributesTestMethodAttribute::class)
            ->first();

        $this->assertInstanceOf(AttributesTestMethodAttribute::class, $attribute);
    }

    /** @test */
    public function test_new_from_array()
    {
        $attribute = Attributes::new([AttributesTestClass::class, 'test'])
            ->instanceOf(AttributesTestMethodAttribute::class)
            ->first();

        $this->assertInstanceOf(AttributesTestMethodAttribute::class, $attribute);
    }

    /** @test */
    public function test_new_from_array_with_object()
    {
        $attribute = Attributes::new([new AttributesTestClass(), 'test'])
            ->instanceOf(AttributesTestMethodAttribute::class)
            ->first();

        $this->assertInstanceOf(AttributesTestMethodAttribute::class, $attribute);
    }

    /** @test */
    public function test_filter()
    {
        $attribute = Attributes::new(AttributesTestClass::class)
            ->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === AttributesTestOtherAttribute::class)
            ->first();

        $this->assertInstanceOf(AttributesTestOtherAttribute::class, $attribute);
    }

    /** @test */
    public function test_reject()
    {
        $attribute = Attributes::new(AttributesTestClass::class)
            ->reject(fn (ReflectionAttribute $attribute) => $attribute->getName() === AttributesTestOtherAttribute::class)
            ->first();

        $this->assertInstanceOf(AttributesTestAttribute::class, $attribute);
    }

    /** @test */
    public function test_as_attribute()
    {
        $attribute = Attributes::new(AttributesTestClass::class)
            ->reject(fn (ReflectionAttribute $attribute) => $attribute->getName() === AttributesTestOtherAttribute::class)
            ->asAttributes()
            ->first();

        $this->assertInstanceOf(ReflectionAttribute::class, $attribute);
        $this->assertEquals(AttributesTestAttribute::class, $attribute->getName());
    }

    /** @test */
    public function test_all()
    {
        $attributes = Attributes::new(AttributesTestClass::class)->all();

        $this->assertCount(2, $attributes);
        $this->assertInstanceOf(AttributesTestAttribute::class, $attributes[0]);
        $this->assertInstanceOf(AttributesTestOtherAttribute::class, $attributes[1]);
    }

    /** @test */
    public function test_all_with_filter()
    {
        $attributes = Attributes::new(AttributesTestClass::class)
            ->filter(fn (ReflectionAttribute $attribute) => $attribute->getName() === AttributesTestOtherAttribute::class)
            ->all();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(AttributesTestOtherAttribute::class, $attributes[0]);
    }

    /** @test */
    public function test_all_with_as_attributes()
    {
        $attributes = Attributes::new(AttributesTestClass::class)
            ->asAttributes()
            ->all();

        $this->assertEquals(2, $attributes->count());
        $this->assertInstanceOf(ReflectionAttribute::class, $attributes[0]);
        $this->assertInstanceOf(ReflectionAttribute::class, $attributes[1]);
    }
}


#[AttributesTestAttribute('test')]
#[AttributesTestOtherAttribute]
class AttributesTestClass
{
    #[AttributesTestMethodAttribute]
    public function test()
    {
    }
}

#[Attribute]
class AttributesTestAttribute
{
    public function __construct(public string $name)
    {
    }
}

#[Attribute]
class AttributesTestOtherAttribute
{
    public function __construct()
    {
    }
}

#[Attribute]
class AttributesTestMethodAttribute
{
    public function __construct()
    {
    }
}
