<?php
/**
 * Created by PhpStorm.
 * User: javie_000
 * Date: 11/05/2017
 * Time: 10:56 AM
 */

namespace CreativeScience\Alexa\Skill;


class Response
{
    const VERSION = '1.0';

    protected $response;
    public $sessionAttributes = [];
    protected $directives = [];

    public function __construct()
    {
        $this->response = [
            'shouldEndSession' => true,
        ];
    }

    public function speak($speechOutput ) {
        $this->response['outputSpeech'] = $this->createSSMLspeech( $speechOutput );
        return $this;
    }

    public function listen( $repromptSpeech ) {
        $this->response['reprompt']['outputSpeech'] = $this->createSSMLspeech( $repromptSpeech );
        $this->response['shouldEndSession'] = false;
        return $this;
    }

    public function card($title, $content = '', $image = [] ) {
        $card = [
            'title'     => $title,
        ];

        if ( $image && ( @$image['small_image_url'] || @$image['large_image_url'] )) {
            $card['type'] = 'Standard';
            $card['text'] = $content;

            if (@$image['small_image_url']) {
                $card['image']['smallImageUrl'] = $image['small_image_url'];
            }

            if (@$image['large_image_url']) {
                $card['image']['largeImageUrl'] = $image['large_image_url'];
            }

        } else {
            $card['type']       = 'Simple';
            $card['content']    = $content;
        }

        $this->response['card'] = $card;

        return $this;
    }

    public function linkAccountCard( ) {
        $this->response['card'] = [ 'type' => 'LinkAccount' ];
        return $this;
    }

    public function shouldEndSession($endSession = true ) {
        $this->response['shouldEndSession'] = $endSession;
        return $this;
    }

    public function directives( $directives ) {
        if (!empty($directives)) {
            $this->directives = $directives;
        }
        return $this;
    }

    public function sessionAttributes ( $attributes ) {
        if (!empty($attributes)) {
            $this->sessionAttributes = $attributes;
        }
        return $this;
    }

    public function response( $response ) {
        $this->response = $response;
        return $this;
    }

    public function createSSMLspeech($message ) {
        return [
            'type'  => 'SSML',
            'ssml'  => "<speak>$message</speak>",
        ];
    }

    public function toArray() {

        $data = [
            'version'           => self::VERSION,
            'sessionAttributes' => $this->sessionAttributes,
        ];

        if ( !empty($this->response) ) {
            $data['response'] = $this->response;
        }

        if ( !empty($this->directives) ) {
            $data['directives'] = $this->directives;
        }

        return $data;
    }

    public function render() {
        return json_encode($this->toArray());
    }

    public function __toString()
    {
        return strval($this->render());
    }
}