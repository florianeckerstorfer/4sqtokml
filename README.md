Foursquare to KML
=================

A command line tool written in PHP to export all your Foursquare checkins to a KML file.

Handcrafted in Vienna by [Florian Eckerstorfer](http://florianeckerstorfer.com).

Installation
------------

You need to clone this Git repository and [Composer](http://getcomposer.org).

```bash
git clone https://github.com/florianeckerstorfer/4sqtokml.git
cd 4sqtokml/
curl -s https://getcomposer.org/installer | php
php composer.phar update
```

Usage
-----

### Export all checkins

The filename of the resulting KML is `checkins.kml`

```bash
php 4sqtokml.php export
```

### Specify the filename

```bash
php 4sqtokml.php export --output=my-checkins.kml
```

or shorter

```bash
php 4sqtokml.php export -o my-checkins.kml
```

### Specify the number of checkins to fetch

```bash
php 4sqtokml.php export --limit=250
```

or shorter

```bash
php 4sqtokml.php export -l 250
```
