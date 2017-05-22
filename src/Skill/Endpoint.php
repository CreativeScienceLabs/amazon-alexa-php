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
    public $appId;

    protected $handlers = [];

    protected $observer = null;

    /**
     * @var Request The handled request
     */
    public $request = null;
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

    public function addObserver( $observer )
    {
        if ( !is_object ( $observer ))
        {
            throw new \InvalidArgumentException('Observer is not an object');
        }

        $this->observer = $observer;
    }

    public function execute( Request $request = null )
    {

        if (null === $request)
        {
            $this->request = $this->request ?: Request::create();
        }
        else
        {
            $this->request = $request;
        }

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
        return $this->call( $eventName );

    }

    public function call( $eventName )
    {
        $callback = null;

        if ( null !== $this->observer ) {

            $possibleMethodNames = [];

            if (0 === strpos($eventName, 'AMAZON.'))
            {
                $amazonMethodName =  substr($eventName, 7);
                $possibleMethodNames = [
                    'amazon' . $amazonMethodName,
                    'AMAZON' . $amazonMethodName,
                    lcfirst($amazonMethodName),
                    $amazonMethodName,
                ];
            }

            $possibleMethodNames[] =  lcfirst($eventName);
            $possibleMethodNames[] = $eventName;

            foreach( $possibleMethodNames as $methodName )
            {
                if ( method_exists($this->observer, $methodName ) && is_callable( [$this->observer, $methodName] ))
                {
                    $callback = [ $this->observer, $methodName ];
                    break;
                }
            }

            // Check for magic method __call implementation
            if ( null === $callback && is_callable( $this->observer, $eventName ))
            {
                $callback = [ $this->observer, $eventName ];
            }

        }
        // No observer or no callback found in observer, trying with regular handlers.
        if ( null === $callback ) {
            if ( isset( $this->handlers[$eventName]) )
            {
                $callback = $this->handlers[$eventName];
            }
            elseif ( isset( $this->handlers[self::UNHANDLED_REQUEST]) )
            {
                $callback = $this->handlers[self::UNHANDLED_REQUEST];
            }
            else
            {
                throw new \RuntimeException('No \'Unhandled\' callback defined for request: ' . $eventName);
            }
        }

        return call_user_func( $callback, $this);
    }

    public function setRequest(Request $request = null)
    {
        $this->request = isNull($request) ? Request::create() : $request;
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