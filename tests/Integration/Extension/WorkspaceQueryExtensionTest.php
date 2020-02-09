<?php

namespace Phpactor\WorkspaceQuery\Tests\Integration\Extension;

use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\WorkspaceQuery\Adapter\ReferenceFinder\IndexedImplementationFinder;
use Phpactor\WorkspaceQuery\Extension\WorkspaceQueryExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\WorkspaceQuery\Model\IndexBuilder;
use Phpactor\WorkspaceQuery\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

class WorkspaceQueryExtensionTest extends IntegrationTestCase
{
    public function testReturnsImplementationFinder()
    {
        $container = $this->createContainer();
        $finder = $container->get(ReferenceFinderExtension::SERVICE_IMPLEMENTATION_FINDER);
        self::assertInstanceOf(IndexedImplementationFinder::class, $finder);
    }

    public function testBuildIndex()
    {
        $container = $this->createContainer();
        $builder = $container->get(IndexBuilder::class);
        $this->assertInstanceOf(IndexBuilder::class, $builder);
        $index = $builder->build();
        foreach ($index as $record) {
        }
    }

    protected function setUp(): void
    {
        $this->workspace()->loadManifest(file_get_contents(__DIR__ . '/../Manifest/buildIndex.php.test'));
        $process = new Process([
            'composer', 'install'
        ], $this->workspace()->path('/'));
        $process->mustRun();
    }

    private function createContainer()
    {
        return PhpactorContainer::fromExtensions([
            ConsoleExtension::class,
            WorkspaceQueryExtension::class,
            FilePathResolverExtension::class,
            LoggingExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            ReferenceFinderExtension::class,
        ], [
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../..',
            FilePathResolverExtension::PARAM_PROJECT_ROOT => $this->workspace()->path(),
            LoggingExtension::PARAM_PATH => 'php://stderr',
            LoggingExtension::PARAM_ENABLED => true,
            LoggingExtension::PARAM_LEVEL => 'debug',
        ]);
    }
}