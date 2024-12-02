<?php

namespace App\Listeners;

use App\Events\DiagnosingMysqlHealth;
use Illuminate\Support\Facades\DB;

class DiagnosingMysqlHealthListener
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
    public function handle(DiagnosingMysqlHealth $event): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            throw new \Exception('Default DB Connection failed.');
        }
    }
}
