<?php
namespace App\Console\Commands;

use App\Services\Game\kg;
use App\Services\Game\avia;
use App\Services\Game\ig;
use App\Services\Game\leg;
use App\Services\Game\ticket;
use App\Services\Game\chess;
use DB;
use Illuminate\Console\Command;

class GameRecord extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gameRecord';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Game Record';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        date_default_timezone_set('PRC');

        $ticket = new ticket();
        @$gameRecord = $ticket->getRecord();

        $kg = new kg();
        @$gameRecord = $kg->getRecord();

        $leg = new leg();
        @$gameRecord = $leg->getRecord();

        $chess = new chess();
        @$gameRecord = $chess->getRecord();

        $ig = new ig();
        @$gameRecord = $ig->getRecord();
    }

}
