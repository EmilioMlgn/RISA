<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurriculumService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected CurriculumService $curriculumService
    ) {}

    /**
     * Muestra la vista principal de la retícula interactiva RISA.
     */
    public function index(Request $request): View
    {
        $userId = $request->query('student_id') ? (int) $request->query('student_id') : null;
        $student = $this->curriculumService->getActiveStudent($userId);

        $gridData = $this->curriculumService->getInteractiveGrid($student);
        $recommendationData = $this->curriculumService->getRecommendation($student);

        return view('risa.index', [
            'grid' => $gridData,
            'recommendation' => $recommendationData,
            'student' => $student,
        ]);
    }
}
