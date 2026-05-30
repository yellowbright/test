@extends('layouts.app')

@section('title', '联系我们')
@section('meta_description', '联系 Nexora，告诉我们你的项目需求，我们会尽快回复。')

@section('content')
<section class="page-banner">
    <div class="container">
        <span class="eyebrow">Contact Us</span>
        <h1>聊聊你的需求</h1>
        <p>填写下面的表单，或通过邮箱、电话联系我们，我们将在 1 个工作日内回复。</p>
    </div>
</section>

<section class="section">
    <div class="container contact-grid">
        <div>
            <div class="info-block">
                <div class="k">邮箱</div>
                <div class="v"><a href="mailto:hello@example.com">hello@example.com</a></div>
            </div>
            <div class="info-block">
                <div class="k">电话</div>
                <div class="v"><a href="tel:+861000000000">+86 100 0000 000</a></div>
            </div>
            <div class="info-block">
                <div class="k">地址</div>
                <div class="v">上海市浦东新区 · 张江</div>
            </div>
            <div class="info-block">
                <div class="k">工作时间</div>
                <div class="v">周一至周五 9:00 - 18:00</div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-alert"></div>
            <form id="contactForm" action="{{ route('contact.store') }}" method="POST" novalidate>
                @csrf
                <div class="form-row">
                    <div class="field">
                        <label for="name">姓名 *</label>
                        <input type="text" id="name" name="name" autocomplete="name">
                        <div class="err"></div>
                    </div>
                    <div class="field">
                        <label for="email">邮箱 *</label>
                        <input type="email" id="email" name="email" autocomplete="email">
                        <div class="err"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="phone">电话</label>
                        <input type="text" id="phone" name="phone" autocomplete="tel">
                        <div class="err"></div>
                    </div>
                    <div class="field">
                        <label for="subject">主题</label>
                        <input type="text" id="subject" name="subject">
                        <div class="err"></div>
                    </div>
                </div>
                <div class="field">
                    <label for="message">留言内容 *</label>
                    <textarea id="message" name="message"></textarea>
                    <div class="err"></div>
                </div>
                <button type="submit" class="btn btn-primary">提交</button>
            </form>
        </div>
    </div>
</section>
@endsection
