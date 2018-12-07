<?php
/**
 * Created by PhpStorm.
 * User: romain.gautier
 * Date: 06/12/2018
 * Time: 14:31
 */

namespace Tests\AppBundle\Security;

use PHPUnit\Framework\TestCase;
use AppBundle\Security\GithubUserProvider;
use AppBundle\Entity\User;

class GithubUserProviderTest extends TestCase
{

    private $client;
    private $serializer;
    private $streamedResponse;
    private $response;

    public function setUp()
    {
        $this->client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->serializer = $this
            ->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->streamedResponse = $this
            ->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

        $this->response = $this
            ->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();
    }

    public function testLoadUserByUsernameReturningAUser()
    {
        $client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $serializer = $this
            ->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $response = $this
            ->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();
        $client->method('get')->willReturn($response);

        $streamedResponse = $this
            ->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamedResponse);

        $userData = ['login' => 'a login', 'name' => 'user name', 'email' => 'adress@mail.com', 'avatar_url' => 'url to the avatar', 'html_url' => 'url to profile'];
        $serializer
            ->expects($this->once()) // Nous nous attendons à ce que la méthode deserialize soit appelée une fois
            ->method('deserialize')
            ->willReturn([]);

        $this->expectException('LogicException');
        $githubUserProvider = new GithubUserProvider($client, $serializer);
        $user = $githubUserProvider->loadUserByUsername('an-access-token');

        $expectedUser = new User($userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']);

        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('AppBundle\Entity\User', get_class($user));
    }

    public function tearDown()
    {
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;
    }
}