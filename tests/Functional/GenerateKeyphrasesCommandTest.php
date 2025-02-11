<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;

class GenerateKeyphrasesCommandTest extends KernelTestCase
{
    private $inputFile;
    private $outputDir;
    private $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->inputFile = sys_get_temp_dir() . "/input.txt";
        $this->outputDir = sys_get_temp_dir();
        $content = "
            Honda, Honda CRF, Honda CRF-450X
            Владивосток, Приморский край -Владивосток
            продажа, покупка, цена, с пробегом";

        $this->filesystem->dumpFile($this->inputFile, $content);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove([$this->inputFile]);
    }

    public function testCommandSuccess(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find("app:generate-keyphrases");
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            "input" => $this->inputFile,
            "output" => $this->outputDir,
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $excepted = "The results file has been successfully saved";
        $this->assertStringContainsString($excepted, $output);

        $filename = $this->outputDir . "/phrases_" . date("Y-m-d_H-i-s") . ".csv";
        $this->assertFileExists($filename);

        $exceptedContent = <<<EOF
Honda;Владивосток;продажа
Honda;Владивосток;покупка
Honda;Владивосток;цена
Honda;Владивосток;+с;пробегом
Honda;Приморский;край;продажа;-Владивосток
Honda;Приморский;край;покупка;-Владивосток
Honda;Приморский;край;цена;-Владивосток
Honda;Приморский;край;+с;пробегом;-Владивосток
Honda;CRF;Владивосток;продажа
Honda;CRF;Владивосток;покупка
Honda;CRF;Владивосток;цена
Honda;CRF;Владивосток;+с;пробегом
Honda;CRF;Приморский;край;продажа;-Владивосток
Honda;CRF;Приморский;край;покупка;-Владивосток
Honda;CRF;Приморский;край;цена;-Владивосток
Honda;CRF;Приморский;край;+с;пробегом;-Владивосток
Honda;CRF;450X;Владивосток;продажа
Honda;CRF;450X;Владивосток;покупка
Honda;CRF;450X;Владивосток;цена
Honda;CRF;450X;Владивосток;+с;пробегом
Honda;CRF;450X;Приморский;край;продажа;-Владивосток
Honda;CRF;450X;Приморский;край;покупка;-Владивосток
Honda;CRF;450X;Приморский;край;цена;-Владивосток
Honda;CRF;450X;Приморский;край;+с;пробегом;-Владивосток
EOF;
        $actualContent = file_get_contents($filename);
        $this->assertEquals($exceptedContent, $actualContent);

        $this->filesystem->remove([$filename]);
    }

    public function testCommandInputFileNotFound(): void
    {
        $this->filesystem->remove($this->inputFile);

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find("app:generate-keyphrases");
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            "input" => $this->inputFile,
            "output" => $this->outputDir,
        ]);;

        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("Source data is empty!", $output);

        $filename = $this->outputDir . "/phrases_" . date("Y-m-d_H-i-s") . ".csv";
        $this->assertFileDoesNotExist($filename);
    }
}
