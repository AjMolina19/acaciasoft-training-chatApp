<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageEvent;
use App\GroupMessage;
use Illuminate\Http\Request;
use App\User;
use App\Messages;
use Illuminate\Support\Facades\Auth;
use App\Events\PrivateMessageEvent;

class MessageController extends Controller
{
    public function conversation($userId) 
    {
        $users = User::where('id', '!=', Auth::id())->get();
        $friendInfo = User::findOrfail($userId);
        $myInfo = User::find(Auth::id());
        $groups = GroupMessage::get();

        $this->data['users'] = $users;
        $this->data['friendInfo'] = $friendInfo;
        $this->data['myInfo'] = $myInfo;
        $this->data['users'] = $users;
        $this->data['groups'] = $groups;

        return view('message.conversation', $this->data);
    }

    public function sendmessage(Request $request) {
        $request->validate([
            'message' => 'required',
            'receiver_id' => 'required'
        ]);

        $sender_id = Auth::id();
        $receiver_id = $request->receiver_id;

        $message = new Messages();
        $message->message = $request->message;

        if ($message->save()) {
            try {
                $message->users()->attach($sender_id, ['receiver_id' => $receiver_id]);
                $sender = User::where('id', '=', $sender_id)->first();

                $data['sender_id'] = $sender_id;
                $data['sender_name'] = $sender->name;
                $data['receiver_id'] = $receiver_id;
                $data['content'] = $message->message;
                $data['created_at'] = $message->created_at;
                $data['message_id'] = $message->id;

                event(new PrivateMessageEvent($data)); 

                return response()->json([
                    'data' => $data,
                    'success' => true,
                    'message' => 'Message sent successfully'
                ]);
            } catch (\Exception $e) {
                $message->delete();
            }
        }
    }
    public function send_group_message(Request $request) {
        $request->validate([
            'message' => 'required',
            'group_message_id' => 'required'
        ]);

        $sender_id = Auth::id();
        $group_message_id = $request->group_message_id;

        $message = new Messages();
        $message->message = $request->message;

        if ($message->save()) {
            try {
                $message->users()->attach($sender_id, ['group_message_id' => $group_message_id]);
                $sender = User::where('id', '=', $sender_id)->first();

                $data['sender_id'] = $sender_id;
                $data['sender_name'] = $sender->name;
                $data['content'] = $message->message;
                $data['created_at'] = $message->created_at;
                $data['message_id'] = $message->id;
                $data['group_id'] = $group_message_id;
                $data['type'] = 2;

                event(new GroupMessageEvent($data));

                return response()->json([
                    'data' => $data,
                    'success' => true,
                    'message' => 'Message sent successfully'
                ]);
            } catch (\Exception $e) {
                $message->delete();
            }
        }
    }
}
