<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:prod';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $command = ['dep',"-f=".base_path('docker/deploy-prod.php'),'deploy','--ansi','-n','-vvv'];
        $command = ['dep',"-f=".base_path('docker/deploy-prod.php'),'deploy','--ansi','-n','-vvv'];
        $this->info(implode(' ',$command));
        $process = new Process($command);

        $process->setTimeout(3600);//总超时

        $tmp['message'] = '';
        $process->run(function ($type, $buffer)use(&$tmp) {
            $tmp['message'] .= $buffer;
            $this->info($buffer);
        });

        if ($process->isSuccessful()) {
            $this->info('Success');
        } else {
            $this->error('error');
        }

        $this->info('exitCode:'.$process->getExitCode());
        return 0;
    }
}
