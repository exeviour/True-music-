# README

[![Build Status](https://secure.travis-ci.org/owncloud/music.png)](http://travis-ci.org/owncloud/music)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/owncloud/music/badges/quality-score.png?s=ddb9090619b6bcf0bf381e87011322dd2514c884)](https://scrutinizer-ci.com/g/owncloud/music/)

## FLAC & AAC support in progress

Using Aurora.js

## Commands

	./occ music:scan USERNAME

This scans all not scanned music files of the user USERNAME and saves the extracted metadata into the music tables in the database. This is also done if you browse the music app web interface. There the scan is done in steps of 20 tracks and the current state is visible at the bottom of the interface.

	./occ music:scan --all

This scans music files for all users.

Both of the above commands can be combined with the `--debug` switch, which enables debug output and shows the memory usage of each scan step.

## Ampache

In the settings the URL you need form Ampache is listed and looks like this:

```
https://cloud.domain.org/index.php/apps/music/ampache/server/xml.server.php
```

This is the full path. Some clients append the last part (`server/xml.server.php`) automatically. If you have connection problems try the shorter version of the URL.

## L10n hints

Sometimes translatable strings aren't detected. Try to move the `translate` attribute
more to the beginning of the HTML element.

## Build appstore package

	git archive HEAD --format=zip --prefix=music/ > build/music.zip

## Run tests

PHP tests

	phpunit tests/php
	phpunit --coverage-html coverage-html tests/php

## 3rdparty libs

update JavaScript libraries

	cd js
	bower update
