<?php

declare(strict_types=1);

namespace App\Enums;

use Prism\Prism\Enums\Provider;

enum ModelName: string
{
    case GPT_5_MINI = 'gpt-5-mini';
    case GPT_5_NANO = 'gpt-5-nano';
    case GEMMA4 = 'gemma4:latest';
    case LLAMA3_2_3B = 'llama3.2:3b';
    case QWEN3_5 = 'qwen3.5:latest';
    case GEMMA3 = 'gemma3:latest';

    /**
     * @return array{id: string, name: string, description: string, provider: string}[]
     */
    public static function getAvailableModels(): array
    {
        return array_map(
            fn (ModelName $model): array => $model->toArray(),
            self::cases()
        );
    }

    public function getName(): string
    {
        return match ($this) {
            self::GPT_5_MINI => 'GPT-5 mini',
            self::GPT_5_NANO => 'GPT-5 Nano',
            self::GEMMA4 => 'Gemma 4 (9B)',
            self::LLAMA3_2_3B => 'Llama 3.2 (3B)',
            self::QWEN3_5 => 'Qwen 3.5 (9B)',
            self::GEMMA3 => 'Gemma 3 (4B)',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::GPT_5_MINI => 'Cheapest OpenAI model, best for smarter tasks',
            self::GPT_5_NANO => 'Cheapest OpenAI model, best for simpler tasks',
            self::GEMMA4 => 'Local Gemma 4 model via Ollama',
            self::LLAMA3_2_3B => 'Local Llama 3.2 3B model via Ollama',
            self::QWEN3_5 => 'Local Qwen 3.5 9B model via Ollama',
            self::GEMMA3 => 'Local Gemma 3 4B model via Ollama',
        };
    }

    public function getProvider(): Provider
    {
        return match ($this) {
            self::GPT_5_MINI, self::GPT_5_NANO => Provider::OpenAI,
            self::GEMMA4, self::LLAMA3_2_3B, self::QWEN3_5, self::GEMMA3 => Provider::Ollama,
        };
    }

    /**
     * @return array{id: string, name: string, description: string, provider: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'provider' => $this->getProvider()->value,
        ];
    }
}
