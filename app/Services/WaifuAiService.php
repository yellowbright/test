<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * 看板娘 AI 问答服务（按 driver 切换 Ollama / NVIDIA NIM）。
 *
 * 设计要点：
 *  - 推理后端通过 config('ai.driver') 切换，控制器无感；
 *  - 单轮问答，system 注入人设，user 为提问；
 *  - HTTP 调用统一封装，超时 / 异常 / 非 2xx 归一化为 ERROR_UNAVAILABLE。
 */
class WaifuAiService
{
    public const ERROR_DISABLED = 'AI_DISABLED';

    public const ERROR_UNAVAILABLE = 'AI_UNAVAILABLE';

    public const ERROR_BAD_RESPONSE = 'AI_BAD_RESPONSE';

    public const ERROR_MISCONFIGURED = 'AI_MISCONFIGURED';

    public const DRIVER_OLLAMA = 'ollama';

    public const DRIVER_NVIDIA = 'nvidia';

    public function isEnabled(): bool
    {
        if (! config('ai.enabled')) {
            return false;
        }

        // NVIDIA 模式缺 key 等同禁用，避免前端误判可用
        if ($this->driver() === self::DRIVER_NVIDIA && empty(config('ai.nvidia.api_key'))) {
            return false;
        }

        return true;
    }

    /**
     * 针对单条提问生成看板娘回答。
     *
     * @throws RuntimeException 业务级错误，消息为 ::ERROR_* 常量
     */
    public function ask(string $question): string
    {
        if (! config('ai.enabled')) {
            throw new RuntimeException(self::ERROR_DISABLED);
        }

        $driver = $this->driver();
        if ($driver === self::DRIVER_NVIDIA && empty(config('ai.nvidia.api_key'))) {
            throw new RuntimeException(self::ERROR_MISCONFIGURED);
        }

        $messages = [
            ['role' => 'system', 'content' => (string) config('ai.persona')],
            ['role' => 'user', 'content' => $question],
        ];

        $content = match ($driver) {
            self::DRIVER_NVIDIA => $this->callNvidia($messages),
            default => $this->callOllama($messages),
        };

        $answer = trim($content);
        if ($answer === '') {
            throw new RuntimeException(self::ERROR_BAD_RESPONSE);
        }

        return $answer;
    }

    /**
     * 当前 driver（统一小写、容错未配置）。
     */
    protected function driver(): string
    {
        $d = strtolower((string) config('ai.driver', self::DRIVER_NVIDIA));

        return in_array($d, [self::DRIVER_OLLAMA, self::DRIVER_NVIDIA], true) ? $d : self::DRIVER_NVIDIA;
    }

    /**
     * 调用本地 Ollama，返回 message.content 原文。
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    protected function callOllama(array $messages): string
    {
        $url = rtrim((string) config('ai.ollama.base_url'), '/').'/api/chat';

        $response = $this->safeRequest(
            fn (PendingRequest $http) => $http->post($url, [
                'model' => config('ai.ollama.model'),
                'stream' => false,
                'messages' => $messages,
                'options' => config('ai.ollama.options'),
            ]),
            'Ollama'
        );

        $content = $response->json('message.content');
        if (! is_string($content) || $content === '') {
            throw new RuntimeException(self::ERROR_BAD_RESPONSE);
        }

        return $content;
    }

    /**
     * 调用 NVIDIA NIM（OpenAI 兼容 /chat/completions），返回 choices[0].message.content。
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    protected function callNvidia(array $messages): string
    {
        $url = rtrim((string) config('ai.nvidia.base_url'), '/').'/chat/completions';
        $apiKey = (string) config('ai.nvidia.api_key');
        $options = (array) config('ai.nvidia.options', []);

        $payload = array_merge([
            'model' => config('ai.nvidia.model'),
            'messages' => $messages,
            'stream' => false,
        ], $options);

        $response = $this->safeRequest(
            fn (PendingRequest $http) => $http
                ->withToken($apiKey)
                ->post($url, $payload),
            'NVIDIA'
        );

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || $content === '') {
            throw new RuntimeException(self::ERROR_BAD_RESPONSE);
        }

        return $content;
    }

    /**
     * 统一封装 HTTP 调用：超时、异常、非 2xx 全部归一化为 ERROR_UNAVAILABLE。
     *
     * @param  callable(PendingRequest): Response  $sender
     */
    protected function safeRequest(callable $sender, string $label): Response
    {
        $http = Http::timeout((int) config('ai.timeout'))
            ->connectTimeout(5)
            ->acceptJson();

        try {
            $response = $sender($http);
        } catch (\Throwable $e) {
            Log::warning("{$label} request failed", ['error' => $e->getMessage()]);
            throw new RuntimeException(self::ERROR_UNAVAILABLE);
        }

        if (! $response->successful()) {
            Log::warning("{$label} returned non-2xx", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException(self::ERROR_UNAVAILABLE);
        }

        return $response;
    }
}
