<?php

namespace App\Http\Controllers;

use App\Models\ResultCategoryRule;
use App\Models\ResultCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ResultCategoryRuleController extends Controller
{
    /**
     * Get all rules for a specific result category
     */
    public function index(ResultCategory $resultCategory): JsonResponse
    {
        try {
            $rules = $resultCategory->resultCategoryRules()->orderBy('operation')->get();
            
            return response()->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch result category rules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new result category rule
     */
    public function store(Request $request, ResultCategory $resultCategory): JsonResponse
    {
        try {
            $validated = $request->validate([
                'operation' => ['required', Rule::in(['lt', 'gt', 'else'])],
                'title' => 'required|string|max:255',
                'score' => 'required|numeric|min:0|max:100',
                'color' => ['required', Rule::in(['primary', 'secondary', 'accent', 'success', 'warning', 'error', 'info'])]
            ]);

            $rule = $resultCategory->resultCategoryRules()->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Result category rule created successfully',
                'data' => $rule
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create result category rule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing result category rule
     */
    public function update(Request $request, ResultCategory $resultCategory, ResultCategoryRule $rule): JsonResponse
    {
        try {
            // Ensure the rule belongs to the result category
            if ($rule->result_category_id !== $resultCategory->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rule does not belong to this result category'
                ], 403);
            }

            $validated = $request->validate([
                'operation' => ['required', Rule::in(['lt', 'gt', 'else'])],
                'title' => 'required|string|max:255',
                'score' => 'required|numeric|min:0|max:100',
                'color' => ['required', Rule::in(['primary', 'secondary', 'accent', 'success', 'warning', 'error', 'info'])]
            ]);

            $rule->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Result category rule updated successfully',
                'data' => $rule->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update result category rule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a result category rule
     */
    public function destroy(ResultCategory $resultCategory, ResultCategoryRule $rule): JsonResponse
    {
        try {
            // Ensure the rule belongs to the result category
            if ($rule->result_category_id !== $resultCategory->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rule does not belong to this result category'
                ], 403);
            }

            $rule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Result category rule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete result category rule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}