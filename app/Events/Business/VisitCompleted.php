<?php

namespace App\Events\Business;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $visitId, public int $userId, public ?int $companyId = null) {}
}
