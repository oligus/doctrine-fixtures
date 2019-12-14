# Doctrine fixtures

Simple fixture loader for Doctrine.

[![Build Status](https://travis-ci.org/oligus/doctrine-fixtures.svg?branch=master)](https://travis-ci.org/oligus/doctrine-fixtures)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Codecov.io](https://codecov.io/gh/oligus/doctrine-fixtures/branch/master/graphs/badge.svg)](https://codecov.io/gh/oligus/doctrine-fixtures)
[![Maintainability](https://codeclimate.com/github/codeclimate/codeclimate/badges/gpa.svg)](https://codeclimate.com/github/oligus/doctrine-fixtures/maintainability)

Load XML fixtures into database using entity manager.

## Quick start

```bash
$ composer require oligus/doctrine-fixtures --dev
```

*Load all fixtures in a directory*
```php
$fixture = new FixtureManager($em, new XmlLoader('path/to/fixtures'));
$fixture->loadAll();
```

*Load a single fixture file*
```php
$fixture = new FixtureManager($em, new XmlLoader());
$fixture->loadFile('path/to/fixtures/data_table.xml');
```

### Availible Loaders

* Xml Loader
* Csv Loader

## Fixtures

#### Creating XML fixture from mysql database

```bash
$ mysqldump -h localhost -u username --password=password --xml -t database data_table --where="id='1'"
```



