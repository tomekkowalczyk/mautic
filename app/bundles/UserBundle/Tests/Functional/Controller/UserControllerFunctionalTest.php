<?php

declare(strict_types=1);

namespace Mautic\UserBundle\Tests\Functional\Controller;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerFunctionalTest extends MauticMysqlTestCase
{
    public function testEditGetPage()
    {
        $this->client->request('GET', '/s/users/edit/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testRedirectNonExistingUser()
    {
        $crawler = $this->client->request('GET', '/s/users/edit/00000');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Users', $crawler->filter('h1')->text());
        $this->assertStringContainsString('User not found with', $crawler->filter('#flashes')->text());
    }

    public function testEditActionFormSubmissionValid()
    {
        $crawler                = $this->client->request('GET', '/s/users/edit/1');
        $buttonCrawlerNode      = $crawler->selectButton('Save & Close');
        $form                   = $buttonCrawlerNode->form();
        $form['user[username]'] = 'test';
        $this->client->submit($form);

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('has been updated!', $response->getContent());
    }

    public function testEditActionFormSubmissionInvalid()
    {
        $crawler = $this->client->request('GET', '/s/users/edit/1');

        $form = $crawler->selectButton('Save')->form([
            'user[firstName]'               => '',
            'user[lastName]'                => '',
            'user[email]'                   => 'invalid-email',
            'user[plainPassword][password]' => '',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('The email entered is invalid.', $this->client->getResponse()->getContent());
    }
}
