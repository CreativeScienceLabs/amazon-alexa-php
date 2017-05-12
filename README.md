# Amazon Alexa PHP Library

This is an unofficial PHP library for handling Alexa custom Skills (and flash briefings
in the future).

## Usage

When you configure an endpoint in Amazon Developer portal for your custom skill, you
will receive launch requests or intent requests by Alexa. You can handle them by 
initializing a new Endpoint:

```php
use CreativeScience\Alexa\Skill\Endpoint;
// Configure your own app id
$alexaSkillAppId = 'amzn1.ask.skill.xxxx...';
$alexa = new Endpoint( $alexaSkillAppId );
```

Then you should create and add your handlers (in a similar way to the nodejs sdk):

```php
function helloWorldIntentHandler ( $endpoint )
{
    // your logic goes here
    
    // Prepare and send your answer
    header('Content-type: application/json');
    echo $endpoint->response()->speak('Hello World!');
}

$endpoint->addHandler('HelloWorldIntent', 'helloWorldintentHandler' );
```

Assuming you registered in the Amazon Skill Builder the **HelloWorldIntent**, when 
Alexa asks to your endpoint for that intent, your function will be called and you
will instruct Alexa to say "Hello World!" back to the user.

The add handler receives any callable function or method, so you can also use object
or class methods to handle the different events:

```php
class MyObject 
{
    public function myIntentHandler( $endpoint )
    {
        // Logic goes here
    }
}

$object = new MyObject;
$endpoint->addHandler( 'YourIntentName', array( $object, 'myIntentHandler' ) );
```

### Handling other events

In addition of handling IntentRequest you can handle LaunchRequest and 
SessionEndedRequest. All you need to do is to define the handler with the proper
event name:

```php
$endpoint->addHandler( Endpoint::LAUNCH_REQUEST , 'myLaunchRequestHandler' );
$endpoint->addHanlder( Endpoint::SESSION_ENDED_REQUEST, 'mySessionEndedRequestHanlder' );
```

In top of that, you can add a catch-all for all the requests without associated
handler:

```php
$endpoint->addHandler( Endpoint::UNHANDLED_REQUEST, 'myCatchAllRequestHandler' );
```

### Response

You can send multiple content when sending a response. Consider the following example:
```php
echo $endpoint->response()
    ->speak('Hello World!')
    ->card('Card Title', 'This is the card content')
    ->listen('Do you want to say something else?');
```

This would render a card in the Alexa user app, reproduce a SSML speech and a reprompt
question.

## TODO
* Implement flash brief API
* Increase tests code coverage