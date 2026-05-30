@extends('layouts.app')

@section('title', '服务')
@section('meta_description', 'Nexora 提供 Web 与移动应用开发、AI/机器学习、UI/UX 设计、云与 DevOps 等数字化服务。')

@section('content')
<section class="page-banner">
    <div class="container">
        <span class="eyebrow">Our Services</span>
        <h1>面向数字化转型的 IT 服务</h1>
        <p>从软件开发、数字化转型到咨询与运维，我们助力企业在数字时代持续成长。</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-2">
            @foreach ([
                ['Web 应用开发', '基于前沿技术，交付稳健、可扩展的 Web 应用，满足多样化业务需求。'],
                ['移动应用开发', '为 iOS 与 Android 打造创新、易用的原生及跨平台应用。'],
                ['AI / 机器学习', '从智能推荐到流程自动化，用 AI 释放数据价值。'],
                ['UI/UX 设计', '以用户为中心的界面设计，提升体验与转化。'],
                ['云与 DevOps', '可扩展、可靠的部署体系，加速交付并降低运维成本。'],
                ['咨询与运维', '提供技术咨询、支持与长期维护，保障系统稳定运行。'],
            ] as $i => $s)
                <div class="card">
                    <div class="icon">{{ $i + 1 }}</div>
                    <h3>{{ $s[0] }}</h3>
                    <p>{{ $s[1] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Work Structure</span>
            <h2>我们的开发流程</h2>
            <p>从需求梳理到上线运维，标准化流程保障交付质量。</p>
        </div>
        <div class="grid grid-4">
            @foreach ([
                ['需求分析', '深入理解业务目标，明确范围与优先级。'],
                ['设计与原型', '产出架构方案与交互原型，快速对齐。'],
                ['敏捷开发', '迭代式开发，持续交付可用版本。'],
                ['测试上线', '严格测试后上线，并提供持续运维。'],
            ] as $i => $step)
                <div class="card">
                    <div class="step-no">STEP {{ $i + 1 }}</div>
                    <h3>{{ $step[0] }}</h3>
                    <p>{{ $step[1] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta">
            <h2>需要一个可靠的技术合作伙伴？</h2>
            <p>无论是从零开始还是系统升级，我们都能提供合适的方案。</p>
            <a href="{{ route('contact') }}" class="btn btn-ghost">免费咨询</a>
        </div>
    </div>
</section>
@endsection
