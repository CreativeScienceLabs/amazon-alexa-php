<?php
use PHPUnit\Framework\TestCase;
use CreativeScience\Alexa\Skill\Endpoint;
use CreativeScience\Alexa\Skill\Request;
use CreativeScience\Alexa\Skill\Response;

class EndpointTest extends TestCase
{
    public function testHandlerIsCalled()
    {
        $handlerMock = $this->getMockBuilder(stdClass::class)
                            ->setMethods(['handler'])
                            ->getMock();

        $endpoint = new Endpoint('xxxxxx');
        $handlerMock->expects($this->once())->method('handler')->with($this->equalTo($endpoint));

        $endpoint->addHandler('event', array($handlerMock, 'handler'));
        $endpoint->call('event');
    }

    public function testObserverIsCalled()
    {
        $observer = $this->getMockBuilder(stdClass::class)
                         ->setMethods(['handler'])
                         ->getMock();

        $endpoint = new Endpoint('xxxxxx');
        $observer->expects($this->once())->method('handler')->with($this->equalTo($endpoint));

        $endpoint->addObserver($observer);
        $endpoint->call('handler');
    }

    public function testResponseIsCreatedWithSessionAttributes()
    {
        $requestMock = $this->getMockBuilder( Request::class);

        $requestMock->attributes = [
            'a' => 1,
            'b' => 2,
        ];

        $endpoint = new Endpoint('xxxxxx');
        $endpoint->request = $requestMock;

        $response = $endpoint->response();
        $this->assertInstanceOf(Response::class, $response );

        $this->assertEquals($requestMock->attributes, $response->sessionAttributes);
    }
}