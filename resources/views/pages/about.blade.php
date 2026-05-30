@extends('layouts.app')

@section('title', '关于我们')
@section('meta_description', '了解 Nexora：以协作、创新与卓越为核心，为企业提供可扩展的技术解决方案。')

@section('content')
<section class="page-banner">
    <div class="container">
        <span class="eyebrow">About Us</span>
        <h1>领先的软件开发公司</h1>
        <p>我们以技术为桥梁，连接企业愿景与商业成功——无论是怀揣梦想的创业者，还是行业领军的大型企业。</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid grid-3">
            @foreach ([
                ['协作', '汇聚多元背景的人才，搭建共享想法、共创方案的平台。'],
                ['创新', '拥抱新兴技术，紧跟行业趋势，不断探索新的可能。'],
                ['卓越', '以超越客户期待为目标，专注交付卓越成果。'],
            ] as $v)
                <div class="card">
                    <h3>{{ $v[0] }}</h3>
                    <p>{{ $v[1] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="stats">
            <div><div class="num">18+</div><div class="label">年信赖</div></div>
            <div><div class="num">580+</div><div class="label">成功项目</div></div>
            <div><div class="num">110+</div><div class="label">IT 专家</div></div>
            <div><div class="num">9+</div><div class="label">全球办公室</div></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Industries We Serve</span>
            <h2>我们服务的行业</h2>
            <p>针对不同行业的特定挑战，提供定制化解决方案。</p>
        </div>
        <div class="grid grid-4">
            @foreach (['医疗健康', '教育', '金融', '房地产', '零售', '物流出行', '制造业', '互联网'] as $ind)
                <div class="card text-center"><h3 class="mt-0">{{ $ind }}</h3></div>
            @endforeach
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="cta">
            <h2>想了解我们能为你做什么？</h2>
            <p>联系我们，开启一次免费的需求沟通。</p>
            <a href="{{ route('contact') }}" class="btn btn-ghost">联系我们</a>
        </div>
    </div>
</section>
@endsection
