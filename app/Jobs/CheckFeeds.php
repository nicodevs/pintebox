<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use SimpleXMLElement;
use Storage;

class CheckFeeds implements ShouldQueue
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
        $board = $this->board;

        Log::info('Checking ' . $board['name'] . ' feed');
        $feed = @file_get_contents($board['url']);
        if (!$feed) {
            Log::info('Error reading ' . $board['url']);
            return false;
        }

        $data = new SimpleXMLElement($feed);
        $pins = [];
        foreach ($data->channel->item as $item) {
            $pins[] = json_decode(json_encode($item), true);
        }
        Log::info('Pins: ' . json_encode($pins));

        $boardDate = strtotime($board['created_at']);
        $guids = count($board['images']) ? array_pluck($board['images'], 'guid') : [];

        foreach ($pins as $pin) {
            Log::info('Checking ' . $pin['guid']);
            $pinDate = strtotime($pin['pubDate']);

            if ($pinDate > $boardDate && !in_array($pin['guid'], $guids)) {
                Log::info('New pin, dispatch import job');
                dispatch(new ImportImage($pin, $board));
            } else {
                Log::info('Pin already downloaded, continue');
            }
        }
    }
}
