<?php


namespace JIMP\BreadcrumbsBundle\Tests\Model;

use JIMP\BreadcrumbsBundle\Model\Breadcrumb;
use PHPUnit\Framework\TestCase;

class BreadcrumbTest extends TestCase
{
    /**
     * @test
     * @group model
     */
    public function constructor()
    {
        $bc = new Breadcrumb('home', '/welcome');
        $this->assertInstanceOf('\JIMP\BreadcrumbsBundle\Model\Breadcrumb', $bc);
    }

    /**
     * @test
     * @group model
     */
    public function getters()
    {
        $bc = new Breadcrumb('home', '/welcome');
        $this->assertEquals('home', $bc->label);
        $this->assertEquals('/welcome', $bc->url);
    }
}
