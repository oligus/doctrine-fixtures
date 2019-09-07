# Doctrine fixtures

Simple fixture loader for Doctrine.

[![Build Status](https://travis-ci.org/oligus/doctrine-fixtures.svg?branch=master)](https://travis-ci.org/oligus/doctrine-fixtures)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Codecov.io](https://codecov.io/gh/oligus/doctrine-fixtures/branch/master/graphs/badge.svg)](https://codecov.io/gh/oligus/doctrine-fixtures)
[![Maintainability](https://api.codeclimate.com/v1/badges/db45a4d29b976060fe8a/maintainability)](https://codeclimate.com/github/oligus/doctrine-fixtures/maintainability)

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
$fixture = new FixtureManager($em, new XmlLoader('path/to/fixtures'));
$fixture->loadFile('path/to/fixtures/data_table.xml');
```

## Fixtures

#### Creating XML fixture from mysql database

```bash
$ mysqldump -h localhost -u username --password=passord --xml -t database data_table --where="id='1'"
```



