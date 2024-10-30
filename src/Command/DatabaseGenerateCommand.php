<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:database-generate')]
class DatabaseGenerateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('type', mode: InputOption::VALUE_REQUIRED)
            ->addOption('count', mode: InputOption::VALUE_REQUIRED)
            ->addOption('databasePath', mode: InputOption::VALUE_REQUIRED)
            ->addOption('infoPath', mode: InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        self::generate(
            $input->getOption('type'),
            (int)$input->getOption('count'),
            $input->getOption('databasePath'),
            (string)$input->getOption('infoPath'),
        );

        return Command::SUCCESS;
    }

    function generate(string $type, int $max, string $filePath, string $infoPath): void
    {
        $step = min(100000, $max);

        if (is_file($filePath)) {
            unlink($filePath);
        }

        $db = new \SQLite3($filePath);

        $queries = [];
        switch ($type) {
            case 'index':
            case 'search-equal':
            case 'search-range':
            case 'search-greater-than':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 INT NOT NULL);');
                foreach (self::generateIntegerValues($max, $step) as $values) {
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, 'CREATE INDEX idx ON table_test (column1 ASC);');
                break;
            case 'index-order':
            case 'search-order':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 INT NOT NULL);');
                foreach (self::generateIntegerValues($max, $step) as $values) {
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, 'CREATE INDEX idx_asc ON table_test (column1 ASC);');
                $queries[] = self::sql($db, 'CREATE INDEX idx_desc ON table_test (column1 DESC);');
                break;
            case 'index-expression':
            case 'search-expression':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 TEXT NOT NULL);');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        $values .= sprintf("('%s'),", json_encode(['timestamp' => $value]));
                    }
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1) VALUES %s;', rtrim($values, ',')));
                }

                $queries[] = self::sql($db, "CREATE INDEX idx ON table_test (strftime('%Y-%m-%d %H:%M:%S', json_extract(column1, '$.timestamp'), 'unixepoch') ASC);");
                break;
            case 'index-unique':
            case 'search-unique':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 INT)');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        if ($value === 1 || $value === $max) {
                            $values .= sprintf('(%d),', $value);
                        } else {
                            $values .= '(NULL),';
                        }
                    }
                    $values = rtrim($values, ',');
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, "CREATE UNIQUE INDEX idx ON table_test (column1 ASC);");
                break;
            case 'index-partial':
            case 'search-partial':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 INT)');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        if ($value === 1 || $value === $max) {
                            $values .= sprintf('(%d),', $value);
                        } else {
                            $values .= '(NULL),';
                        }
                    }
                    $values = rtrim($values, ',');
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, "CREATE INDEX idx ON table_test (column1 ASC) WHERE column1 IS NOT NULL;");
                break;
            case 'index-complex':
            case 'search-complex-equal':
//            case 'search-complex-range':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 INT NOT NULL, column2 INT NOT NULL);');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        $values .= sprintf('(%d,%d),', $value, $value);
                    }
                    $values = rtrim($values, ',');
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1, column2) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, 'CREATE INDEX idx ON table_test (column1 ASC, column2 ASC);');
                break;
            case 'index-time':
            case 'index-vacuum':
            case 'index-reindex':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 INT NOT NULL);');
                $queries[] = self::sql($db, 'CREATE INDEX idx_before ON table_test (column1 ASC);');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        $values .= sprintf('(%d),', $value);
                    }
                    $values = rtrim($values, ',');
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, 'CREATE INDEX idx_after ON table_test (column1 ASC);');
                break;
            case 'index-text':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 text NOT NULL);');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        $values .= sprintf('(\'text-%d\'),', $value);
                    }
                    $values = rtrim($values, ',');
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, 'CREATE INDEX idx ON table_test (column1 ASC);');
                break;
            case 'index-real':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 REAL NOT NULL);');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        $values .= sprintf('(\'%d.14\'),', $value);
                    }
                    $values = rtrim($values, ',');
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, 'CREATE INDEX idx ON table_test (column1 ASC);');
                break;
            case 'index-integer-text':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 INT NOT NULL, column2 TEXT NOT NULL);');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        $values .= sprintf('(%d,\'text-%d\'),', $value, $value);
                    }
                    $values = rtrim($values, ',');
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1, column2) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, 'CREATE INDEX idx ON table_test (column1 ASC, column2 ASC);');
                break;
            case 'search-complex-cardinality':
                $queries[] = self::sql($db, 'CREATE TABLE table_test (column1 INT NOT NULL, column2 INT NOT NULL);');
                for ($i = 1; $i <= $max; $i += $step) {
                    $values = '';
                    for ($value = $i; $value < $i + $step; $value++) {
                        $values .= sprintf('(%d,%d),', $value, $value % 2 === 0 ? 2 : 1);
                    }
                    $values = rtrim($values, ',');
                    $queries[] = self::sql($db, sprintf('INSERT INTO table_test (column1, column2) VALUES %s;', $values));
                }
                $queries[] = self::sql($db, 'CREATE INDEX idx_column1_column2 ON table_test (column1 ASC, column2 ASC);');
                $queries[] = self::sql($db, 'CREATE INDEX idx_column2_column1 ON table_test (column2 ASC, column1 ASC);');
                break;
        }

        if (!empty($infoPath)) {
            file_put_contents(
                $infoPath,
                self::renderInfo($queries)
            );
        }
    }

    private static function sql(\SQLite3 $db, string $query): string
    {
        $db->exec($query);

        return $query;
    }

    private static function generateIntegerValues(int $max, int $step): \Generator
    {
        for ($i = 1; $i <= $max; $i += $step) {
            $values = '';
            for ($value = $i; $value < $i + $step; $value++) {
                $values .= sprintf('(%d),', $value);
            }
            yield rtrim($values, ',');
        }
    }

    private static function renderInfo(array $queries): string
    {
        $output = new BufferedOutput();

        $output->writeln('```SQL');
        $inserts = [];
        foreach ($queries as $query) {
            if (str_starts_with($query, 'INSERT')) {
                preg_match('/.*VALUES\s+(\([^\)]+\)\,\s*){3}/', $query, $matches1);
                preg_match('/(\([^\)]+\)\,{0,1}\s*){3}(\;){0,1}$/', $query, $matches2);

                if (!isset($matches1[0]) && !isset($matches2[0])) {
                    $output->writeln($query);
                    continue;
                }

                $inserts[] = [
                    $matches1[0],
                    $matches2[0]
                ];

                continue;
            }

            if (!empty($inserts)) {
                if (count($inserts) === 1) {
                    $output->writeln($inserts[0][0] . '...,' . $inserts[0][1]);
                } else {
                    $output->writeln($inserts[0][0] . '...,' . end($inserts)[1]);
                }
                $inserts = [];
            }

            $output->writeln($query);
        }
        $output->writeln('```');

        return $output->fetch();
    }
}
