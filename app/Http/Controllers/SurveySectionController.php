<?php

namespace App\Http\Controllers;

use App\Models\SurveySection;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SurveySectionController extends Controller
{
    /**
     * Display a listing of survey sections.
     */
    public function index(Survey $survey): JsonResponse
    {
        try {
            $sections = $survey->sections()->with('questions.choices')->orderBy('order')->get();

            return response()->json([
                'success' => true,
                'data' => $sections,
                'message' => 'Survey sections retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve survey sections',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created survey section.
     */
    public function store(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'order' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle order assignment with proper reordering
            if (!$request->has('order') || $request->order === null) {
                // Auto-assign to end if not provided
                $maxOrder = SurveySection::where('survey_id', $survey->id)->max('order') ?? 0;
                $order = $maxOrder + 1;
                $section = SurveySection::create([
                    'survey_id' => $survey->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'order' => $order,
                ]);
            } else {
                // If order is specified, reorder existing sections
                $newOrder = $request->order;
                
                // Get all existing sections in this survey
                $existingSections = SurveySection::where('survey_id', $survey->id)
                    ->orderBy('order')
                    ->get();
                
                // Shift existing sections to make room for new section
                foreach ($existingSections as $existingSection) {
                    if ($existingSection->order >= $newOrder) {
                        $existingSection->update(['order' => $existingSection->order + 1]);
                    }
                }
                
                // Create new section with specified order
                $section = SurveySection::create([
                    'survey_id' => $survey->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'order' => $newOrder,
                ]);
                
                // Reorder all sections sequentially to avoid gaps
                $allSections = SurveySection::where('survey_id', $survey->id)
                    ->orderBy('order')
                    ->get();
                    
                foreach ($allSections as $index => $sectionToReorder) {
                    $sectionToReorder->update(['order' => $index + 1]);
                }
                
                // Reload the created section to get updated order
                $section = $section->fresh();
            }

            // Load relationships
            $section->load('questions.choices');

            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Survey section created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create survey section',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified survey section.
     */
    public function show(Survey $survey, SurveySection $section): JsonResponse
    {
        try {
            // Ensure section belongs to the survey
            if ($section->survey_id !== $survey->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section not found in this survey'
                ], 404);
            }

            $section->load('questions.choices');
            
            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Survey section retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve survey section',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified survey section.
     */
    public function update(Request $request, Survey $survey, SurveySection $section): JsonResponse
    {
        try {
            // Ensure section belongs to the survey
            if ($section->survey_id !== $survey->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section not found in this survey'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'order' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
            ];

            // Handle order update with proper reordering
            if ($request->has('order') && $request->order !== null) {
                $newOrder = $request->order;
                $currentOrder = $section->order;
                
                // Get total sections count to validate order range
                $totalSections = SurveySection::where('survey_id', $survey->id)->count();
                
                // Ensure order is within valid range
                $newOrder = max(1, min($newOrder, $totalSections));
                
                // If order is changing, reorder other sections
                if ($newOrder != $currentOrder) {
                    // First, update the current section's order to avoid conflicts
                    $section->update(['order' => $newOrder]);
                    
                    if ($newOrder > $currentOrder) {
                        // Moving down: shift sections between current and new position up
                        SurveySection::where('survey_id', $survey->id)
                            ->where('id', '!=', $section->id) // Exclude current section
                            ->where('order', '>', $currentOrder)
                            ->where('order', '<=', $newOrder)
                            ->decrement('order');
                    } else {
                        // Moving up: shift sections between new and current position down
                        SurveySection::where('survey_id', $survey->id)
                            ->where('id', '!=', $section->id) // Exclude current section
                            ->where('order', '>=', $newOrder)
                            ->where('order', '<', $currentOrder)
                            ->increment('order');
                    }
                    
                    // Normalize all orders to ensure sequential numbering (1, 2, 3, 4, ...)
                    $allSections = SurveySection::where('survey_id', $survey->id)
                        ->orderBy('order')
                        ->get();
                    
                    foreach ($allSections as $index => $sectionToNormalize) {
                        $normalizedOrder = $index + 1;
                        if ($sectionToNormalize->order != $normalizedOrder) {
                            $sectionToNormalize->update(['order' => $normalizedOrder]);
                        }
                    }
                    
                    // Remove order from updateData since we already updated it
                    unset($updateData['order']);
                }
            }

            $section->update($updateData);
            $section->load('questions.choices');

            return response()->json([
                'success' => true,
                'data' => $section,
                'message' => 'Survey section updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update survey section',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified survey section.
     */
    public function destroy(Survey $survey, SurveySection $section): JsonResponse
    {
        try {
            // Ensure section belongs to the survey
            if ($section->survey_id !== $survey->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section not found in this survey'
                ], 404);
            }

            $deletedOrder = $section->order;
            $section->delete();

            // Reorder remaining sections to fill the gap
            $remainingSections = SurveySection::where('survey_id', $survey->id)
                ->where('order', '>', $deletedOrder)
                ->orderBy('order')
                ->get();

            foreach ($remainingSections as $remainingSection) {
                $remainingSection->update(['order' => $remainingSection->order - 1]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Survey section deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete survey section',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}