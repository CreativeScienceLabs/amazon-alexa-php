<?php
/**
 * Created by PhpStorm.
 * User: javie_000
 * Date: 11/05/2017
 * Time: 10:54 AM
 */

namespace CreativeScience\Alexa\Skill;

/**
 * Class Handler
 * @package CreativeScience\Alexa\Skill
 */
class Endpoint
{
    protected $appId;

    protected $handlers = [];

    /**
     * @var Request The handled request
     */
    protected $request;
    /**
     * @var The response for the handled request
     */
    protected $response;

    const LAUNCH_REQUEST = 'LaunchRequest';
    const SESSION_ENDED_REQUEST = 'SessionEndedRequest';
    const UNHANDLED_REQUEST = 'UnhandledRequest';

    /**
     * Endpoint constructor.
     * @param $appId
     * @throws InvalidAppIdException If
     */
    public function __construct( $appId )
    {
        if ( empty($appId) ) {
            throw new \InvalidArgumentException('The provided alexa App ID is empty');
        }
        $this->appId = $appId;


    }

    public function addHandler( $eventName, $callback )
    {
        if ( ! is_callable( $callback ) )
        {
            throw new \InvalidArgumentException('Invalid callback for Alexa Endpoint');
        }

        $this->handlers[ $eventName ] = $callback;
    }

    public function execute( $rawData )
    {
        if (empty($rawData))
        {
            $rawData = file_get_contents('php://input');
        }

        $this->request = Request::create($rawData);

        if ($this->appId != $this->request->appId)
        {
            throw new \Exception("The configured app id {$this->appId} doesn't match with the request id {$this->request->appId}");
        }

        $eventName = '';
        switch ( $this->request->type )
        {
            case Request::LAUNCH_REQUEST:
                $eventName = Request::LAUNCH_REQUEST;
                break;
            case Request::SESSION_ENDED_REQUEST:
                $eventName = Request::SESSION_ENDED_REQUEST;
                break;
            case Request::INTENT_REQUEST:
                $eventName = $this->request->intent['name'];
                break;
        }
        $this->call( $eventName );

    }

    public function call( $eventName )
    {
        if ( !isset( $this->handlers[$eventName]) )
        {
            $eventName = self::UNHANDLED_REQUEST;
        }

        if ( !isset( $this->handlers[$eventName]) ) {
            throw new \RuntimeException('No \'Unhandled\' callback defined for request: ' . $eventName);
        }
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function setAppId( $appId )
    {
        $this->appId = $appId;
    }


    /**
     * @return Response
     */
    public function response() {

        if ( null == $this->response )
        {
            $this->response = new Response;
            $this->response->sessionAttributes($this->request->attributes);
        }

        return $this->response;
    }

}