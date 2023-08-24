<?php

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class SchoolTest extends TestCase
{
    private const POST_TYPE = 'school';

    public function testPostTypeExists()
    {
        $this->assertContains(self::POST_TYPE, get_post_types());
    }

    public function testRestApiEnabledOnPostType()
    {
        $this->assertTrue(get_post_type_object(self::POST_TYPE)->show_in_rest);
    }
}
