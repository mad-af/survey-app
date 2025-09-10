<?php

namespace App\Http\Controllers;

use App\Models\ResultCategory;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResultCategoryController extends Controller
{
    /**
     * Get result categories for a specific survey
     */
    public function getBySurvey(Survey $survey): JsonResponse
    {
        try {
            // Get result categories for the survey and its sections
            $surveyCategories = ResultCategory::with(['resultCategoryRules' => function ($query) {
                $query->orderBy('operation');
            }])
            ->where('owner_type', 'survey')
            ->where('owner_id', $survey->id)
            ->get();

            $sectionCategories = ResultCategory::with(['resultCategoryRules' => function ($query) {
                $query->orderBy('operation');
            }, 'surveySection'])
            ->where('owner_type', 'survey_section')
            ->whereIn('owner_id', $survey->surveySections->pluck('id'))
            ->get();

            // Combine and format the data
            $allCategories = $surveyCategories->concat($sectionCategories)->map(function ($category) {
                $data = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'owner_type' => $category->owner_type,
                    'owner_id' => $category->owner_id,
                    'rules' => $category->resultCategoryRules->map(function ($rule) {
                        return [
                            'id' => $rule->id,
                            'operation' => $rule->operation,
                            'title' => $rule->title,
                            'score' => $rule->score,
                            'color' => $rule->color,
                        ];
                    })
                ];
                
                // Add section order if it's a section category
                if ($category->owner_type === 'survey_section' && $category->surveySection) {
                    $data['section_order'] = $category->surveySection->order;
                }
                
                return $data;
            });

            return response()->json([
                'success' => true,
                'data' => $allCategories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch result categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get result categories for a specific survey section
     */
    public function getBySection(Request $request, $sectionId): JsonResponse
    {
        try {
            $categories = ResultCategory::with(['resultCategoryRules' => function ($query) {
                $query->orderBy('operation');
            }])
            ->where('owner_type', 'survey_section')
            ->where('owner_id', $sectionId)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'owner_type' => $category->owner_type,
                    'owner_id' => $category->owner_id,
                    'rules' => $category->resultCategoryRules->map(function ($rule) {
                        return [
                            'id' => $rule->id,
                            'operation' => $rule->operation,
                            'title' => $rule->title,
                            'score' => $rule->score,
                            'color' => $rule->color,
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch result categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}