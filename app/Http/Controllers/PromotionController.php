<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceSeniorRequest;
use App\Http\Requests\StudentPromoteRequest;
use App\Models\MyClass;
use App\Models\Pathway;
use App\Models\Promotion;
use App\Models\User;
use App\Services\Cbc\PlacementService;
use App\Services\Student\StudentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public $student;

    public function __construct(StudentService $student)
    {
        $this->student = $student;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Promotion::class);

        return view('pages.student.promotion.index');
    }

    /**
     * promote view.
     */
    public function promoteView(): View
    {
        $this->authorize('promote', Promotion::class);

        return view('pages.student.promotion.promote');
    }

    /**
     * Promote student.
     */
    public function promote(StudentPromoteRequest $request): RedirectResponse
    {
        $this->authorize('promote', Promotion::class);
        $data = collect($request->except('_token'));
        $this->student->promoteStudents($data);

        return back()->with('success', 'Students Promoted Successfully');
    }

    /**
     * Senior placement view (Grade 9 into a pathway).
     */
    public function placeSeniorView(): View
    {
        $this->authorize('promote', Promotion::class);

        return view('pages.student.promotion.place-senior');
    }

    /**
     * Place a learner into Senior School.
     */
    public function placeSenior(PlaceSeniorRequest $request, PlacementService $placement): RedirectResponse
    {
        $this->authorize('promote', Promotion::class);
        $placement->placeToSenior(
            User::findOrFail($request->input('student_id')),
            Pathway::findOrFail($request->input('pathway_id')),
            MyClass::findOrFail($request->input('senior_class_id')),
            $request->input('electives', []),
            $request->input('kjsea_score') !== null ? (float) $request->input('kjsea_score') : null,
            $request->input('senior_section_id'),
            current_school()->academic_year_id,
        );

        return back()->with('success', 'Learner Placed Successfully');
    }

    /**
     * Reset promotion.
     */
    public function resetPromotion(Promotion $promotion): RedirectResponse
    {
        $this->authorize('reset', Promotion::class);
        $this->student->resetPromotion($promotion);

        return back()->with('success', 'Promotion Reset Successfully');
    }

    /**
     * Display the specified resource.
     *
     *
     * @throws AuthorizationException
     */
    public function show(Promotion $promotion): View
    {
        $this->authorize('view', $promotion);

        return view('pages.student.promotion.show', compact('promotion'));
    }
}
