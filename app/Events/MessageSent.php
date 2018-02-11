<?php

use App\Models\User,
    App\Models\Message;

use Illuminate\Broadcasting\InteractsWithSockets,
    Illuminate\Broadcasting\PresenceChannel,
    Illuminate\Broadcasting\PrivateChannel,
    Illuminate\Broadcasting\Channel;

use Illuminate\Queue\SerializesModels;

use Illuminate\Foundation\Events\Dispatchable;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 *  @category   Event
 *  @package    Broadcast-learn
 *  @author     Aleksa Cvijić <aleksa.cvijic@cubes.rs>
 *  @copyright  2015-2018 Cubes d.o.o.
 *  @version    1.0.0 One of the first classes implemented.
 *  @link       http://broadcast.learn/
 *  @since      Class available since Release 1.0.0
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     *  User that sent the message
     *
     *  @var    \App\Models\User
     *  @access public
     */
    public $user;

    /**
     *  Message details
     *
     *  @var    \App\Models\Message
     *  @access public
     */
    public $message;

    /**
     *  Creates a new event instance
     *
     *  @param  \App\Models\User    $user
     *  @param  \App\Models\Message $message
     *  @return void
     *  @access public
     */
    public function __construct(User $user, Message $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     *  Gets the channels the event should broadcast on
     *
     *  @return Channel|array
     *  @access public
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat');
    }
}