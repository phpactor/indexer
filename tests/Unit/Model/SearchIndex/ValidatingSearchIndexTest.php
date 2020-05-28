<?php

namespace Phpactor\Indexer\Tests\Unit\Model\SearchIndex;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\Indexer\Adapter\Php\InMemory\InMemorySearchIndex;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\Indexer\Model\SearchIndex\ValidatingSearchIndex;
use Phpactor\Indexer\Tests\IntegrationTestCase;

class ValidatingSearchIndexTest extends IntegrationTestCase
{
    /**
     * @var InMemorySearchIndex
     */
    private $innerSearchIndex;
    /**
     * @var InMemoryIndex
     */
    private $index;

    /**
     * @var ValidatingSearchIndex
     */
    private $searchIndex;

    protected function setUp(): void
    {
        $this->innerSearchIndex = new InMemorySearchIndex();
        $this->index = new InMemoryIndex();
        $this->searchIndex = new ValidatingSearchIndex(
            $this->innerSearchIndex,
            $this->index
        );
    }

    public function testWillRemoveResultIfNotExistIndex(): void
    {
        $record = ClassRecord::fromName('Foobar');
        $this->innerSearchIndex->write($record);

        self::assertSearchCount(0, $this->searchIndex->search('Foobar'));
        self::assertFalse($this->innerSearchIndex->has($record));
    }

    public function testYieldsRecordsWithoutAPath(): void
    {
        $record = MemberRecord::fromIdentifier('method#foo');
        $this->index->write($record);
        $this->innerSearchIndex->write($record);

        self::assertSearchCount(1, $this->searchIndex->search('method#foo'));
    }

    public function testRemovesFromIndexIfFileDoesNotExist(): void
    {
        $record = ClassRecord::fromName('Foobar')
            ->setFilePath($this->workspace()->path('nope.php'));

        $this->index->write($record);
        $this->innerSearchIndex->write($record);

        self::assertSearchCount(0, $this->searchIndex->search('Foobar'));
        self::assertFalse($this->innerSearchIndex->has($record));
    }

    public function testYieldsSearchResultIfFileExists(): void
    {
        $this->workspace()->put('yep.php', 'foo');
        $record = ClassRecord::fromName('Foobar')
            ->setFilePath($this->workspace()->path('yep.php'));

        $this->index->write($record);
        $this->innerSearchIndex->write($record);

        self::assertSearchCount(1, $this->searchIndex->search('Foobar'));
        self::assertTrue($this->innerSearchIndex->has($record));
    }

    private static function assertSearchCount(int $int, Generator $generator): void
    {
        self::assertEquals($int, count(iterator_to_array($generator)));
    }
}
