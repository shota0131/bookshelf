<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            読書計画編集
        </h2>
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-8">

        <div class="max-w-6xl mx-auto px-4">

            <div class="max-w-md mx-auto">

                <div class="bg-white rounded-lg shadow-sm p-5">

                    <form
                        action="{{ route('reading-plans.update', $readingPlan) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PUT')

                        {{-- 書籍名 --}}
                        <div class="mb-4">
                            <div class="text-sm text-gray-500">
                                対象書籍
                            </div>

                            <div class="text-base font-semibold text-gray-800">
                                {{ $readingPlan->book->title }}
                            </div>
                        </div>

                        {{-- 現在の状態 --}}
                        <div class="mb-5">
                            <div class="text-sm text-gray-500">
                                現在の状態：
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    @if ($readingPlan->status === 'in_progress')
                                        読書中
                                    @elseif ($readingPlan->status === 'completed')
                                        完了
                                    @elseif ($readingPlan->status === 'expired')
                                        期限切れ
                                    @else
                                        {{ $readingPlan->status }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- 期日 --}}
                        <div class="mb-5">

                            <label
                                for="target_date"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                期日 <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="date"
                                name="target_date"
                                id="target_date"
                                value="{{ old('target_date', $readingPlan->target_date?->format('Y-m-d')) }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('target_date') border-red-500 @enderror"
                                required
                            >

                            @error('target_date')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- ボタン --}}
                        <div class="flex items-center justify-end gap-4 pt-2">

                            <a
                                href="{{ route('reading-plans.index') }}"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                キャンセル
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-md shadow-sm transition"
                            >
                                更新
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>