@extends('layouts.app')

@section('title', '软件工程 · 人工智能 · 数字化转型')
@section('meta_description', 'Nexora 为企业提供软件工程、AI/机器学习与数字化转型服务，助力业务增长。')

@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow">We Build</span>
            <h1>实现你的软件愿景</h1>
            <p>从软件工程、人工智能到数字化转型，我们用可扩展、可靠的技术方案，帮助企业把想法变成产品，把产品变成增长。</p>
            <div class="hero-actions">
                <a href="{{ route('contact') }}" class="btn btn-primary">免费咨询</a>
                <a href="{{ route('services') }}" class="btn btn-ghost">了解服务</a>
            </div>
            <div class="hero-badges">
                <div><div class="num">18+</div><div class="label">年技术沉淀</div></div>
                <div><div class="num">580+</div><div class="label">成功项目</div></div>
                <div><div class="num">110+</div><div class="label">技术专家</div></div>
            </div>
        </div>
        <div class="hero-visual">产品 / 团队 视觉占位</div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">What we provide</span>
            <h2>我们的核心业务方案</h2>
            <p>量身定制的策略，提升效率、驱动数据决策、优化客户体验。</p>
        </div>
        <div class="grid grid-3">
            @foreach ([
                ['01', '业务流程自动化', '梳理并自动化重复性流程，显著提升运营效率。'],
                ['02', '商业智能 BI', '挖掘数据价值，支撑科学决策，构建竞争优势。'],
                ['03', '电商开发', '打造顺畅的在线购物体验，提升转化与复购。'],
                ['04', '客户关系管理', '优化销售流程，增强客户互动与留存。'],
                ['05', '企业内容管理', '高效组织、管理并保护关键业务内容。'],
                ['06', '遗留系统现代化', '升级老旧系统，提升性能、安全与可维护性。'],
            ] as $item)
                <div class="card">
                    <div class="step-no">{{ $item[0] }}</div>
                    <h3>{{ $item[1] }}</h3>
                    <p>{{ $item[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Expertise</span>
            <h2>我们的技术栈</h2>
            <p>使用业界成熟且前沿的技术，兼顾效率、稳定与扩展性。</p>
        </div>
        <div class="tech-tags">
            @foreach (['Laravel', 'PHP', '.NET', 'Node.js', 'Python', 'React', 'Vue', 'Angular', 'Flutter', 'iOS', 'Android', 'MySQL', 'PostgreSQL', 'Redis', 'AWS', 'Azure', 'Docker'] as $tech)
                <span>{{ $tech }}</span>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="stats">
            <div><div class="num">18+</div><div class="label">年信赖</div></div>
            <div><div class="num">580+</div><div class="label">成功项目</div></div>
            <div><div class="num">110+</div><div class="label">IT 专家</div></div>
            <div><div class="num">9+</div><div class="label">全球办公室</div></div>
        </div>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Testimonials</span>
            <h2>客户怎么说</h2>
        </div>
        <div class="scroller">
            @foreach ([
                ['项目交付完全符合规格，几乎没有 bug，沟通高效专业，还给出了很多有价值的建议。', 'Davis Carbo', '联合创始人 & CEO'],
                ['团队响应非常及时，合作四年来始终如一，整体非常满意。', 'Jenny Wang', '人才发展负责人'],
                ['我们的 App 收获了全五星评价，强烈推荐这个开发团队。', 'Richard Cruz', '项目负责人'],
            ] as $t)
                <div class="quote">
                    <p>“{{ $t[0] }}”</p>
                    <div class="who">{{ $t[1] }}</div>
                    <div class="role">{{ $t[2] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta">
            <h2>准备好启动你的项目了吗？</h2>
            <p>告诉我们你的需求，我们会在 1 个工作日内回复，提供免费的方案评估。</p>
            <a href="{{ route('contact') }}" class="btn btn-ghost">联系我们</a>
        </div>
    </div>
</section>
@endsection
