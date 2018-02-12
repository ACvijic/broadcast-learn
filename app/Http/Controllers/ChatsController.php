<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Events\MessageSent;

/**
 *  @category   Controller
 *  @package    Broadcast-learn
 *  @author     Aleksa Cvijić <aleksa.cvijic@cubes.rs>
 *  @copyright  2015-2018 Cubes d.o.o.
 *  @version    1.0.0 One of the first classes implemented.
 *  @link       http://broadcast.learn/
 *  @since      Class available since Release 1.0.0
 */
class ChatsController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth');
    }

    /**
     *  Shows chats
     *
     *  @return \Illuminate\Http\Response
     *  @access public
     */
    public function index()
    {
        return view('chat');
    }

    /**
     *  Fetches all messages
     *
     *  @return Message
     *  @access public
     */
    public function fetchMessages()
    {
        return Message::with('user')->get();
    }

    /**
     *  Persists message to database
     *
     *  @param  \Illuminate\Http\Request $request
     *  @return \Illuminate\Http\Response
     *  @access public
     */
    public function sendMessage(Request $request)
    {
        $user = Auth::user();

        $message = $user->messages()->create([
            'message' => $request->input('message')
        ]);

        broadcast(new MessageSent($user, $message))->toOthers();

        return ['status' => 'Message Sent!'];
    }
}