#!/usr/bin/env php
<?php
require __DIR__.'/../vendor/autoload.php';

use App\Models\Order;
use App\Services\CSVStreamWriter;
use App\Services\FileStreamReader;
use App\Services\HTTPStreamReader;
use App\Services\JSONLStreamWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('Symfony Order Processor')
    ->addArgument('input', InputArgument::OPTIONAL, 'The URL or local path to the input file (*.jsonl)', 'orders.jsonl')
    ->addArgument('output', InputArgument::OPTIONAL, 'The path to the output file (*.csv, *.jsonl)', 'out.csv')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $inputPath = $input->getArgument('input');
        $inputStream = match (true) {
            FileStreamReader::supports($inputPath) => new FileStreamReader($inputPath),
            HTTPStreamReader::supports($inputPath) => new HTTPStreamReader($inputPath),
            default => null,
        };

        if ($inputStream === null) {
            $output->writeln('<error>Unsupported input: ' . $inputPath . '</error>');

            return Command::INVALID;
        }

        $outputPath = $input->getArgument('output');
        $outputStream = match (true) {
            CSVStreamWriter::supports($outputPath) => new CSVStreamWriter($outputPath),
            JSONLStreamWriter::supports($outputPath) => new JSONLStreamWriter($outputPath),
            default => null,
        };

        if ($outputStream === null) {
            $output->writeln('<error>Unsupported output file type: ' . $outputPath . '</error>');
            $inputStream->close();

            return Command::INVALID;
        }

        $progress = new ProgressBar($output);
        foreach ($progress->iterate($inputStream->lines()) as $line) {
            $data = json_decode($line);
            if ($data === null) {
                $progress->clear();
                $output->writeln('<error>Failed to parse JSON on line ' . ($progress->getProgress() + 1) . '</error>');
                $progress->display();

                continue;
            }

            $result = Order::fromArray($data)->process();
            if ($result['total_order_value'] <= 0) {
                continue;
            }

            $outputStream->writeLine($result);
        }

        $inputStream->close();
        $outputStream->close();
        $progress->finish();

        return Command::SUCCESS;
    })
    ->run();
