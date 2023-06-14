<?php

namespace App\Http\Controllers\API\question;

use App\Filters\QuestionFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\QuestionAttachmentRequest;
use App\Http\Requests\API\QuestionSettingsRequest;
use App\Http\Requests\API\QuestionSolutionRequest;
use App\Http\Requests\API\StoreQuestionRequest;
use App\Http\Requests\API\UpdateQuestionRequest;
use App\Models\ComprehensionPassage;
use App\Models\DifficultyLevel;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Skill;
use App\Models\Tag;
use App\Models\Topic;
use App\Repositories\QuestionRepository;
use App\Repositories\TagRepository;
use App\Transformers\API\QuestionTransformer;
use App\Transformers\API\SkillSearchTransformer;
use App\Transformers\API\TopicSearchTransformer;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuestionCrudController extends Controller
{
    private QuestionRepository $repository;

    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List all questions
     *
     * @param QuestionFilters $filters
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(QuestionFilters $filters)
    {
        $questions = Question::filter($filters)
            ->with(['questionType:id,name,code', 'section:sections.id,sections.name', 'skill:id,name', 'topic:id,name'])
            ->orderBy('id', 'desc')
            ->paginate(request()->perPage != null ? request()->perPage : 10);

        return response()->json([
            'questionTypes' => QuestionType::select(['id as value', 'code', 'name as text'])->active()->get(),
            'questions' => fractal($questions, new QuestionTransformer())->toArray(),
        ]);
    }

    /**
     * Create a question
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreQuestionRequest $request)
    {
        $question = new Question();
        $question->question = $request->question;
        $question->question_type_id = $request->question_type_id;
        $question->options = $request->options;
        $question->correct_answer = $request->question_type_id == 7 ? getBlankItems($request->question) :  $request->correct_answer;
        $question->skill_id = $request->skill_id;
        $question->preferences = $request->preferences;
        $question->save();

        return response()->json(['message' => 'Question created successfully!']);
    }

    /**
     * Show a question
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $question = Question::with(['questionType:id,name,code', 'difficultyLevel:id,name,code', 'skill:id,name'])->find($id);
        return response()->json(fractal($question, new QuestionTransformer())->toArray()['data'], 200);
    }

    /**
     * Update a question
     *
     * @param UpdateQuestionRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateQuestionRequest $request, $id)
    {
        $question = Question::findOrFail($id);
        $question->question = $request->question;
        $question->question_type_id = $request->question_type_id;
        $question->options = $request->options;
        $question->correct_answer = $request->correct_answer;
        request->question_type_id == 7 ? getBlankItems($request->question) : $request->correct_answer;
        $question->skill_id = $request->skill_id;
        $question->preferences = $request->preferences;
        $question->save();

        return response()->json(['message' => 'Question updated successfully!']);
    }

    /**
     * Delete a question
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json(['message' => 'Question deleted successfully!']);
    }

    /**
     * Attach a skill to a question
     *
     * @param QuestionAttachmentRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachSkill(QuestionAttachmentRequest $request, $id)
    {
        $question = Question::findOrFail($id);
        $question->skill_id = $request->skill_id;
        $question->save();

        return response()->json(['message' => 'Skill attached successfully!']);
    }

    /**
     * Set solution settings for a question
     *
     * @param QuestionSettingsRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSolutionSettings(QuestionSettingsRequest $request, $id)
    {
        $question = Question::findOrFail($id);
        $question->solution_settings = $request->solution_settings;
        $question->save();

        return response()->json(['message' => 'Solution settings updated successfully!']);
    }

    /**
     * Add a solution to a question
     *
     * @param QuestionSolutionRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addSolution(QuestionSolutionRequest $request, $id)
    {
        $question = Question::findOrFail($id);
        $question->solution = $request->solution;
        $question->save();

        return response()->json(['message' => 'Solution added successfully!']);
    }

    /**
     * Remove a solution from a question
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeSolution($id)
    {
        $question = Question::findOrFail($id);
        $question->solution = null;
        $question->save();

        return response()->json(['message' => 'Solution removed successfully!']);
    }

    /**
     * Search skills by name
     *
     * @param $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchSkills($name)
    {
        $skills = Skill::search($name)->get();
        return response()->json(fractal($skills, new SkillSearchTransformer())->toArray());
    }

    /**
     * Search topics by name
     *
     * @param $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchTopics($name)
    {
        $topics = Topic::search($name)->get();
        return response()->json(fractal($topics, new TopicSearchTransformer())->toArray());
    }
}

