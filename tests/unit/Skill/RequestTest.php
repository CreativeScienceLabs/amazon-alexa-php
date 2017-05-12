<?php
use PHPUnit\Framework\TestCase;
use CreativeScience\Alexa\Skill\Request;

class RequestTest extends TestCase
{

    private $loadedFiles = [];

    /**
     * @dataProvider validRequestDataProvider
     */
    public function testCanBeCreatedFromValidData( $data )
    {
        $this->assertInstanceOf(
            Request::class,
            Request::create( $data )
        );
    }

    public function testCannotBeCreatedFromInvalidJSON()
    {
        $this->expectException(UnexpectedValueException::class);

        Request::create('invalid data');
    }

    public function testCannotBeCreatedFromOldTimestamp()
    {
        $this->expectException(UnexpectedValueException::class);
        Request::create($this->loadSampleFile('old-timestamp.json'));
    }

    public function testSetValidTimestampTolerance()
    {
        $oldValue = Request::getTimestampTolerance();

        Request::setTimestampTolerance(50);
        $this->assertEquals(50, Request::getTimestampTolerance());

        Request::setTimestampTolerance($oldValue);
        $this->assertEquals($oldValue, Request::getTimestampTolerance());
    }

    /**
     * @depends testCanBeCreatedFromValidData
     */
    public function testRequestValuesArePresent()
    {
        $request = Request::create($this->loadSampleFile('intent-request.json'));

        $this->assertEquals('SessionId.xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
            , $request->sessionId);

        $this->assertEquals(true, $request->isNewSession);
        $this->assertEquals('amzn1.ask.skill.xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
            , $request->appId);

        $this->assertEquals( 'amzn1.ask.account.XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
        , $request->user['userId']);

        $this->assertEquals('IntentRequest', $request->type);
        $this->assertEquals( 'en-US', $request->locale);
        $this->assertEquals('EdwRequestId.xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            $request->requestId);
        $this->assertNotEmpty($request->intent);

        $this->assertEquals(true, $request->attributes['sampleAttribute']);
    }

    /**
     * @depends testCanBeCreatedFromValidData
     */
    public function testReadOnlyProperties()
    {
        $this->expectException(RuntimeException::class);
        $request = Request::create($this->loadSampleFile('launch-request.json'));

        $request->appId = "XXXXXX";
    }


    public function testCannotSetInvalidTimestampTolerance()
    {
        $this->expectException(UnexpectedValueException::class);
        Request::setTimestampTolerance(2500);

    }

    public function validRequestDataProvider()
    {

        return [
            [  $this->loadSampleFile('launch-request.json') ],
            [  $this->loadSampleFile('intent-request.json') ],
        ];
    }

    private function loadSampleFile( $filename )
    {
        if (isset($this->loadedFiles[$filename]))
        {
            return $this->loadedFiles[$filename];
        }

        $date = date('Y-m-d\TH:i:s');

        $samplesFolder = __DIR__ . '/../../sample-data/';

        $data = file_get_contents($samplesFolder . $filename);
        $this->loadedFiles[$filename] = strtr( $data,['%%now%%' => $date]);

        return $this->loadedFiles[$filename];

    }


}