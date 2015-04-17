
// 最简的示例代码，请换成自己的 appId，可以通过浏览器多个标签模拟多用户通信
var appId = '61f8o88g50rrpmzeu9fep3v5z44j0pve8wqqzf82hspqcp2h';
// clientId 就是实时通信中的唯一用户 id
var clientId = '551d105ee4b01ae28398891f';
var realtimeObj;
var conversationObj;

// 创建实时通信实例（支持单页多实例）
realtimeObj = AV.realtime({
    appId: appId,
    clientId: clientId
    // 是否开启服务器端认证
    // auth: authFun
});
alert('asdf');

// 当前 SDK 版本
console.log('当前 SDK 版本是 ' + AV.realtime.version);

// 实时通信服务连接成功
realtimeObj.on('open', function() {
    console.log('实时通信服务建立成功！');

    // 创建一个聊天室，conv 是 conversation 的缩写，也可以用 room 方法替换
    conversationObj = realtimeObj.conv({
        // 人员的 id
        members: [
            'LeanCloud02'
        ],
        // 默认的数据，可以放 Conversation 名字等
        data: {
            name: 'LeanCloud',
            m: 123
        }
    }, function(data) {
        if (data) {
            console.log('Conversation 创建成功!', data);
        }
    });
});

// 当聊天断开时触发
realtimeObj.on('close', function() {
    console.log('实时通信服务被断开！');
});

// 接收断线或者网络状况不佳的事件（断网可测试）
realtimeObj.on('reuse', function() {
    console.log('正在重新连接。。。');
});

// 当 Conversation 被创建时触发，当然您可以使用回调函数来处理，不一定要监听这个事件
realtimeObj.on('create', function(data) {

    // 向这个 Conversation 添加新的用户
    conversationObj.add([
        'LeanCloud03', 'LeanCloud04'
    ], function(data) {
        console.log('成功添加用户：', data);
    });

    // 从这个 Conversation 中删除用户
    conversationObj.remove('LeanCloud03', function(data) {
        console.log('成功删除用户：', data);
    });

    // 向这个 Conversation 中发送消息
    conversationObj.send({
        abc: 123
    }, function(data) {
        console.log('发送的消息服务端已收收到：', data);
    });

    setTimeout(function() {
        // 查看历史消息
        conversationObj.log(function(data) {
            console.log('查看当前 Conversation 最近的聊天记录：', data);
        });
    }, 2000);

    // 当前 Conversation 接收到消息
    conversationObj.receive(function(data) {
        console.log('当前 Conversation 收到消息：', data);
    });

    // 获取当前 Conversation 中的成员信息
    conversationObj.list(function(data) {
        console.log('列出当前 Conversation 的成员列表：', data);
    });

    // 取得当前 Conversation 中的人数
    conversationObj.count(function(num) {
        console.log('取得当前的用户数量：' + num);
    });
});

// 监听所有用户加入的情况
realtimeObj.on('join', function(data) {
    console.log('有用户加入某个当前用户在的 Conversation：', data);
});

// 监听所有用户离开的情况
realtimeObj.on('left', function(data) {
    console.log('有用户离开某个当前用户在的 Conversation：', data);
});

// 监听所有 Conversation 中发送的消息
realtimeObj.on('message', function(data) {
    console.log('某个当前用户在的 Conversation 接收到消息：', data);
});