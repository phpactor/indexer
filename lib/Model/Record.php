<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Exception\CorruptedRecord;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\TextDocument\ByteOffset;

abstract class Record
{
    /**
     * @var int
     */
    private $lastModified;

    /**
     * @var string
     */
    private $fqn;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var int
     */
    private $start;

    public function __construct(
        FullyQualifiedName $fqn
    ) {
        // this object is serialized, do not store the object representation as
        // it adds around 100b to the size of each indexed class
        $this->fqn = $fqn->__toString();
    }

    /**
     * Return string which is unique to this record (used for namespacing),
     * e.g. "class".
     */
    abstract public function recordType(): string;

    public function setFilePath(string $path): self
    {
        $this->filePath = $path;
        return $this;
    }

    public function setStart(ByteOffset $start): self
    {
        $this->start = $start->toInt();
        return $this;
    }

    public function setLastModified(int $mtime): self
    {
        $this->lastModified = $mtime;
        return $this;
    }

    public function fqn(): FullyQualifiedName
    {
        return FullyQualifiedName::fromString($this->fqn);
    }

    public function lastModified(): int
    {
        return $this->lastModified;
    }

    public function filePath(): ?string
    {
        return $this->filePath;
    }

    public function start(): ByteOffset
    {
        return ByteOffset::fromInt($this->start);
    }

    public function __wakeup(): void
    {
        if (null === $this->fqn) {
            throw new CorruptedRecord(sprintf(
                'Record was corrupted'
            ));
        }
    }
}
