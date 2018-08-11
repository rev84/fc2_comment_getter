<?php

define('CHAT_LOG_FILE', 'chat.log');
define('MESSAGE_COUNT', 100);

$config = json_decode(file_get_contents(dirname(__FILE__).'/config.json'), true);
$token = $config['token'];
$channelId = $config['channel_id'];
$lastCommentIndex = -1;
$hash2id = [];

file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.CHAT_LOG_FILE, '');

while (true) {
    $logs = explode("\n", file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.CHAT_LOG_FILE));

    $res = json_decode(file_get_contents(
        'https://live.fc2.com/api/getChannelComment.php?'.
        'token='.$token.
        '&channel_id='.$channelId.
        '&last_comment_index='.$lastCommentIndex
    ), true);

    $lastCommentIndex = $res['last_comment_index'];
    foreach ($res['comments'] as $c) {
        $userName  = $c['user_name'];
        $comment   = $c['comment'];
        $timestamp = $c['timestamp'];
        $color     = $c['color'];
        $size      = $c['size'];
        $lang      = $c['lang'];
        $hash      = $c['hash'];
        $anonymous = $c['anonymous'];

        if (isset($hash2id[$hash])) {
            $anonyNum = $hash2id[$hash];
        }
        else {
            $anonyNum = count($hash2id);
            $hash2id[$hash] = $anonyNum;
        }

        $logs[] = $timestamp."\t".($anonymous ? '匿名('.$anonyNum.')' : $userName)."\t".$comment;
        while (count($logs) > MESSAGE_COUNT) array_shift($logs);
    }
    file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.CHAT_LOG_FILE, join("\n", $logs));

    sleep(1);
}
