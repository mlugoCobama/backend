<?php

namespace Modules\Pld\Services;

use Illuminate\Support\Facades\File;

class PromtService
{

    public function getPromptFromFile(string $filename, array $variables = []): string
    {
       $prompt =  File::get(base_path('Modules/Pld/resources/assets/prompts/' . $filename));

        foreach ($variables as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }

        return $prompt;
    }
}
