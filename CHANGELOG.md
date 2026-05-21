# Changelog

## Unreleased

- Fixed the local quality gate so `composer check` runs tests, Psalm, and PHPCS without requiring a coverage driver or mutating files.
- Cleaned PHPUnit, Psalm, and PHPCS issues in the Prophecy runtime package.
- Ignored the local Swoole research checkout and documented future long-running runtime adapters as explicit integrations.
- Made `LoggerServiceProvider` always bind `LoggerInterface`, falling back to `NullLogger` when Monolog is unavailable.
