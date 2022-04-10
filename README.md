# symfony-order-processor

A single command Symfony application built to process orders line-by-line and produce summary output.

## Table of Contents
* [Usage](#usage)
  * [Makefile/Docker](#makefile-docker)
    * [Requirements](#requirements)
    * [Commands](#commands)
  * [Manual](#manually)
    * [Requirements](#requirements-1)
    * [Commands](#commands-1)
* [Extending Functionality](#extending-functionality)
  * [Adding a new input source](#adding-a-new-input-source)
  * [Adding a new output format](#adding-a-new-output-format)

## Usage

The CLI accepts optional input and output arguments:
* **input**: the path to the input file (can be a URL or a path to a local file). *default:* `orders.jsonl`
* **output**: the path to the output file - the program will also determine the desired output format based on the file extension (`*.csv` and `*.jsonl` are supported). *default:* `out.csv`
### Makefile/Docker

For convenience, a `Makefile` is provided which allows for use within a containerized environment.

#### Requirements

* [Docker](https://www.docker.com/products/docker-desktop/)
* GNU make

#### Commands

For standard usage:
* `make run`: runs the CLI, installing dependencies if they haven't been already. You can optionally provide input/output arguments by setting the `INPUT` and `OUTPUT` environment variables. When used with local files, paths must be relative to the project root in order to ensure they are accessible from within the container - this applies to both input and output paths.
  * **Examples:**
    * `INPUT="my-orders.jsonl" OUTPUT="processed-orders.jsonl" make run`
    * `INPUT="https://example.com/orders.jsonl" OUTPUT="processed-orders.csv" make run`

Running tests:
* `make test`: runs all tests using PHPUnit.

Formatting code:
* `make fix`: uses [`php-cs-fixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to format the `src` directory.

### Manually

If you'd prefer to set up/run the CLI yourself you may do so.

#### Requirements

* PHP 8.1 or later
* Composer 2

#### Commands

For standard usage:
* `composer install` in the project root to install required dependencies.
* `php src/app.php` to run the CLI, optionally providing the input/output arguments described previously.
  * **Examples:**
    * `php src/app.php my-orders.jsonl processed-orders.jsonl`
    * `php src/app.php https://example.com/orders.jsonl processed-orders.csv`

Running tests:
* `bin/phpunit tests`: runs all tests using PHPUnit

Formatting code:
* `composer install --working-dir=tools/php-cs-fixer`: installs the required dependencies for `php-cs-fixer`.
* `tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src`: uses [`php-cs-fixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to format the `src` directory.

## Extending Functionality

### Adding a new input source

Create a new service in `src/Services` which extends the `App\Services\AbstractStreamReader` class and implement the abstract methods defined within (`supports`, `readBytes` and `close`) - the docblocks in `AbstractStreamReader` describe the expected inputs/outputs for these methods. Once your new service is built you can add it to the `$inputStream` match block in `src/app.php` so that it can automatically be resolved based on the input provided to the CLI. You can look at the existing *StreamReader services for inspiration.

### Adding a new output format

Create a new service in `src/Services` which extends the `App\Services\AbstractStreamWriter` class and implement the abstract methods defined within (`supports` and `writeLine`) - the docblocks in `AbstractStreamWriter` describe the expected inputs/outputs for these methods. Once your new service is built you can add it to the `$outputStream` match block in `src/app.php` so that it can automatically be resolved based on the output provided to the CLI. You can look at the existing *StreamWriter services for inspiration.
