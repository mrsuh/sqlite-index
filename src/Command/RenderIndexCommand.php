<?php

namespace App\Command;

use App\Parser;
use App\Prepare;
use App\Render\Image;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:render-index')]
class RenderIndexCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('dumpIndexPath', mode: InputOption::VALUE_REQUIRED)
            ->addOption('outputImagePath', mode: InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dumpIndexPath = $input->getOption('dumpIndexPath');
        if (!is_file($dumpIndexPath)) {
            throw new \RuntimeException('Invalid option value "--dumpIndexPath"');
        }

        $tree = Parser\Index::parse($dumpIndexPath);

        $outputImagePath = $input->getOption('outputImagePath');
        $imageResource = fopen($outputImagePath, 'w+');
        $prepare = (new Prepare\Index($tree));
        (new Image())->draw($prepare, $imageResource);

        return Command::SUCCESS;
    }
}
