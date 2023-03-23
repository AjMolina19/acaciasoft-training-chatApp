<?php

namespace App\Http\Controllers;

use App\GroupMessage;
use App\GroupMessageMember;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data['name'] = $request->name;
        $data['user_id'] = Auth::id();

        $groupMessage = GroupMessage::create($data);

        if ($groupMessage) {
            if (isset($request->user_id) && !empty($request->user_id)) {
                foreach ($request->user_id as $userId) {
                    $memberData['user_id'] = $userId;
                    $memberData['group_message_id'] = $groupMessage->id;
                    $memberData['status'] = 0;

                    GroupMessageMember::create($memberData);
                }
            }
        }
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $users = User::where('id', '!=', Auth::id())->get();
        $myInfo = User::find(Auth::id());
        $groups = GroupMessage::get();
        $currentGroup = GroupMessage::where('id', $id)->with('groupMessageMember.user')->first();

        $this->data['users'] = $users;
        $this->data['myInfo'] = $myInfo;
        $this->data['groups'] = $groups;
        $this->data['currentGroup'] = $currentGroup;

        return view('groupsMessage.index', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
