<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>节日提醒</title>
</head>
<body>
<p>您好，{{ $reminder->user->name ?? $reminder->user->email }}：</p>
<p>这是您的节日提醒：</p>
<ul>
    <li>日期：{{ $reminder->date->format('Y-m-d') }}</li>
    <li>内容：{{ $reminder->content }}</li>
    <li>提前天数：{{ $reminder->remind_before_days }}</li>
</ul>
<p>祝您生活愉快。</p>
</body>
</html>
