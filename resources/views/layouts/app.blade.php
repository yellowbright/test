<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) · {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', config('app.name').' 提供软件工程、人工智能与数字化转型服务。')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<header class="site-header" id="siteHeader">
    <div class="container header-inner">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-mark">N</span>
            <span class="brand-name">{{ config('app.name') }}</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="切换菜单" aria-expanded="false" aria-controls="primaryNav">
            <span></span><span></span><span></span>
        </button>

        <nav class="primary-nav" id="primaryNav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">首页</a>
            <a href="{{ route('services') }}" class="{{ request()->routeIs('services') ? 'active' : '' }}">服务</a>
            <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">关于我们</a>
            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">联系我们</a>
            <a href="{{ route('contact') }}" class="btn btn-primary nav-cta">免费咨询</a>
        </nav>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col footer-brand">
            <div class="brand">
                <span class="brand-mark">N</span>
                <span class="brand-name">{{ config('app.name') }}</span>
            </div>
            <p>用技术为企业搭建通往增长的桥梁，从软件工程到 AI 与数字化转型。</p>
        </div>
        <div class="footer-col">
            <h4>服务</h4>
            <a href="{{ route('services') }}">Web 应用开发</a>
            <a href="{{ route('services') }}">移动应用开发</a>
            <a href="{{ route('services') }}">AI / 机器学习</a>
            <a href="{{ route('services') }}">UI/UX 设计</a>
        </div>
        <div class="footer-col">
            <h4>公司</h4>
            <a href="{{ route('about') }}">关于我们</a>
            <a href="{{ route('about') }}">服务行业</a>
            <a href="{{ route('contact') }}">联系我们</a>
        </div>
        <div class="footer-col">
            <h4>联系</h4>
            <a href="mailto:hello@example.com">hello@example.com</a>
            <a href="tel:+861000000000">+86 100 0000 000</a>
            <p>上海市 · 浦东新区</p>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            &copy; {{ date('Y') }} {{ config('app.name') }} Inc. 保留所有权利。
        </div>
    </div>
</footer>

<script src="{{ asset('js/main.js') }}"></script>
@stack('scripts')
</body>
</html>
