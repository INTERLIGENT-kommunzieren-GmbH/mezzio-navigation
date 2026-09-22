# mezzio-navigation

[![CI](https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation/badge.svg?branch=master)](https://coveralls.io/github/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation?branch=master)

`mezzio-navigation` provides page, middleware and factories for
navigations in a mezzio application.

This is the [INTERLIGENT](https://github.com/INTERLIGENT-kommunzieren-GmbH)
maintained fork of the unmaintained upstream project.

## Requirements

* PHP 8.5
* laminas-navigation 2.23+
* mezzio-helpers 5.20+ and mezzio-router 4.2+

## Installation

The package is not published on Packagist. Add the repository to your
`composer.json` and require it:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation.git"
        }
    ]
}
```

```bash
composer require ik-oss/mezzio-navigation
```

The package ships a `ConfigProvider`, which is registered automatically by
`laminas-component-installer`. Without it, add
`Mezzio\Navigation\ConfigProvider` to your application config manually.

## Development

```bash
composer install
composer test        # PHPUnit
composer cs-check    # laminas-coding-standard
composer cs-fix
composer check       # both
```

## Feature requests, problems and bugs

Please use the issue tracker:
https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation/issues
