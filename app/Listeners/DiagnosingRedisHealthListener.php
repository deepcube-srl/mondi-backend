<?php

namespace App\Listeners;

use App\Events\DiagnosingRedisHealth;
use Illuminate\Support\Facades\Redis;

class DiagnosingRedisHealthListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DiagnosingRedisHealth $event): void
    {
        try {
            Redis::resolve();
        } catch (\Throwable $e) {
            throw new \Exception('Default Redis Connection failed.');
        }
    }
}
