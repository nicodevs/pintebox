<?php

namespace App\Jobs;

use App\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Storage;
use Log;

class ImportImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pin;
    protected $board;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pin, $board)
    {
        $this->pin = $pin;
        $this->board = $board;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $re = '/(http(s?):)([\/|.|\w|\S|-])*\.(?:jpg|gif|png)/m';
        preg_match_all($re, $this->pin['description'], $matches, PREG_SET_ORDER, 0);
        if (count($matches)) {
            $url = str_replace('236x/', 'originals/', $matches[0]);
            $url = $url[0];
        } else {
            Log::info('Error parsing ' . $this->pin['link']);
            return false;
        }

        if (array_key_exists('title', $this->pin) && is_string($this->pin['title'])) {
           $name = $this->pin['title'];
        } else {
           $name = str_slug($this->pin['link']);
        }

        $publishedAt = date("Y-m-d H:i:s", strtotime($this->pin['pubDate']));
        $image = [
            'name' => $name,
            'url' => $url,
            'guid' => $this->pin['guid'],
            'published_at' => $publishedAt,
            'board_id' => $this->board['id'],
        ];

        $contents = @file_get_contents($url);
        if (!$contents) {
            Log::info('Error reading ' . $url);
            return false;
        }

        $pathinfo = pathinfo($url);
        $path = $this->board['folder'] . '/' . $pathinfo['basename'];
        $result = Storage::disk('dropbox')->put($path, $contents);

        if ($result) {
            $image = Image::create($image);
            Log::info('Imported image ' . $url);
        } else {
            Log::info('Error saving ' . $url);
        }
    }
}
