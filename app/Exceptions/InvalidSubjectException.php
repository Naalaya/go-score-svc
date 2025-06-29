<?php

namespace App\Exceptions;

use Exception;

class InvalidSubjectException extends Exception
{
    public function __construct(string $subjectCode)
    {
        parent::__construct("Invalid subject code: {$subjectCode}");
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'error' => 'INVALID_SUBJECT',
            'message' => $this->getMessage(),
        ], 400);
    }
}
