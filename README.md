Foursquare to KML
=================

A command line tool written in PHP to export all your Foursquare checkins to a KML file.

Handcrafted in Vienna by [Florian Eckerstorfer](http://florianeckerstorfer.com).

Installation
------------

First download the latest version of **4sqtokml** from Github.

Next you need to run the `config` command and enter your **Client ID** and **Client Secret** (which can be obtained from [Foursquares developer site](https://developer.foursquare.com)).

```bash
php 4sqtokml.phar config
```

Installation using Git and Composer
-----------------------------------

You need to clone this Git repository and [Composer](http://getcomposer.org).

```bash
git clone https://github.com/florianeckerstorfer/4sqtokml.git
cd 4sqtokml/
curl -s https://getcomposer.org/installer | php
php composer.phar update
```


Usage
-----

*Note:* If you installed **4sqtokml** using Git and Composer you need to use `4sqtokml.php` instead of `4sqtokml.phar`.

### Export all checkins

The filename of the resulting KML is `checkins.kml`

```bash
php 4sqtokml.phar export
```

### Specify the filename

```bash
php 4sqtokml.phar export --output=my-checkins.kml
```

or shorter

```bash
php 4sqtokml.phar export -o my-checkins.kml
```

### Specify the number of checkins to fetch

```bash
php 4sqtokml.phar export --limit=250
```

or shorter

```bash
php 4sqtokml.phar export -l 250
```
