<?php

namespace App\Exceptions;

use Exception;

class InvalidScoreException extends Exception
{
    public function __construct(float $score)
    {
        parent::__construct("Invalid score: {$score}. Must be 0-10.");
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'error' => 'INVALID_SCORE',
            'message' => $this->getMessage(),
        ], 400);
    }
}
