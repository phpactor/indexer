<?php

namespace Phpactor\WorkspaceQuery\Tests\Integration;

use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\WorkspaceQuery\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\WorkspaceQuery\Adapter\Php\InMemory\InMemoryRepository;
use Phpactor\WorkspaceQuery\Model\Index;
use Phpactor\WorkspaceQuery\Adapter\Worse\WorseIndexBuilder;
use Phpactor\WorkspaceQuery\Model\IndexBuilder;
use Phpactor\WorkspaceQuery\Tests\IntegrationTestCase;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StubSourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Psr\Log\NullLogger;

abstract class InMemoryTestCase extends IntegrationTestCase
{
    protected function createBuilder(Index $index): IndexBuilder
    {
        $indexBuilder = new WorseIndexBuilder(
            $index,
            new SimpleFilesystem($this->workspace()->path('/project')),
            ReflectorBuilder::create()->addLocator(
                new StubSourceLocator(
                    ReflectorBuilder::create()->build(),
                    $this->workspace()->path('/'),
                    $this->workspace()->path('/')
                )
            )->build(),
            new NullLogger()
        );
        return $indexBuilder;
    }

    protected function createIndex(): Index
    {
        $repository = new InMemoryRepository();
        return new InMemoryIndex($repository);
    }

    protected function createReflector(): Reflector
    {
        return ReflectorBuilder::create()->addLocator(
            new StubSourceLocator(
                ReflectorBuilder::create()->build(),
                $this->workspace()->path('/'),
                $this->workspace()->path('/')
            )
        )->build();
    }
}