# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.0.0 - TBD

First release under the `ik-oss` namespace.

### Added

- Nothing.

### Changed

- Renamed the package to `ik-oss/mezzio-navigation`.
- Raised the PHP requirement to 8.5; the package no longer supports PHP 7.
- Upgraded to laminas-navigation 2.23, laminas-stdlib 3.21, mezzio-helpers 5.20
  and mezzio-router 4.2.
- Replaced `Interop\Container\ContainerInterface` with
  `Psr\Container\ContainerInterface` throughout.
- Added `declare(strict_types=1)`, native property types and native parameter
  and return types across `src/` and `test/`.
- `MezzioPage::getHref()` now throws `Laminas\Navigation\Exception\DomainException`
  when no `UrlHelper` has been set, instead of failing with a PHP error.
- Replaced Travis CI with GitHub Actions and migrated the test suite from
  PHPUnit 7 (Prophecy) to PHPUnit 13.
- Moved the `extra.zf` config-provider key to `extra.laminas`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2017-10-11

Initial release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
