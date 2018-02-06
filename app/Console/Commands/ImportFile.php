<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:import-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import file into database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = File::allFiles(storage_path('imports'));
        if ($files) {
            foreach ($files as $file)
            {
                echo "Import of: ", $file, "\n";
                Excel::load(storage_path('imports').'/'.$file, function($reader) {

                    // reader methods

                });
            }
        } else {
            echo "No file to import\n";
        }
    }
}
