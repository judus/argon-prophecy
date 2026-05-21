# Changelog

## Unreleased

- Fixed the local quality gate so `composer check` runs tests, Psalm, and PHPCS without requiring a coverage driver or mutating files.
- Cleaned PHPUnit, Psalm, and PHPCS issues in the Prophecy runtime package.
- Ignored the local Swoole research checkout and documented future long-running runtime adapters as explicit integrations.
- Made `LoggerServiceProvider` always bind `LoggerInterface`, falling back to `NullLogger` when Monolog is unavailable.
- Limited bootstrap shutdown handling to fatal PHP errors.
- Added upfront validation for facade container compilation configuration.
- Made `Argon::reset()` tear down bootstrap error and exception handler side effects.
- Switched suite package repositories from local path references to public GitHub VCS repositories for CI.
- Tracked the empty integration test suite directory so fresh CI checkouts match local PHPUnit configuration.
