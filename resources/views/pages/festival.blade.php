@extends('layouts.app')

@section('title', '节日提醒')
@section('meta_description', '在日历上选择节日并设置提前提醒，登录后保存，到期自动邮件提醒。')

@push('head')
    <link rel="stylesheet" href="{{ asset('festival_app/assets/index.css') }}">
@endpush

@section('content')
    <div id="app"></div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('festival_app/assets/app.js') }}"></script>
@endpush
