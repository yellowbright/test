<?php

/*
|--------------------------------------------------------------------------
| 看板娘 AI 问答配置
|--------------------------------------------------------------------------
|
| 通过 AI_ENABLED=false 可一键关闭功能（接口返回 disabled，前端隐藏看板娘）。
|
| AI_DRIVER 切换推理后端：
|   - ollama : 本地 Ollama（离线、免费、依赖宿主机算力）
|   - nvidia : NVIDIA NIM 远程 API（云端、需要 API Key）
|
*/

return [
    'enabled' => env('AI_ENABLED', true),

    'driver' => env('AI_DRIVER', 'nvidia'),

    'timeout' => (int) env('AI_TIMEOUT', 25),

    // 看板娘人设：决定回答风格
    'persona' => env(
        'AI_WAIFU_PERSONA',
        '你是一个名叫 Pio 的可爱看板娘助手，性格活泼友善。请用简洁、口语化的中文回答用户的问题，回答不要太长，避免使用 markdown 和 emoji。'
    ),

    'ollama' => [
        'base_url' => env('AI_BASE_URL', 'http://127.0.0.1:11434'),
        'model' => env('AI_MODEL', 'qwen2.5:3b-instruct-q4_K_M'),
        'options' => [
            'num_ctx' => 1024,
            'num_predict' => 256,
            'temperature' => 0.7,
            'top_p' => 0.9,
        ],
    ],

    'nvidia' => [
        'base_url' => env('AI_NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
        'api_key' => env('AI_NVIDIA_API_KEY'),
        'model' => env('AI_NVIDIA_MODEL', 'meta/llama-3.1-8b-instruct'),
        'options' => [
            'max_tokens' => 256,
            'temperature' => 0.7,
            'top_p' => 0.9,
        ],
    ],
];
