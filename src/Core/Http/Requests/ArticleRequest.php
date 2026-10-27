<?php

namespace Aspectox\LaraJoom\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title'     => 'required|string|max:255',
            'alias'     => 'nullable|string|max:255|unique:larajoom_content,alias,' . ($id ?? ''),
            'catid'     => 'required|integer',
            'introtext' => 'nullable|string',
            'fulltext'  => 'nullable|string',
            'published' => 'nullable|boolean',
        ];
    }
}
