<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\SurveySection;
use App\Models\Choice;
use App\Enums\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * Display a listing of questions for a section.
     */
    public function index(SurveySection $section): JsonResponse
    {
        try {
            $questions = $section->questions()->with('choices')->orderBy('order')->get();

            return response()->json([
                'success' => true,
                'data' => $questions,
                'message' => 'Questions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created question.
     */
    public function store(Request $request, SurveySection $section): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'text' => 'required|string',
                'type' => 'required|string|in:short_text,long_text,single_choice,multiple_choice,number,date',
                'required' => 'boolean',
                'order' => 'nullable|integer|min:1',
                'score_weight' => 'nullable|numeric|min:0',
                'choices' => 'nullable|array',
                'choices.*.label' => 'required_with:choices|string',
                'choices.*.value' => 'nullable|string',
                'choices.*.score' => 'nullable|numeric',
                'choices.*.order' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Handle order logic
            if ($request->has('order') && $request->order !== null) {
                $requestedOrder = $request->order;
                $maxOrder = $section->questions()->max('order') ?? 0;
                
                // Validate order range
                if ($requestedOrder > $maxOrder + 1) {
                    $requestedOrder = $maxOrder + 1;
                }
                
                // Shift existing questions to make room
                $section->questions()
                    ->where('order', '>=', $requestedOrder)
                    ->increment('order');
                
                $order = $requestedOrder;
            } else {
                // Auto-assign to the end
                $order = ($section->questions()->max('order') ?? 0) + 1;
            }

            $question = Question::create([
                'section_id' => $section->id,
                'text' => $request->text,
                'type' => QuestionType::from($request->type),
                'required' => $request->boolean('required', false),
                'order' => $order,
                'score_weight' => $request->score_weight ?? 0,
            ]);
            
            // Normalize order sequence to ensure no gaps
            $questions = $section->questions()->orderBy('order')->get();
            foreach ($questions as $index => $q) {
                $q->update(['order' => $index + 1]);
            }

            // Create choices if provided
            if ($request->has('choices') && is_array($request->choices)) {
                foreach ($request->choices as $index => $choiceData) {
                    Choice::create([
                        'question_id' => $question->id,
                        'label' => $choiceData['label'],
                        'value' => $choiceData['value'] ?? $choiceData['label'],
                        'score' => $choiceData['score'] ?? 0,
                        'order' => $choiceData['order'] ?? ($index + 1),
                    ]);
                }
            }

            DB::commit();

            // Load relationships
            $question->load('choices');

            return response()->json([
                'success' => true,
                'data' => $question,
                'message' => 'Question created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified question.
     */
    public function show(SurveySection $section, Question $question): JsonResponse
    {
        try {
            // Ensure question belongs to the section
            if ($question->section_id !== $section->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found in this section'
                ], 404);
            }

            $question->load('choices');
            
            return response()->json([
                'success' => true,
                'data' => $question,
                'message' => 'Question retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified question.
     */
    public function update(Request $request, SurveySection $section, Question $question): JsonResponse
    {
        try {
            // Ensure question belongs to the section
            if ($question->section_id !== $section->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found in this section'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'text' => 'required|string',
                'type' => 'required|string|in:short_text,long_text,single_choice,multiple_choice,number,date',
                'required' => 'boolean',
                'order' => 'nullable|integer|min:1',
                'score_weight' => 'nullable|numeric|min:0',
                'choices' => 'nullable|array',
                'choices.*.id' => 'nullable|integer|exists:choices,id',
                'choices.*.label' => 'required_with:choices|string',
                'choices.*.value' => 'nullable|string',
                'choices.*.score' => 'nullable|numeric',
                'choices.*.order' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $updateData = [
                'text' => $request->text,
                'type' => QuestionType::from($request->type),
                'required' => $request->boolean('required', false),
                'score_weight' => $request->score_weight ?? 0,
            ];

            // Handle order update logic
            if ($request->has('order') && $request->order !== null && $request->order != $question->order) {
                $newOrder = $request->order;
                $oldOrder = $question->order;
                $maxOrder = $section->questions()->max('order');
                
                // Validate order range
                if ($newOrder > $maxOrder) {
                    $newOrder = $maxOrder;
                }
                
                // Update current question's order first to avoid conflicts
                $question->update(['order' => $newOrder]);
                
                if ($newOrder < $oldOrder) {
                    // Moving up: increment orders of questions between new and old position
                    $section->questions()
                        ->where('id', '!=', $question->id)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                } else {
                    // Moving down: decrement orders of questions between old and new position
                    $section->questions()
                        ->where('id', '!=', $question->id)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                }
                
                // Normalize order sequence to ensure no gaps or duplicates
                $questions = $section->questions()->orderBy('order')->get();
                foreach ($questions as $index => $q) {
                    $q->update(['order' => $index + 1]);
                }
            } else {
                $question->update($updateData);
            }

            // Update choices if provided
            if ($request->has('choices') && is_array($request->choices)) {
                // Get existing choice IDs
                $existingChoiceIds = $question->choices()->pluck('id')->toArray();
                $updatedChoiceIds = [];

                foreach ($request->choices as $index => $choiceData) {
                    if (isset($choiceData['id']) && in_array($choiceData['id'], $existingChoiceIds)) {
                        // Update existing choice
                        $choice = Choice::find($choiceData['id']);
                        $choice->update([
                            'label' => $choiceData['label'],
                            'value' => $choiceData['value'] ?? $choiceData['label'],
                            'score' => $choiceData['score'] ?? 0,
                            'order' => $choiceData['order'] ?? ($index + 1),
                        ]);
                        $updatedChoiceIds[] = $choiceData['id'];
                    } else {
                        // Create new choice
                        $choice = Choice::create([
                            'question_id' => $question->id,
                            'label' => $choiceData['label'],
                            'value' => $choiceData['value'] ?? $choiceData['label'],
                            'score' => $choiceData['score'] ?? 0,
                            'order' => $choiceData['order'] ?? ($index + 1),
                        ]);
                        $updatedChoiceIds[] = $choice->id;
                    }
                }

                // Delete choices that are no longer present
                $choicesToDelete = array_diff($existingChoiceIds, $updatedChoiceIds);
                if (!empty($choicesToDelete)) {
                    Choice::whereIn('id', $choicesToDelete)->delete();
                }
            }

            DB::commit();

            $question->load('choices');

            return response()->json([
                'success' => true,
                'data' => $question,
                'message' => 'Question updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified question.
     */
    public function destroy(SurveySection $section, Question $question): JsonResponse
    {
        try {
            // Ensure question belongs to the section
            if ($question->section_id !== $section->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found in this section'
                ], 404);
            }

            DB::beginTransaction();
            
            $deletedOrder = $question->order;
            $question->delete();
            
            // Reorder remaining questions to fill the gap
            $section->questions()
                ->where('order', '>', $deletedOrder)
                ->decrement('order');
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}