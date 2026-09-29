
@php
    use App\Enums\ReadingPlanStatus;
@endphp

<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            読書計画
        </h2>
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-8">

        <div class="max-w-6xl mx-auto px-4">

            <div class="flex items-center justify-between mb-4">

                {{-- ステータス絞り込み --}}
                <form
                    action="{{ route('reading-plans.index') }}"
                    method="GET"
                    class="flex items-center gap-2"
                >
                    <label
                        for="status"
                        class="text-sm text-gray-600"
                    >
                        状態:
                    </label>

                    <select
                        name="status"
                        id="status"
                        onchange="this.form.submit()"
                        class="border-gray-300 rounded-md shadow-sm text-sm"
                    >
                        <option value="">すべて</option>

                        <option
                            value="in_progress"
                            {{ request('status') === 'in_progress' ? 'selected' : '' }}
                        >
                            読書中
                        </option>

                        <option
                            value="completed"
                            {{ request('status') === 'completed' ? 'selected' : '' }}
                        >
                            完了
                        </option>

                        <option
                            value="expired"
                            {{ request('status') === 'expired' ? 'selected' : '' }}
                        >
                            期限切れ
                        </option>
                    </select>
                </form>

                {{-- 新規計画作成 --}}
                <a
                    href="{{ route('reading-plans.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-md"
                >
                    新規計画作成
                </a>

            </div>

            {{-- 成功メッセージ --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-md mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- 読書計画一覧 --}}
            <div class="bg-white rounded-lg shadow-sm">

                @forelse($readingPlans as $readingPlan)

                    <div class="border-b border-gray-200 last:border-b-0 p-5">

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                            {{-- 書籍情報 --}}
                            <div>

                                <h3 class="font-semibold text-gray-800">
                                    {{ $readingPlan->book->title }}
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $readingPlan->book->author }}
                                </p>

                                <p class="text-sm text-gray-500 mt-2">
                                    目標日:
                                    {{ $readingPlan->target_date?->format('Y年m月d日') }}
                                </p>

                            </div>

                            {{-- ステータス・操作 --}}
                            <div class="flex flex-wrap items-center gap-3">

                                {{-- ステータス表示 --}}
                                @if($readingPlan->status === ReadingPlanStatus::IN_PROGRESS)

                                    <span class="bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full">
                                        読書中
                                    </span>

                                @elseif($readingPlan->status === ReadingPlanStatus::COMPLETED)

                                    <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full">
                                        完了
                                    </span>

                                @elseif($readingPlan->status === ReadingPlanStatus::EXPIRED)

                                    <span class="bg-red-100 text-red-700 text-xs px-3 py-1 rounded-full">
                                        期限切れ
                                    </span>

                                @endif

                                {{-- 編集 --}}
                                <a
                                    href="{{ route('reading-plans.edit', $readingPlan) }}"
                                    class="text-sm text-blue-500 hover:text-blue-700"
                                >
                                    編集
                                </a>

                                {{-- 完了処理 --}}
                                @if($readingPlan->status === ReadingPlanStatus::IN_PROGRESS)
                                    <form
                                        action="{{ route('reading-plans.complete', $readingPlan) }}"
                                        method="POST"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="text-sm text-green-600 hover:text-green-800 font-medium"
                                        >
                                            完了にする
                                        </button>
                                    </form>
                                @endif

                                {{-- 削除処理 --}}
                                <form
                                    action="{{ route('reading-plans.destroy', $readingPlan) }}"
                                    method="POST"
                                    onsubmit="return confirm('この読書計画を削除しますか？')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-sm text-red-600 hover:text-red-800 font-medium"
                                    >
                                        削除
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>

                @empty

                    {{-- 該当する計画がない場合 --}}
                    <div class="p-5">
                        <p class="text-sm text-gray-500">
                            該当する読書計画はありません。
                        </p>
                    </div>

                @endforelse

            </div>

            {{-- ページネーション --}}
            @if($readingPlans->hasPages())
                <div class="mt-4">
                    {{ $readingPlans->links() }}
                </div>
            @endif

        </div>

    </div>

</x-app-layout>
