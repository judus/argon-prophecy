# Changelog

## Unreleased

- Replaced stale `BootstrapErrorHandlerTest` TODO blocks with focused `cli-server` output coverage.
- Added coverage for `ContainerManager` compiled-container loading, compile writes, and container caching.
- Added coverage for `ErrorHandlerManager` fallback edge cases around request resolution and response emission.
- Added coverage for HTTP fallback paths when runtime error handling cannot produce a response.
- Defined direct `Application::reset()` as idempotent terminal teardown and guarded post-reset calls.
- Made bootstrap debug logging secret-safe by replacing raw container dumps with metadata counts.
- Added `ProphecyException` as the stable exception type for Prophecy runtime and configuration failures.
- Made container compilation flag parsing strict so invalid non-empty values fail during boot.
- Made explicit runtime error-handler resolution and registration failures fail during bootstrap instead of falling back silently.
- Aligned the CI PHP version matrix with maduser-argon across PHP 8.2, 8.3, 8.4, and experimental 8.5.
- Restored PHP 8.2 compatibility by avoiding typed class constant syntax.
- Tightened application handler resolution so invalid or ambiguous handler bindings fail during bootstrap.
- Expanded Prophecy runtime tests around handler resolution, container setup, lifecycle dispatch, error-manager fallbacks, and tag helpers.
- Tightened the public README around Prophecy as a bootstrap/runtime shell rather than an application framework.
- Linked framework-composition and application-integration examples from the README to wiki how-to pages.
- Fixed the local quality gate so `composer check` runs tests, Psalm, and PHPCS without requiring a coverage driver or mutating files.
- Cleaned PHPUnit, Psalm, and PHPCS issues in the Prophecy runtime package.
- Ignored the local Swoole research checkout and documented future long-running runtime adapters as explicit integrations.
- Made `LoggerServiceProvider` always bind `LoggerInterface`, falling back to `NullLogger` when Monolog is unavailable.
- Limited bootstrap shutdown handling to fatal PHP errors.
- Added upfront validation for facade container compilation configuration.
- Made `Argon::reset()` tear down bootstrap error and exception handler side effects.
- Switched suite package repositories from local path references to public GitHub VCS repositories for CI.
- Tracked the empty integration test suite directory so fresh CI checkouts match local PHPUnit configuration.
