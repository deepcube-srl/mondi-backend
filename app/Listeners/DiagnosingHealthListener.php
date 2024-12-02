<?php

namespace App\Listeners;

use App\Events\DiagnosingMysqlHealth;
use App\Events\DiagnosingRedisHealth;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;

class DiagnosingHealthListener
{
    /**
     * Handle the event.
     */
    public function handle(DiagnosingHealth $event): void
    {
        Event::dispatch(new DiagnosingMysqlHealth);
        Event::dispatch(new DiagnosingRedisHealth);
    }
}
