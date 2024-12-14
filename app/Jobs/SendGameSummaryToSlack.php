<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\Middleware\ThrottlesExceptions;


class SendGameSummaryToSlack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $game;
    public $message;

    /**
     * Create a new job instance.
     *
     * @param  object  $game
     * @param  string  $message
     */
    public function __construct($game = null, $message = '')
    {
        $this->game = $game;
        $this->message = $message;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (env('SLACK_KEY')) {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . env('SLACK_KEY'),
                'Content-Type' => 'application/json',
            ])
                ->withoutVerifying()
                ->post('https://slack.com/api/chat.postMessage', [
                    'channel' => '#informal',
                    'text' => $this->message,
                ]);
        }
    }
}
