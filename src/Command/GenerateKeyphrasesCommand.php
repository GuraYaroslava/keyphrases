<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Filesystem\Filesystem;
use App\PhraseGenerator;
use App\PhraseProcessor;


#[AsCommand(
    name: "app:generate-keyphrases",
    description: "Generating keyphrases for search advertisements.",
)]
class GenerateKeyphrasesCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument("directory", InputArgument::REQUIRED, "save directory")
            ->addArgument("filename", InputArgument::REQUIRED, "source file")
            ->addOption("display", "d", InputOption::VALUE_NONE, "display in the terminal",)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument("filename");
        $inputText = "";
        try {
            $inputText = file_get_contents($filename);
        } catch (\Exception $e) {
            $output->writeln([$e->getMessage()]);
        }

        if ("" === $inputText) {
            $message = "Source data is empty!";
            $output->writeln([$message]);

            return Command::SUCCESS;
        }

        $groups = explode("\n", trim($inputText));
        $phrases = PhraseGenerator::generate($groups);
        $phraseProcessor = new PhraseProcessor($phrases);
        $phraseProcessor->run();

        if ($phraseProcessor->isEmptyProcessResult()) {
            $message = "Generation result is empty!";
            $output->writeln([$message]);

            return Command::SUCCESS;
        }

        $display = $input->getOption("display");
        if ($display) {
            $table = new Table($output);
            $table->addRows($phraseProcessor->getTableRows());
            $table->setHeaders($phraseProcessor->getTableHeader());
            $table->render();
        }

        $directory = $input->getArgument("directory");
        $filesystem = new Filesystem();
        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory);
        }
        $filename = "phrases_" . date("Y-m-d_H-i-s") . ".csv";
        $filepath = $directory . "/" . $filename;
        $content = $phraseProcessor->getCSV();
        $filesystem->dumpFile($filepath, $content);

        $message = "The results file has been successfully saved: $filepath";
        $output->writeln([$message]);

        return Command::SUCCESS;
    }
}
