<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @dataProvider getPublicUrls
     */
    public function testPublicUrls($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider getSecureUrls
     */
    public function testSecureUrls($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect());

        $this->assertEquals(
            'http://localhost/login',
            $client->getResponse()->getTargetUrl()
        );
    }

    public function getPublicUrls()
    {
        return array(
            array('/'),
            array('/explore/'),
        );
    }

    public function getSecureUrls()
    {
        return array(
            array('/upload'),
        );
    }
}
