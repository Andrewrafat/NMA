<?php

 namespace  App\Http\Controllers\Api\question;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportQuestionsRequest;
use App\Imports\QuestionsImport;
use App\Models\DifficultyLevel;
use App\Models\QuestionType;
use App\Models\Skill;
use Illuminate\Http\Request;

class QuestionImportController extends Controller
{
    /**
     * Import questions page
     *
     * @return \Illuminate\Http\Response
     */
    public function initiateImport()
    {
        $questionTypes = QuestionType::select(['id', 'code', 'name'])->active()->get();
        $difficultyLevels = DifficultyLevel::select(['id', 'code', 'name'])->active()->get();
        $sampleFileUrl = url('assets/sample-questions-upload.xlsx');

        $data = [
            'questionTypes' => $questionTypes,
            'difficultyLevels' => $difficultyLevels,
            'sampleFileUrl' => $sampleFileUrl
        ];

        return response()->json($data);
    }

    /**
     * Import questions from excel sheet
     *
     * @param ImportQuestionsRequest $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function import(ImportQuestionsRequest $request, $id)
    {
        if (config('qwiktest.demo_mode')) {
            return response()->json(['errorMessage' => 'Demo Mode! Files can\'t be imported.'], 400);
        }

        $file = $request->file('file');
        $skill = Skill::select(['id', 'name'])->findOrFail($id);

        $questionTypes = QuestionType::pluck('id', 'code');
        $difficultyLevels = DifficultyLevel::pluck('id', 'code');

        try {
            $import = new QuestionsImport($questionTypes, $difficultyLevels, $skill->id);
            $import->import($file);

            $importedCount = $import->getRowCount();

            return response()->json(['successMessage' => $importedCount . ' questions were imported successfully.'], 200);
        } catch (\Exception $exception) {
            return response()->json(['errorMessage' => 'Oops! Upload Failed. Please check all rows are entered accurately.'], 400);
        }
    }
}
