<?php

namespace App\Http\Resources;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $scoreData = parent::toArray($request);

        $scoreData['grade_levels'] = $this->buildGradeLevels();

        $scoreData['total_group_a'] = $this->total_group_a;

        return $scoreData;
    }

    /**
     * Build grade levels array for all subjects with scores.
     */
    private function buildGradeLevels(): array
    {
        $subjects = Subject::active()->get()->keyBy('code');
        $gradeLevels = [];

        foreach ($subjects as $code => $subject) {
            $score = $this->resource->{$code};

            if (!is_null($score)) {
                $gradeLevels[$code] = [
                    'score' => $score,
                    'level' => Subject::getGradeLevelLabelByScore($score),
                    'display_name' => $subject->display_name
                ];
            }
        }

        return $gradeLevels;
    }
}
