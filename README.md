Foursquare to KML
=================

4sqtokml is a tool that takes your checkins on Foursquare and generates a KML file. KML files can be imported in apps like Google Earth or Fog of World.

Handcrafted in Vienna by [Florian Eckerstorfer](http://florianeckerstorfer.com).

Installation
------------

1. Clone this project
    `git clone git@github.com:florianeckerstorfer/4sqtokml.git 4sqtokml`
2. Download the depencies (using [Composer](http://getcomposer.org))
    `composer update`
3. Copy `config/foursquare.yml.dist` to `config/foursquare.yml`
4. Open `config/foursquare.yml` and insert your **Client ID**, **Client Secret** and **Callback URI**
5. Configure your web server (an example configuration for Nginx can be found under `data/nginx.conf.sample`)
6. Done

Dependencies
------------

* My fork of [foursquare-php](https://github.com/florianeckerstorfer/foursquare-php) by [Chris Woodford](https://github.com/chriswoodford/foursquare-php)
* [Symfony Yaml Component](https://github.com/symfony/Yaml)
* [Symfony HTTP Foundation Component](https://github.com/symfony/httpfoundation)
* [php-kml](https://code.google.com/p/php-kml/)
* [Twig](https://github.com/fabpot/Twig)
* [Silex](https://github.com/fabpot/Silex/)
