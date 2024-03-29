@extends('layouts.app')

@section('content')
    <div class="row chat-row">
        <div class="col-md-3">
            <div class="users">
                <h5>Users</h5>
                <ul class="list-group list-chat-item">
                    @if($users->count())
                        @foreach ($users as $user)    
                            <li class="chat-user-list 
                                @if($user->id == $friendInfo->id) active @endif">
                                <a href="{{ route('message.conversation', $user->id) }}">
                                    <div class="chat-image">
                                        {!! makeImageFromName($user->name) !!}
                                        <i class="fa fa-circle user-status-icon user-icon-{{ $user->id }}" title="away"></i>
                                    </div>

                                    <div class="chat-name font-weight-bold">
                                        {{ $user->name }}
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
            <div class="groups mt-5">
                <h5>Groups<i class="fa fa-plus btn-add-group ml-3"></i></h5>
                <ul class="list-group list-chat-item">
                    @if($groups->count())
                        @foreach ($groups as $group)
                            <li class="chat-user-list">
                                <a href="{{ route('group-message.show', $group->id) }}">
                                    {{ $group->name }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
        <div class="col-md-8">
            <div class="chat-header">
                <div class="chat-image">
                    {!! makeImageFromName($user->name) !!}
                </div>
                <div class="chat-name font-weight-bold">
                    {{ $user->name }}
                    <i class="fa fa-circle user-status-head" title="away" id="userStatusHead {{ $friendInfo->id }}"></i>
                </div>
            </div>
            <div class="chat-body" id="chatBody">
                <div class="message-listing" id="messageWrapper">
                </div>
            </div>
            <div class="chat-box">
                <div class="chat-input bg-white" id="chatInput" contenteditable="">
                </div>
                <div class="chat-input-toolbar">
                    <button title="Add File" class="btn btn-light btn-sm btn-file-upload">
                        <i class="fa fa-paperclip"></i>
                    </button>
                    <button title="Bold" class="btn btn-light btn-sm tool-items" 
                        onclick="document.execCommand('bold', false, '');">
                        <i class="fa fa-bold tool-icon"></i>
                    </button>
                    <button title="Italic" class="btn btn-light btn-sm tool-items"
                        onclick="document.execCommand('italic', false, '');">
                        <i class="fa fa-italic tool-icon"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add to Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('group-message.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Group Name</label>
                            <input type="text" class="form-control" name="name" id="">
                        </div>
                        <div class="form-group">
                            <label for="">Select Member</label>
                            <select class="form-control" name="user_id[]" id="selectMember" multiple>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"/>
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(function() {
            let $ChatInput = $('.chat-input');
            let $ChatInputToolbar = $('.chat-input-toolbar');
            let $ChatBody = $('.chat-body');
            let $messageWrapper = $("#messageWrapper");


            let user_id = "{{ auth()->user()->id }}";
            let ip_address = '127.0.0.1';
            let socket_port = '3000';
            let socket = io(ip_address + ':' + socket_port);
            let friendId = "{{ $friendInfo->id }}";
           
            socket.on('connect', function() {
                socket.emit('user_connected', user_id);
            });

            socket.on('updateUserStatus', (data) => {

                let $userStatusIcon = $('.user-status-icon');
                $userStatusIcon.removeClass('tex-success');
                $userStatusIcon.attr('title', 'Away');
                console.log(data);

                $.each(data, function (key, val) {
                    if (val !== null && val!= 0 ){
                        console.log(key);
                        let $userIcon = $(".user-icon-"+key);
                        $userIcon.addClass('text-success');
                        $userIcon.attr('title', 'Online');
                    }
                });
            });

            $ChatInput.keypress(function (e) { 
                let message = $(this).html();
                if (e.which === 13 && !e.ShiftKey) {
                    $ChatInput.html("");
                    sendMessage(message);
                    return false;

                }
            });

            function sendMessage(message) {
                let url = "{{ route('message.send-message') }}";
                let form = $(this);
                let formData = new FormData();
                let token = "{{ csrf_toKen() }}";

                formData.append('message', message);
                formData.append('_token', token);
                formData.append('receiver_id', friendId);

                appendMessageToSender(message);

                $.ajax({
                url: url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function (response) {
                    if(response.success) {
                        console.log(response.data);
                    }
                }
            });
        }
        
        function appendMessageToSender(message) {
            let name = '{{ $myInfo->name }}';
            let Image = '{!! makeImageFromName($myInfo->name) !!}';
            let userInfo = '<div class="col-md-12 user-info">\n' +
                            '<div class="chat-image">\n' + Image +
                            '</div>\n' +
                            '\n' +
                            '<div class="chat-name font-weight-bold">\n' + 
                            name +
                            '<span class="small time text-gray-500" title="'+getCurrentDateTime()+'">\n' +
                            getCurrentTime()+ '</span>\n' +
                            '</div>\n' +
                            '</div>';

            let messageContent = '<div class="col-md-12 message-content">\n' +
                                    '<div class="message-text">\n' + message +
                                    '</div>\n' +
                                    '</div>';
            let newMessage = '<div class="row message align-items-center mb-2">' 
                                +userInfo + messageContent + 
                                '</div>';

            $messageWrapper.append(newMessage);
        }

        function appendMessageToReceiver(message) {
            let name = '{{ $friendInfo->name }}';
            let Image = '{!! makeImageFromName($friendInfo->name) !!}';
            let userInfo = '<div class="col-md-12 user-info">\n' +
                            '<div class="chat-image">\n' + Image +
                            '</div>\n' +
                            '\n' +
                            '<div class="chat-name font-weight-bold">\n' + 
                            name +
                            '<span class="small time text-gray-500" title="'+dateFormat(message.created_at)+'">\n' +
                            timeFormat(message.created_at) + '</span>\n' +
                            '</div>\n' +
                            '</div>';

            let messageContent = '<div class="col-md-12 message-content">\n' +
                                    '<div class="message-text">\n' + message.content +
                                    '</div>\n' +
                                    '</div>';
            let newMessage = '<div class="row message align-items-center mb-2">' 
                                +userInfo + messageContent + 
                                '</div>';

            $messageWrapper.append(newMessage);
        }
        socket.on("private-channel:App\\Events\\PrivateMessageEvent", function(message)
        {
            appendMessageToReceiver(message);
        });

        $('.btn-add-group').click(function (e) { 
            e.preventDefault();
            $('#addGroupModal').modal('show');
        });

        $('#selectMember').select2();
    });
    </script>
@endpush
