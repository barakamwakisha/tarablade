<?php


namespace Mwakisha\Tarablade\Console;


use Illuminate\Console\Command;
use Mwakisha\Tarablade\Tarablade;
use Mwakisha\Tarablade\TarabladeFileParser;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class ImportCommand extends Command
{

    protected $signature = "tarablade:import";

    protected  $description = "Converts HTML files in the specified directory to blade files and imports them into 
                                your Laravel project together with accompanying assets";

    public function handle()
    {

        if(is_null(config('tarablade'))) {
            return $this->warn("Please publish the config file by running ".
                                      "'php artisan vendor:publish --tag=tarablade-config'");
        }

        $this->info("                        
             _____                _     _           _      
            /__   \__ _ _ __ __ _| |__ | | __ _  __| | ___ 
              / /\/ _` | '__/ _` | '_ \| |/ _` |/ _` |/ _ \
             / / | (_| | | | (_| | |_) | | (_| | (_| |  __/
             \/   \__,_|_|  \__,_|_.__/|_|\__,_|\__,_|\___|
                                               
        ");

        sleep(1);

        $path = $this->ask("Where is the directory with the HTML files and assets located? ");

        try {
            $this->info("Checking if the directory at " . $path . " exists");
            Tarablade::validateSourceDirectory($path);
            $this->comment("Template directory found");

            $this->comment("Searching for 'index.html' in " . $path);
            Tarablade::validateFileExists(Tarablade::cleanPath($path) . '/index.html');
            $this->comment("index.html file found");

            $this->info("Starting template import...");

            Tarablade::validateAssetDestinationFolders();
            Tarablade::createAssetDestinationFolders();

            $this->info("Importing images...");

            // TODO: Refactor to scan all html files in folder

            $parser = new TarabladeFileParser(Tarablade::cleanPath($path) . '/index.html');
            $parser->importImages();





        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }


    }
}