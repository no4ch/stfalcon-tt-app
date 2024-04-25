<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\RollingDice\RollingDiceTriggerProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:rolling-dice-trigger',
    description: 'Command for triggering rolling dice'
)]
class RollingDiceTriggerCommand extends Command
{
    private RollingDiceTriggerProcessor $rollingDiceTriggerProcessor;

    /**
     * @param RollingDiceTriggerProcessor $rollingDiceTriggerProcessor
     */
    public function __construct(
        RollingDiceTriggerProcessor $rollingDiceTriggerProcessor
    ) {
        parent::__construct();

        $this->rollingDiceTriggerProcessor = $rollingDiceTriggerProcessor;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->rollingDiceTriggerProcessor->process();

        return Command::SUCCESS;
    }
}
