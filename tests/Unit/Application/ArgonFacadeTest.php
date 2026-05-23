<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Maduser\Argon\Prophecy\Argon;
use Maduser\Argon\Prophecy\Exceptions\ProphecyException;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ArgonFacadeTest extends TestCase
{
    #[\Override]
    protected function tearDown(): void
    {
        $this->clearCompileEnv();
        Argon::reset();

        parent::tearDown();
    }

    public function testBootWithCompileEnabledRequiresCompileFileAndClass(): void
    {
        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage(
            'Container compilation is enabled but compile configuration is incomplete.'
        );

        Argon::boot(static function (): void {
            // no-op
        }, true);
    }

    #[RunInSeparateProcess]
    public function testFailedCompileConfigurationDoesNotMarkApplicationBooted(): void
    {
        try {
            Argon::boot(static function (): void {
                // no-op
            }, true);
        } catch (RuntimeException) {
            // expected
        }

        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('Application not booted yet.');

        Argon::check();
    }

    #[RunInSeparateProcess]
    public function testBootWithValidCompileConfigurationMarksApplicationBooted(): void
    {
        $_ENV['APP_COMPILE_FILE_NAME'] = __DIR__ . '/../../.phpunit/CompiledContainer.php';
        $_ENV['APP_COMPILE_CLASS_NAME'] = 'CompiledContainer';

        Argon::boot(static function (): void {
            // no-op
        }, true);

        $this->assertSame(Argon::check(), Argon::check());
    }

    #[RunInSeparateProcess]
    public function testBootUsesTrueEnvironmentCompileFlag(): void
    {
        $_ENV['APP_COMPILE_CONTAINER'] = 'true';

        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('APP_COMPILE_FILE_NAME');

        Argon::boot(static function (): void {
            // no-op
        });
    }

    #[RunInSeparateProcess]
    public function testBootTreatsFalseEnvironmentCompileFlagAsDisabled(): void
    {
        $_ENV['APP_COMPILE_CONTAINER'] = 'false';

        Argon::boot(static function (): void {
            // no-op
        });

        $this->assertSame(Argon::check(), Argon::check());
    }

    #[RunInSeparateProcess]
    public function testBootTreatsEmptyEnvironmentCompileFlagAsDisabled(): void
    {
        $_ENV['APP_COMPILE_CONTAINER'] = '  ';

        Argon::boot(static function (): void {
            // no-op
        });

        $this->assertSame(Argon::check(), Argon::check());
    }

    #[RunInSeparateProcess]
    public function testBootRejectsInvalidEnvironmentCompileFlag(): void
    {
        $_ENV['APP_COMPILE_CONTAINER'] = 'definitely';

        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('Invalid container compilation flag value "definitely".');

        Argon::boot(static function (): void {
            // no-op
        });
    }

    #[RunInSeparateProcess]
    public function testBootRejectsInvalidCompileArgument(): void
    {
        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('Invalid container compilation flag value "sometimes".');

        Argon::boot(static function (): void {
            // no-op
        }, 'sometimes');
    }

    #[RunInSeparateProcess]
    public function testResetAllowsFacadeToBootAgain(): void
    {
        Argon::boot(static function (): void {
            // no-op
        });
        $firstApplication = Argon::check();

        Argon::reset();

        Argon::boot(static function (): void {
            // no-op
        });

        $this->assertNotSame($firstApplication, Argon::check());
    }

    private function clearCompileEnv(): void
    {
        unset(
            $_ENV['APP_COMPILE_CONTAINER'],
            $_ENV['APP_COMPILE_FILE_NAME'],
            $_ENV['APP_COMPILE_CLASS_NAME'],
            $_ENV['APP_COMPILE_CLASS_NAMESPACE']
        );
    }
}
