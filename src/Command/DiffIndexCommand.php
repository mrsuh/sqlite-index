<?php

namespace App\Command;

use App\Parser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:diff-index')]
class DiffIndexCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('dumpIndexPath', mode: InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED)
            ->addOption('outputDiffPath', mode: InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bufferedOutput = new BufferedOutput();
        $table = new Table($bufferedOutput);
        $table->setHeaders([
            '',
            'Total pages',
            'Total cells',
        ]);
        foreach($input->getOption('dumpIndexPath') as $index => $dumpIndexPath) {
            $tree = Parser\Index::parse($dumpIndexPath);
            $table->addRow([
                $index === 0 ? 'Before' : 'After',
                $tree->getTotalPageCount(),
                $tree->getTotalCellCount(),
            ]);
        }
        $table->render();

        file_put_contents(
            $input->getOption('outputDiffPath'),
            $bufferedOutput->fetch()
        );

        return Command::SUCCESS;
    }
}
