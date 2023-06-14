<?php

namespace App\Http\Controllers\api\question;

use App\Http\Controllers\Controller;
use App\Models\QuestionType;
use Illuminate\Http\Request;

class QuestionTypeController extends Controller
{
    /**
     * List all question types
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questionTypes = QuestionType::active()->paginate(request()->perPage != null ? request()->perPage : 10);

        return response()->json($questionTypes);
    }
}
