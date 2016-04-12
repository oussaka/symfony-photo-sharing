<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImageControllerTest extends WebTestCase
{
    public function testExplore()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/explore/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('div.thumbnail')->count()
        );
    }

    public function testImageDetail()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/photos/31');

        $this->assertCount(1, $crawler->selectButton('favorite'));

        $this->assertGreaterThan(0, $crawler->filter('#tags li')->count());
    }

    public function testLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('_submit')->form(array(
            '_username'  => "test",
            '_password'  => "test",
        ));
        $client->submit($form);

        $this->assertTrue($client->getResponse()->isRedirect());

        //$crawler = $client->followRedirect();

        $crawler = $client->request('GET', '/upload');
        $this->assertTrue($client->getResponse()->isSuccessful());

    }

}
