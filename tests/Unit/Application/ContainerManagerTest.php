<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Application;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Prophecy\Exceptions\ProphecyException;
use PHPUnit\Framework\TestCase;

final class ContainerManagerTest extends TestCase
{
    public function testReturnsProvidedContainer(): void
    {
        $container = new ArgonContainer();

        self::assertSame($container, (new ContainerManager($container))->getContainer());
    }

    public function testBuildsConfiguredContainerWithCwdAndBasePath(): void
    {
        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->setConfigurator(static function (ArgonContainer $container): void {
            $container->getParameters()->set('appName', 'prophecy-test');
        });

        $container = $manager->getContainer();
        $parameters = $container->getParameters();

        self::assertSame(__DIR__, $parameters->get('cwd'));
        self::assertSame(__DIR__, $parameters->get('basePath'));
        self::assertSame('prophecy-test', $parameters->get('appName'));
    }

    public function testConfiguratorMayProvideBasePath(): void
    {
        $basePath = dirname(__DIR__, 3);
        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->setConfigurator(static function (ArgonContainer $container) use ($basePath): void {
            $container->getParameters()->set('basePath', $basePath);
        });

        self::assertSame($basePath, $manager->getContainer()->getParameters()->get('basePath'));
    }

    public function testBuiltContainerIsCached(): void
    {
        $configurationCount = 0;
        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->setConfigurator(static function () use (&$configurationCount): void {
            $configurationCount++;
        });

        $container = $manager->getContainer();

        self::assertSame($container, $manager->getContainer());
        self::assertSame(1, $configurationCount);
    }

    public function testConfiguratorCannotMutateCwd(): void
    {
        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->setConfigurator(static function (ArgonContainer $container): void {
            $container->getParameters()->set('cwd', '/tmp/elsewhere');
        });

        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('Service configuration attempted to mutate cwd parameter.');

        $manager->getContainer();
    }

    public function testBuildRequiresCwd(): void
    {
        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('Current working directory must be provided to ContainerManager.');

        (new ContainerManager())->getContainer();
    }

    public function testLoadsCompiledContainerFromConfiguredFile(): void
    {
        $className = 'CompiledContainerForProphecyTest';
        $filePath = $this->writeCompiledContainerFile($className);

        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->configureCompilation($filePath, $className);

        self::assertInstanceOf(ArgonContainer::class, $manager->getContainer());
    }

    public function testLoadsNamespacedCompiledContainerFromConfiguredFile(): void
    {
        $namespace = 'Tests\\Unit\\Application\\Fixtures';
        $className = 'NamespacedCompiledContainerForProphecyTest';
        $filePath = $this->writeCompiledContainerFile($className, $namespace);

        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->configureCompilation($filePath, $className, $namespace);

        $container = $manager->getContainer();

        self::assertInstanceOf(ArgonContainer::class, $container);
        self::assertSame($namespace . '\\' . $className, $container::class);
    }

    public function testConfiguredCompilationWritesContainerWhenCompiledFileIsMissing(): void
    {
        $className = 'CompiledWritePathForProphecyTest';
        $filePath = $this->fixturePath($className . '.php');

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->configureCompilation($filePath, $className);

        self::assertFileDoesNotExist($filePath);

        $container = $manager->getContainer();

        self::assertInstanceOf(ArgonContainer::class, $container);
        self::assertFileExists($filePath);
    }

    public function testCompiledFileMustDeclareConfiguredClass(): void
    {
        $filePath = $this->fixturePath('MissingCompiledContainer.php');
        file_put_contents($filePath, "<?php\n\ndeclare(strict_types=1);\n");

        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->configureCompilation($filePath, 'MissingCompiledContainer');

        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage("Compiled container class 'MissingCompiledContainer' not found.");

        $manager->getContainer();
    }

    public function testCompiledClassMustExtendArgonContainer(): void
    {
        $className = 'InvalidCompiledContainerForProphecyTest';
        $filePath = $this->fixturePath($className . '.php');
        file_put_contents($filePath, "<?php\n\ndeclare(strict_types=1);\n\nfinal class {$className}\n{\n}\n");

        $manager = new ContainerManager();
        $manager->setCwd(__DIR__);
        $manager->configureCompilation($filePath, $className);

        $this->expectException(ProphecyException::class);
        $this->expectExceptionMessage('Compiled container must extend ArgonContainer.');

        $manager->getContainer();
    }

    private function writeCompiledContainerFile(string $className, string $namespace = ''): string
    {
        $filePath = $this->fixturePath($className . '.php');
        $namespaceDeclaration = $namespace !== ''
            ? "namespace {$namespace};\n\n"
            : '';

        file_put_contents(
            $filePath,
            "<?php\n\n" .
            "declare(strict_types=1);\n\n" .
            $namespaceDeclaration .
            "use Maduser\\Argon\\Container\\ArgonContainer;\n\n" .
            "final class {$className} extends ArgonContainer\n{\n}\n"
        );

        return $filePath;
    }

    private function fixturePath(string $filename): string
    {
        $directory = dirname(__DIR__, 3) . '/.phpunit/test-fixtures';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory . '/' . $filename;
    }
}
