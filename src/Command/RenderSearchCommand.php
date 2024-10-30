<?php

namespace App\Command;

use App\Database;
use App\Parser;
use App\Prepare;
use App\Render\Image;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:render-search')]
class RenderSearchCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('dumpIndexPath', mode: InputOption::VALUE_REQUIRED)
            ->addOption('dumpSearchPath', mode: InputOption::VALUE_REQUIRED)
            ->addOption('traceKey', mode: InputOption::VALUE_OPTIONAL)
            ->addOption('outputImagePath', mode: InputOption::VALUE_OPTIONAL)
            ->addOption('outputInfoPath', mode: InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dumpIndexPath = $input->getOption('dumpIndexPath');
        if (!is_file($dumpIndexPath)) {
            throw new \RuntimeException('Invalid option value "--dumpIndexPath"');
        }
        $tree = Parser\Index::parse($dumpIndexPath);

        $dumpSearchPath = $input->getOption('dumpSearchPath');
        if (!is_file($dumpSearchPath)) {
            throw new \RuntimeException('Invalid option value "--dumpSearchPath"');
        }
        $search = Parser\Search::parse($dumpSearchPath);
        $traces = $search->traces;

        $traceKey = $input->getOption('traceKey');
        $trace = null;
        if ($traceKey === null) {
            $trace = reset($traces);
        } else {
            foreach ($traces as $t) {
                if ($t->key === $traceKey) {
                    $trace = $t;
                    break;
                }
            }
        }

        if (empty($trace)) {
            throw new \RuntimeException('No trace found');
        }

        $outputImagePath = $input->getOption('outputImagePath');
        if (!empty($outputImagePath)) {
            $this->renderImage($tree, $search, $trace, $outputImagePath);
        }

        $outputInfoPath = $input->getOption('outputInfoPath');
        if (!empty($outputInfoPath)) {
            $this->renderInfo($search, $outputInfoPath);
        }

        return Command::SUCCESS;
    }

    private function renderImage(Database\Index $tree, Database\Search $search, Database\Trace $trace, string $filePath): void
    {
        $imageResource = fopen($filePath, 'w+');
        $prepare = (new Prepare\Search($tree, $search, $trace));

        (new Image())->draw($prepare, $imageResource);
    }

    private function renderInfo(Database\Search $search, string $filePath): void
    {
        $output = new BufferedOutput();

        $output->writeln(trim($search->query));
        $output->writeln(trim($search->result));

        file_put_contents($filePath, $output->fetch());
    }
}
