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
    name: "app:generate-keywords",
    description: "Generating keywords for search advertisements.",
)]
class GenerateKeywordsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'directory',
                InputArgument::REQUIRED,
                "save directory"
            )
            ->addArgument(
                "filename",
                InputArgument::REQUIRED,
                "source file"
            )
            ->addOption(
                "display",
                "d",
                InputOption::VALUE_NONE,
                "output the result to the console",
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument("filename");
        $inputText = file_get_contents($filename);

        if ("" === $inputText) {
            return Command::SUCCESS;
        }

        $groups = explode("\n", trim($inputText));
        $phrases = PhraseGenerator::generate($groups);
        $processedPhrases = PhraseProcessor::process($phrases);

        $display = $input->getOption("display");

        if ($display) {
            $table = new Table($output);
            $headers = array_keys($processedPhrases[0]->toArray());
            array_unshift($headers, "#");
            $table->setHeaders($headers);
            foreach ($processedPhrases as $index => $processedPhrase) {
                $row = $processedPhrase->toArray();
                array_unshift($row, $index + 1);
                $table->addRow($row);
            }
            $table->render();
        }

        $directory = $input->getArgument('directory');
        $filesystem = new Filesystem();

        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory);
        }

        $filename = 'phrases_' . date('Y-m-d_H-i-s') . '.txt';
        $filepath = $directory . '/' . $filename;
        $data = array_map(function ($phrase) {
            return (string) $phrase;
        }, $processedPhrases);
        $content = join("\n", $data);
        $filesystem->dumpFile($filepath, $content);

        $message = "The results file has been successfully saved: $filepath";
        $output->writeln([$message]);

        return Command::SUCCESS;
    }
}
