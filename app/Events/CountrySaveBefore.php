<?php

namespace App\Events;

use App\Podcast;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class CountrySaveBefore extends Event
{
    use SerializesModels;

    public $podcast;

    /**
     * Create a new event instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct(Podcast $podcast)
    { echo "in"; exit;
        $this->podcast = $podcast;
    }
}
