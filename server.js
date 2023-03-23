const { on } = require('events');
const express = require('express');

const app = express();

const server = require('http').createServer(app);
const Redis = require('ioredis');
const redis = new Redis();
var users = [];
var groups = [];

const io = require('socket.io')(server, {
    cors: {origin: "*"}
});

server.listen(3000, () => {
    console.log('Listening to port 3000');
});

redis.subscribe("private-channel", (err, count) => {
    if (err) {
        console.error("Failed to subscribe: %s", err.message);
    } else {
        console.log(`Subscribed successfully to private-channel`
        );
    }
});

redis.subscribe("group-channel", (err, count) => {
    if (err) {
        console.error("Failed to subscribe: %s", err.message);
    } else {
        console.log(`Subscribed successfully to group-channel`
        );
    }
});

redis.on('message', function(channel, message) {
    message = JSON.parse(message);
    console.log(message);
    if (channel == 'private-channel') {
        let data = message.data.data;
        let receiver_id = data.receiver_id;
        let event = message.event;

        io.to(`${users[receiver_id]}`).emit(channel + ':' + event, data);
    }

    if (channel == 'group-channel') {
        let data = message.data.data;
      
        if (data.type == 2) {
            io.to('group'+data.group_id).emit('groupMessage', data)
        }
    }
      
});

io.on('connection', (socket) => {

    socket.on('user_connected', (user_id) => {
        users[user_id] = socket.id;
        io.emit('updateUserStatus', users);
        console.log('user connected '+ user_id);
    });

    socket.on('disconnect', (socket) => {
        var i = users.indexOf('socket.id');
        users.splice(i, 1, 0);
        io.emit('updateUserStatus', users);
        console.log(users);
    });

    socket.on('join_group', (data) => {
        data['socket_id'] = socket.id;
        if (groups[data.group_id]) {
            console.log("group already exist");
            userExist = checkIfUserExistInGroup(data.user_id, data.group_id);
            if (!userExist) {
                groups[data.group_id].push(data);
                socket.join(data.room);
            } else {
                var index = groups[data.group_id].map(function(o) {
                    return o.user_id;
                }).indexOf(data.user_id);

                groups[data.group_id].splice(index,1);
                groups[data.group_id].push(data);
                socket.join(data.room);
            }
        
        } else {
            console.log("new group");
            groups[data.group_id] = [data];
            socket.join(data.room);
        }
        console.log('socket-id: '+ socket.id+' - user-id: '+data.user_id);
        console.log(groups);
    });
});

function checkIfUserExistInGroup(user_id, group_id) { 
    var group = groups[group_id];
    var exist = false;
    if (groups.length > 0) {
        for (var i = 0; i < group.length; i++) {
            if (group[i]['user_id'] == user_id) {
                exist = true;
                break;
            }
        }
    }
    return exist;
}

// function getSocketIdOfUserInGroup(user_id, group_id) {
//     var group = groups[group_id];
//     if (groups.length > 0) {
//         for (var i = 0; i < group.length; i++) {
//             if (group[i]['user_id'] == user_id) {
//                 return group[i]['socket_id'];
//             }
//         }
//     }
//  }
