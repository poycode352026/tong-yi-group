<?php

namespace App\Http\Requests\Admin;

class SaveCoverageRequest extends SaveLearningResourceRequest
{
    protected function prepareForValidation(): void
    {
        $this->route()->setParameter('kind', 'frameworks');
        parent::prepareForValidation();
    }
}
