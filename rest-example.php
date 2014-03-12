<?php

/**
 * @file
 * Example of how to access Twitter's direct messages using its REST API.
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

// Create a Guzzle client pointing to Twitter's REST API.
// You will not have to change this.
$client = new Client('https://api.twitter.com/1.1');

// Attach the OAuth credentials to the HTTP client.
$oauth = new OauthPlugin([
    'consumer_key' => $config['api_key'],
    'consumer_secret' => $config['api_secret'],
    'token' => $config['access_token'],
    'token_secret' => $config['access_token_secret'],
]);
$client->addSubscriber($oauth);

// This is used to limit the request to messages the script hasn't seen yet.
// If an ID is speciified as the second argument passed to this script, it will
// only request messages created after that message ID. Otherwise, it will
// return the last twenty messages.
$options = [];
if ($argv[2]) {
    $options['query'] = ['since' => $argv[2]];
}

// Assemble the request to access direct_messages.json.
// List of other possible endpoints: https://dev.twitter.com/docs/api/1.1
$request = $client->get('direct_messages.json', [], $options);

// Send the request to Twitter and get the response.
$response =  $request->send();

// Decode the response from JSON into a regular PHP array.
$messages = json_decode($response->getBody());

// Get the latest message ID. This is used to ensure we don't get any duplicates
// the next time this script is called.
$last_id = reset($messages)->id;

// Cycle through the messages returned.
foreach ($messages as $message) {
    // Only allow authorized senders to submit URLs.
    if (!in_array($message->sender->screen_name, $config['senders'])) {
        continue;
    }

    // Open any URLS found in the DM in the default browser.
    // "open" is an OS X-specific command; an alternative would have to
    // be found for Windows or Linux.
    $urls = $message->entities->urls;
    foreach ($urls as $url) {
        system('open ' . $url->expanded_url);
    }
}

// Print out the last id. You can then feed this back into another call of
// the script to get the next batch of direct messages without getting any
// duplicates.
print $last_id;
