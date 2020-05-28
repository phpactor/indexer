<?php

namespace Phpactor\Indexer\Adapter\Php;

use Generator;
use Phpactor\Indexer\Model\Matcher;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\RecordFactory;
use Phpactor\Indexer\Model\SearchIndex;
use function Safe\file_get_contents;
use function Safe\file_put_contents;

class FileSearchIndex implements SearchIndex
{
    /**
     * Flush to the filesystem after BATCH_SIZE updates
     */
    private const BATCH_SIZE = 10000;

    private const DELIMITER = "\t";

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var array<array{string,string}>
     */
    private $subjects = [];

    /**
     * @var string
     */
    private $path;

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var int
     */
    private $counter = 0;

    public function __construct(string $path, Matcher $matcher)
    {
        $this->path = $path;
        $this->matcher = $matcher;
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $query): Generator
    {
        $this->open();

        foreach ($this->subjects as [ $recordType, $identifier ]) {
            if (false === $this->matcher->match($identifier, $query)) {
                continue;
            }

            yield RecordFactory::create($recordType, $identifier);
        }
    }

    public function remove(Record $record): void
    {
        if (!isset($this->subjects[$this->recordKey($record)])) {
            return;
        }

        unset($this->subjects[$this->recordKey($record)]);
    }

    public function write(Record $record): void
    {
        $this->open();
        $this->subjects[$this->recordKey($record)] = [$record->recordType(), $record->identifier()];

        if (++$this->counter % self::BATCH_SIZE === 0) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->open();

        file_put_contents($this->path, implode("\n", array_unique(array_map(function (array $parts) {
            return implode(self::DELIMITER, $parts);
        }, $this->subjects))));
    }

    private function open(): void
    {
        if ($this->initialized) {
            return;
        }

        if (!file_exists($this->path)) {
            return;
        }

        $this->subjects = array_filter(array_map(function (string $line) {
            $parts = explode(self::DELIMITER, $line);

            if (count($parts) !== 2) {
                return false;
            }

            return $parts;
        }, explode("\n", file_get_contents($this->path))));

        $this->initialized = true;
    }

    private function recordKey(Record $record): string
    {
        return $record->recordType().$record->identifier();
    }
}
