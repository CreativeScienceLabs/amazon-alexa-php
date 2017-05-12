<?php

namespace CreativeScience\Alexa\Skill;

/**
 * Class Request
 *
 * Encapsulates the JSON request coming from Alexa
 *
 * Readonly properties:
 * @property string $appId The request App Id
 * @property array|null $user The request App Id
 * @property array|null $device The device data if present
 * @property string $apiEndpoint The api endpoint for doing device data requests
 * @property bool $isNewSession True if new session, false otherwise
 * @property string $sessionId The session ID for the current request
 * @property string $type The request type: LaunchRequest, SessionEndedRequest or IntentRequest
 * @property string $locale The request locale
 * @property string $requestId The ID for the current request
 * @property array|null $intent The intent data if the request type is IntentRequest
 * @property array|null $attributes The session attributes associative array
 *
 * @package CreativeScience\Alexa\Skill
 */
class Request
{
    // Maximum tolerance allowed by Amazon
    const MAXIMUM_TIMESTAMP_TOLERANCE  = 150;

    const LAUNCH_REQUEST = 'LaunchRequest';
    const SESSION_ENDED_REQUEST = 'SessionEndedRequest';
    const INTENT_REQUEST = 'IntentRequest';

    const DIALOG_STARTED = 'STARTED';
    const DIALOG_IN_PROGRESS = 'IN_PROGRESS';
    const DIALOG_COMPLETED = 'COMPLETED';

    /**
     * @var int Configured Timestamp tolerance
     */
    protected static $timestampTolerance = SELF::MAXIMUM_TIMESTAMP_TOLERANCE;

    /**
     * @var array The request data, see class properties for details
     */
    protected $data = [];

    /**
     * Validates the raw data and creates a Request object
     *
     * @param $rawData The string data of the incoming alexa request
     * @throws \UnexpectedValueException on json or timestamp validation error
     * @return \CreativeScience\Alexa\Skill\Request
     */
    public static function create( $rawData ) {
        $data = \json_decode( $rawData, true );

        if (JSON_ERROR_NONE !== \json_last_error())
        {
            throw new \UnexpectedValueException( 'Non-valid JSON received when parsing Alexa request' );
        }

        try {
            $timestamp = new \DateTime(@$data['request']['timestamp']);
            $now = new \DateTime;

            if ( abs($now->getTimestamp() - $timestamp->getTimestamp()) > self::getTimestampTolerance() )
            {
                throw new \UnexpectedValueException();
            }

        } catch ( \Exception $e ) {
            throw new \UnexpectedValueException( 'Timestamp validation failed for alexa request', $e->getCode(), $e );
        }

        $request = new Request( $data );

        return $request;
    }

    /**
     * Request constructor.
     *
     * @param $data json array data from an Alexa request
     */
    protected function __construct( $data )
    {

        if ( isset( $data['context'] ) )
        {
            $this->data['appId']    = $data['context']['System']['application']['applicationId'];
            $this->data['user']     = $data['context']['System']['user'];

            $this->data['device']   = $data['context']['System']['device'];
        }

        if ( isset( $data['session']))
        {
            $this->data['appId']       = @$this->data['appId'] ?: $data['session']['application']['applicationId'];
            $this->data['user']        = @$this->data['user'] ?: $data['session']['user'];

            $this->data['sessionId']     = $data['session']['sessionId'];
            $this->data['isNewSession']  = $data['session']['new'];
            $this->data['attributes']    = $data['session']['attributes'];
        }

        $this->data['type'] = @$data['request']['type'];

        if ( self::INTENT_REQUEST == $this->data['type'] )
        {
            $this->data['intent'] = @$data['request']['intent'];
        }
        elseif ( self::SESSION_ENDED_REQUEST == $this->data['type'] )
        {
            $this->data['reason'] = $data['request']['reason'];
            if (isset($data['request']['error']))
            {
                $this->data['error'] = $data['request']['error'];
            }
        }

        $this->data['locale']       = $data['request']['locale'];
        $this->data['requestId']    = $data['request']['requestId'];
    }

    /**
     * Returns the configured timestamp tolerance when validating requests
     *
     * If a request timestamp differs with the current timestamp in an amount greater than the defined tolerance
     * the request is not created.
     *
     * @see Request::create()
     * @return int
     */
    public static function getTimestampTolerance()
    {
        return self::$timestampTolerance;
    }

    /**
     * @param int $tolerance The timestamp tolerance when validating requests
     * @see Request::getTimestampTolerance()
     */
    public static function setTimestampTolerance ( $tolerance )
    {

        $tolerance = intval( $tolerance );

        if ( $tolerance > self::MAXIMUM_TIMESTAMP_TOLERANCE || $tolerance < 0)
        {
            throw new \UnexpectedValueException('Alexa timestamp tolerance must be between 0 and ' . self::MAXIMUM_TIMESTAMP_TOLERANCE);
        }
        self::$timestampTolerance = $tolerance;
    }

    /**
     * Magic getter for protected Request data
     *
     * @param $name The property name
     * @return mixed|null
     */
    public function __get( $name )
    {
        if ( !isset($this->data[$name]) )
        {
            return null;
        }
        return $this->data[$name];

    }

    /**
     * Force readonly values for protected variables
     *
     * @param $name
     * @param $value
     */
    public function __set( $name, $value )
    {
        throw new \RuntimeException("Property $name is read only");
    }

}