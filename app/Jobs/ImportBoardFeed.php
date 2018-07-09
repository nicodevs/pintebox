<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Storage;
use Log;

class ImportBoardFeed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $board;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($board)
    {
        $this->board = $board;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $feed = file_get_contents($this->board['url']);
        $path = $this->board['name'] . '/' . time() . '.xml';
        Storage::disk('local')->put($path, $feed);
        Log::info('Imported feed from board: ' . $board['name']);
    }
}
