# Twitter URL Grabber

This is an example project to demonstrate how to access and parse Twitter's
direct messages using both its REST and Streaming APIs.

## Installation

You'll need [Composer][1] to install the dependencies. Once you have Composer,
run:

```sh
composer install
```

## Usage

Because this is an example project, it has limited uses. However, both
`rest-example.php` and `streaming-example.php` are fully functional provided you
[create your own Twitter app][2] and add your API credentials to a configuration
file patterned off of `config-example.yml`.

Once your configuration is set, you can run the examples from the command line:

```sh
php rest-example.php config.yml
php streaming-example.php config.yml
```

Where `config.yml` is the path to your configuration file.

`rest-example.php` will parse the last twenty DMs for any links and open them in
the default browser. `streaming-example.php` will run indefinitely and open any
links in DMs that are received in the future. You can stop
`streaming-example.php` by sending a SIGINT signal (e.g., by pressing `CTRL-C`).

The URL opening relies on OS X's `open` command.

## Copyright and license

This project is copyright © 2014 Mark Trapp. It is licensed under the MIT
license: a copy of the license can be found in the `LICENSE` file. All other
rights reserved.

## Acknowledgements

* [Michael Dowling][3] for the streaming API algorithm

[1]: https://getcomposer.org "Composer website"
[2]: https://apps.twitter.com "Twitter Application Management"
[3]: http://mtdowling.com "Michael Dowling's website"
