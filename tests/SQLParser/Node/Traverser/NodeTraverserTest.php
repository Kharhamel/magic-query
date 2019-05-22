<?php

namespace SQLParser\Node\Traverser;

use PHPSQLParser\PHPSQLParser;
use PHPUnit\Framework\TestCase;
use SQLParser\Query\StatementFactory;

class NodeTraverserTest extends TestCase
{
    public function testStandardSelect()
    {
        $magicJoinDetector = new DetectMagicJoinSelectVisitor();
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($magicJoinDetector);

        $parser = new PHPSQLParser();

        $sql = 'SELECT * FROM users';
        $parsed = $parser->parse($sql);
        $select = StatementFactory::toObject($parsed);
        $nodeTraverser->walk($select);
        $this->assertCount(0, $magicJoinDetector->getMagicJoinSelects());
        $magicJoinDetector->resetVisitor();

        $sql = 'SELECT * FROM magicjoin(mytable)';
        $parsed = $parser->parse($sql);
        $select = StatementFactory::toObject($parsed);
        $nodeTraverser->walk($select);
        $this->assertCount(1, $magicJoinDetector->getMagicJoinSelects());
        $this->assertEquals('mytable', $magicJoinDetector->getMagicJoinSelects()[0]->getMainTable());
        $magicJoinDetector->resetVisitor();

        $sql = 'SELECT SUM(users.age) FROM users WHERE name LIKE :name AND company LIKE :company';
        $parsed = $parser->parse($sql);
        $select = StatementFactory::toObject($parsed);
        $nodeTraverser->walk($select);
        $this->assertCount(0, $magicJoinDetector->getMagicJoinSelects());
        $magicJoinDetector->resetVisitor();

        $sql = 'SELECT * FROM users WHERE status in :status';
        $parsed = $parser->parse($sql);
        $select = StatementFactory::toObject($parsed);
        $nodeTraverser->walk($select);
        $this->assertCount(0, $magicJoinDetector->getMagicJoinSelects());
        $magicJoinDetector->resetVisitor();

        // Triggers a const node
        $sql = 'SELECT id+1 FROM users';
        $parsed = $parser->parse($sql);
        $select = StatementFactory::toObject($parsed);
        $nodeTraverser->walk($select);
        $this->assertCount(0, $magicJoinDetector->getMagicJoinSelects());
        $magicJoinDetector->resetVisitor();
    }
}
