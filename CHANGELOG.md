# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.12] - 2026-04-24

### Changed
- Bump PHP minimum requirement to 7.1, add void return types, expand CI matrix to PHP 7.1–8.5

### Fixed
- Fix PHP 8+ warning: trying to access array offset on false in do...while loops (PR #17)
- Fix CI audit step compatibility across PHP 7.1–8.5 matrix
- Fix PHPUnit 7 mock builder API usage in tests
- Resolve CI failures: braces in StyleCI config, `__clone` void return, broadened PHPUnit constraint

## [0.2.11] - 2017-05-11

### Changed
- Optimize PHPDoc blocks (second pass)

## [0.2.10] - 2017-05-08

### Changed
- Optimize PHPDoc blocks
- Refactor internal methods: rename `__`-prefixed private methods to `_`-prefixed (no breaking changes, second pass)

## [0.2.9] - 2017-05-08

### Changed
- Fix PHPDoc blocks
- Refactor internal methods: rename `__`-prefixed private methods to `_`-prefixed (no breaking changes)

## [0.2.8] - 2016-07-24

### Added
- Expand test coverage with additional test cases
- Use more descriptive return values in hook methods

## [0.2.7] - 2016-07-20

### Changed
- Use `assertSame` instead of `assertEquals` in tests
- Fix Travis CI build configuration
- Update README style

## [0.2.6] - 2015-10-16

### Changed
- Move `php-coveralls` configuration into `.travis.yml`

## [0.2.5] - 2015-10-01

### Changed
- Split test files into separate units

### Removed
- Remove `class_exists('Hooks')` wrapper

## [0.2.4] - 2015-08-20

### Added
- Add `.styleci.yml` configuration

### Fixed
- Fix PHP warnings detected by PHPStorm static analysis

### Changed
- Switch version tracking to git tags

## [0.2.3] - 2015-08-13

### Changed
- Update `composer.json` metadata

## [0.2.2] - 2015-07-24

### Added
- Add `.gitattributes` file
- Add `.editorconfig` file

### Changed
- Switch to PSR-4 autoloading

## [0.2.1] - 2015-07-06

### Added
- Use `spl_object_hash()` (PHP ≥ 5.2.0) for object deduplication
- Add Scrutinizer CI integration
- Add more unit tests

## [0.2.0] - 2015-01-29

### Added
- Port unit tests from [mistic100/PHP-Hooks](https://github.com/mistic100/PHP-Hooks)
- Port customizations from Piwigo
- Add PHPUnit as a `require-dev` dependency
- Use singleton pattern
- Add Travis CI configuration

### Changed
- Prepend private methods with `__` prefix
- Use `! empty()` instead of `isset()` (after upstream change)
- Make `has_shortcode()` recursive (works for nested shortcodes)

### Removed
- Remove `accepted_args` option — all arguments are always transmitted
- Remove debug code (use Xdebug instead)

### Fixed
- Fix `has_shortcode()`
- Fix logical operator style
- Fix license identifier (SPDX)

[Unreleased]: https://github.com/voku/php-hooks/compare/0.2.11...HEAD
[0.2.11]: https://github.com/voku/php-hooks/compare/0.2.10...0.2.11
[0.2.10]: https://github.com/voku/php-hooks/compare/0.2.9...0.2.10
[0.2.9]: https://github.com/voku/php-hooks/compare/0.2.8...0.2.9
[0.2.8]: https://github.com/voku/php-hooks/compare/0.2.7...0.2.8
[0.2.7]: https://github.com/voku/php-hooks/compare/0.2.6...0.2.7
[0.2.6]: https://github.com/voku/php-hooks/compare/0.2.5...0.2.6
[0.2.5]: https://github.com/voku/php-hooks/compare/0.2.4...0.2.5
[0.2.4]: https://github.com/voku/php-hooks/compare/0.2.3...0.2.4
[0.2.3]: https://github.com/voku/php-hooks/compare/0.2.2...0.2.3
[0.2.2]: https://github.com/voku/php-hooks/compare/0.2.1...0.2.2
[0.2.1]: https://github.com/voku/php-hooks/compare/0.2...0.2.1
[0.2.0]: https://github.com/voku/php-hooks/releases/tag/0.2
