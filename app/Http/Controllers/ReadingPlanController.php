<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧
     */
    public function index(Request $request): View
    {
        $query = ReadingPlan::with('book')
            ->where('user_id', auth()->id());

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->value
            );
        }

        $readingPlans = $query
            ->orderBy('target_date')
            ->paginate(10)
            ->withQueryString();

        return view(
            'reading-plans.index',
            compact('readingPlans')
        );
    }

    /**
     * 読書計画作成画面
     */
    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view(
            'reading-plans.create',
            compact('books')
        );
    }

    /**
     * 読書計画登録
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse 
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with(
                'success',
                '読書計画を作成しました。'
            );
    }

    /**
     * 読書計画編集画面
     */
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize(
            'update',
            $plan
        );

        return view(
            'reading-plans.edit',
            [
                'readingPlan' => $plan,
            ]
        );
    }

    /**
     * 読書計画更新
     */
    public function update(
        UpdateReadingPlanRequest $request,
        ReadingPlan $plan
    ): RedirectResponse {
        $this->authorize(
            'update',
            $plan
        );

        $validated = $request->validated();

        $plan->update([
            'target_date' => $validated['target_date'],
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with(
                'success',
                '読書計画を更新しました。'
            );
    }

    /**
     * 読書計画削除
     */
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize(
            'delete',
            $plan
        );

        $plan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with(
                'success',
                '読書計画を削除しました。'
            );
    }

}

