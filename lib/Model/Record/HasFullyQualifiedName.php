<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\TextDocument\ByteOffset;

interface HasFullyQualifiedName
{
    public function fqn(): FullyQualifiedName;
}