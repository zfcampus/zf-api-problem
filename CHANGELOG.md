# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.2.1 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.0 - 2016-07-06

### Added

- [#45](https://github.com/zfcampus/zf-api-problem/pull/45) adds support for PHP 7.
- [#44](https://github.com/zfcampus/zf-api-problem/pull/44) and
  [#45](https://github.com/zfcampus/zf-api-problem/pull/45) add support for
  version 3 components of Zend Framework.
- [#39](https://github.com/zfcampus/zf-api-problem/pull/39) adds the constant
  `ApiProblem::CONTENT_TYPE` for specifying the Content-Type of API Problem
  responses.

### Deprecated

- Nothing.

### Removed

- [#45](https://github.com/zfcampus/zf-api-problem/pull/45) removes support for
  PHP 5.6.
- [#45](https://github.com/zfcampus/zf-api-problem/pull/45) removes the
  `Module::getAutoloaderConfig()` implementation, as it was redundant in
  composer-based applications.

### Fixed

- [#44](https://github.com/zfcampus/zf-api-problem/pull/44) and
  [#45](https://github.com/zfcampus/zf-api-problem/pull/45) ensure that
  definition and attachment of the listener aggregates defined in the module
  will work with both v2 and v3 versions of zend-eventmanager.
- [#38](https://github.com/zfcampus/zf-api-problem/pull/38) fixes an issue
  whereby JSON encoding failed for included stack traces if they originated
  within a PHP stream.
