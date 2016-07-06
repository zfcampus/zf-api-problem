# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.2.0 - TBD

### Added

- [#45](https://github.com/zfcampus/zf-api-problem/pull/45) adds support for PHP 7.
- [#45](https://github.com/zfcampus/zf-api-problem/pull/45) adds support for
  version 3 components of Zend Framework.

### Deprecated

- Nothing.

### Removed

- [#45](https://github.com/zfcampus/zf-api-problem/pull/45) removes support for
  PHP 5.6.
- [#45](https://github.com/zfcampus/zf-api-problem/pull/45) removes the
  `Module::getAutoloaderConfig()` implementation, as it was redundant in
  composer-based applications.

### Fixed

- [#45](https://github.com/zfcampus/zf-api-problem/pull/45) ensures that
  definition and attachment of the listener aggregates defined in the module
  will work with both v2 and v3 versions of zend-eventmanager.
- [#38](https://github.com/zfcampus/zf-api-problem/pull/38) fixes an issue
  whereby JSON encoding failed for included stack traces if they originated
  within a PHP stream.
