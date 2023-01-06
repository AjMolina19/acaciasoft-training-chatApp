const { on } = require('events');
const express = require('express');

const app = express();

const server = require('http').createServer(app);
const Redis = require('ioredis');
const redis = new Redis();
var users = [];

const io = require('socket.io')(server, {
    cors: {origin: "*"}
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
});

server.listen(3000, () => {
    console.log('Listening to port 3000');
});

redis.subscribe("private-channel", (err, count) => {
    if (err) {
        // Just like other commands, subscribe() can fail for some reasons,
        // ex network issues.
        console.error("Failed to subscribe: %s", err.message);
    } else {
        // `count` represents the number of channels this client are currently subscribed to.
        console.log(`Subscribed successfully! This client is currently subscribed to ${count} channels.`
        );
    }

    redis.on("message", (channel, message) => {
        message = JSON.parse(message);
        console.log(channel);
        if(channel == "private-channel") {
            let data = message.data.data;
            let receiver_id = data.receiver_id;
            let event = message.event;

            io.to(`${users[receiver_id]}`).emit(channel + ':' + message.event, data);
        }
        console.log(message);
    });
});



