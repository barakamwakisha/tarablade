<?php

namespace Mwakisha\Tarablade\Console;

use Illuminate\Console\Command;
use Mwakisha\Tarablade\Tarablade;
use Mwakisha\Tarablade\TarabladeFileParser;

class ImportCommand extends Command
{
    // @codeCoverageIgnoreStart
    protected $signature = 'tarablade:import';

    protected $description = 'Converts HTML files in the specified directory to blade files and imports them into 
                                your Laravel project together with accompanying assets';

    public function handle()
    {
        $this->info("                        
             _____                _     _           _      
            /__   \__ _ _ __ __ _| |__ | | __ _  __| | ___ 
              / /\/ _` | '__/ _` | '_ \| |/ _` |/ _` |/ _ \
             / / | (_| | | | (_| | |_) | | (_| | (_| |  __/
             \/   \__,_|_|  \__,_|_.__/|_|\__,_|\__,_|\___|
                                               
        ");

        Tarablade::validateTemplateNamespace();

        $path = $this->ask('Where is the directory with the HTML files and assets located? ');

        try {
            $this->info('Checking if the directory at '.$path.' exists');
            Tarablade::validateDirectoryExists($path);
            $this->comment('Template directory found');

            $this->comment("Searching for 'index.html' in ".$path);
            Tarablade::validateFileExists($path.'/index.html');
            $this->comment('index.html file found');

            $this->info('Starting template import');

            $parser = new TarabladeFileParser(Tarablade::getAbsolutePath($path).'/index.html');
            $this->info('Importing assets and templates. Please wait...');
            $parser->importAssetsFromAllTemplates();
            $this->comment('Import complete. Run php artisan serve then visit http://localhost:8000/'.Tarablade::getTemplateNamespace().'/index');
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
    // @codeCoverageIgnoreEnd
}
