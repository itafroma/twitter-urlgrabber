<?php

/**
 * @file
 * Example of how to access Twitter's direct messages using its streaming API.
 */

// Boilerplate: load the Composer autolaoder
require 'vendor/autoload.php';

// Boilerplate:
// - Require Guzzle's HTTP library, OAuth plugin, and streaming
// - Require Symfony's YAML configuration parser
use Guzzle\Http\Client;
use Guzzle\Plugin\Oauth\OauthPlugin;
use Guzzle\Stream\PhpStreamRequestFactory;
use Symfony\Component\Yaml\Parser;

// Parse the configuration file. The configuration file is specified as the
// first command line argument. An example configuration file can be found
// at example.yml
$yaml = new Parser();
$config = $yaml->parse(file_get_contents($argv[1]));


// Create a Guzzle client pointing to Twitter's streaming user API.
// You will not have to change this.
$client = new Client('https://userstream.twitter.com/1.1');

// Attach the OAuth credentials to the HTTP client.
$oauth = new OauthPlugin([
    'consumer_key' => $config['api_key'],
    'consumer_secret' => $config['api_secret'],
    'token' => $config['access_token'],
    'token_secret' => $config['access_token_secret'],
]);
$client->addSubscriber($oauth);

// Set up the request to access user.json.
// user.json is the only endpoint available for user streaming.
$request = $client->get('user.json');

// Connect the stream to the request.
$stream = (new PhpStreamRequestFactory())->fromRequest($request);

// Parse the stream as it comes in.
//
// Twitter sends data in chunks, so this algorithm will read those chunks as
// they come in and assemble the messages along line breaks.
//
// Credit to Michael Dowling for figuring this out:
//   http://mtdowling.com/blog/2012/01/27/chunked-encoding-in-php-with-guzzle/
$line = '';
while (!$stream->feof()) {
    $line .= $stream->readLine(512);

    while (strstr($line, "\r\n") !== false) {
        list($message, $line) = explode("\r\n", $line, 2);
        // Once we have a complete message, decode it from JSON into a regular
        // PHP array.
        $data = json_decode($message);

        // user.json includes everything streamed to the user. We just want
        // direct messages, so ignore anything that doesn't have the
        // direct_message property.
        if (empty($data->direct_message)) {
            continue;
        }

        $dm = $data->direct_message;

        // Only allow authorized senders to submit URLs.
        $sender = $dm->sender_screen_name;
        if (!in_array($sender, $config['senders'])) {
            continue;
        }

        // Open any URLS found in the DM in the default browser.
        // "open" is an OS X-specific command; an alternative would have to
        // be found for Windows or Linux.
        $urls = $dm->entities->urls;
        foreach ($urls as $url) {
            system('open ' . $url->expanded_url);
        }
    }
}
