<?php

namespace Seat\Tests\Services\CleanCCPHtml;

use PHPUnit\Framework\TestCase;

class CleanCCPHtmlTest extends TestCase
{
    public function testEncapsulationRemoval(){
        $this->assertEquals('test',clean_ccp_html("u'test"));
    }

    public function testWithoutEncapsulation(){
        $this->assertEquals('test',clean_ccp_html('test'));
        //also test that the other feature continue to work
        $this->assertEquals('test',clean_ccp_html('<xyz>test</xyz>'));
    }

    public function testEscapedUnicodeDecoding(){
        $this->assertEquals('&#21019; &auml; &#128512;',clean_ccp_html('\u521b ä 😀'));
    }
}