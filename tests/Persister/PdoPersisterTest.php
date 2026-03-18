<?php

namespace Haigha\Tests\Persister;

use Haigha\Persister\PdoPersister;
use Haigha\TableRecord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test class for PdoPersister
 *
 * @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class PdoPersisterTest extends TestCase
{
    private PdoPersister $persister;
    private BufferedOutput $output;

    protected function setUp(): void
    {
        $pdo = $this->createStub(\PDO::class);
        $this->output = new BufferedOutput();
        $this->persister = new PdoPersister($pdo, $this->output, true);
    }

    public function testPersist(): void
    {
        $records = [
            $this->makeRecord('table1', ['a' => 'foo']),
            $this->makeRecord('table1', ['b' => 'bar']),
            $this->makeRecord('table1', ['b' => 'baz', 'a' => 'qux']),
            $this->makeRecord('table2', ['x' => 'y']),
        ];

        $this->persister->persist($records);
        $expected = "Will be executed: INSERT INTO `table1` (`a`, `b`) VALUES (foo, DEFAULT),\n".
            "(DEFAULT, bar),\n".
            "(qux, baz)\n".
            "Will be executed: INSERT INTO `table2` (`x`) VALUES (y)\n";

        $this->assertEquals($expected, $this->output->fetch());
    }

    public function testReset(): void
    {
        $records = [
            $this->makeRecord('table1', ['a' => 'foo']),
            $this->makeRecord('table1', ['b' => 'bar']),
            $this->makeRecord('table2', ['x' => 'y']),
        ];

        $this->persister->reset($records);
        $expected = "Will be executed: TRUNCATE `table1`\n".
            "Will be executed: TRUNCATE `table2`\n";

        $this->assertEquals($expected, $this->output->fetch());
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function makeRecord(string $table, array $fields): TableRecord
    {
        $record = new TableRecord($table);
        foreach ($fields as $field => $val) {
            $record->{$field} = $val;
        }

        return $record;
    }
}
