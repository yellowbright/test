<x-mail::message>
# 新的联系表单留言

**姓名：** {{ $contact->name }}
**邮箱：** {{ $contact->email }}
**电话：** {{ $contact->phone ?: '—' }}
**主题：** {{ $contact->subject ?: '—' }}

**留言内容：**

{{ $contact->message }}

<small>来源 IP：{{ $contact->ip }} · 时间：{{ $contact->created_at }}</small>
</x-mail::message>
