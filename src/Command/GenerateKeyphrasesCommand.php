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
            return Command::SUCCESS;
        }

        $groups = explode("\n", trim($inputText));
        $phrases = PhraseGenerator::generate($groups);
        list($processedPhrases, $totalMinusWordsHash) = PhraseProcessor::process($phrases);

        $display = $input->getOption("display");
        if ($display) {
            $table = new Table($output);

            $maxWordsNumber = 0;
            foreach ($processedPhrases as $index => $processedPhrase) {
                $row = $processedPhrase->toArray($totalMinusWordsHash);
                $maxWordsNumber = max($maxWordsNumber, count($row));
                array_unshift($row, $index + 1);
                $table->addRow($row);
            }

            $headers = range(1, $maxWordsNumber);
            array_unshift($headers, "#");
            $table->setHeaders($headers);

            $table->render();
        }

        $directory = $input->getArgument("directory");
        $filesystem = new Filesystem();
        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory);
        }

        $filename = "phrases_" . date("Y-m-d_H-i-s") . ".csv";
        $filepath = $directory . "/" . $filename;
        $data = array_map(fn($phrase) => (string) $phrase, $processedPhrases);
        $content = join("\n", $data);
        $filesystem->dumpFile($filepath, $content);

        $message = "The results file has been successfully saved: $filepath";
        $output->writeln([$message]);

        return Command::SUCCESS;
    }
}
