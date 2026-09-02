<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            新規読書計画作成
        </h2>
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-8">

        <div class="max-w-6xl mx-auto px-4">

            <div class="max-w-md mx-auto">

                <div class="bg-white rounded-lg shadow-sm p-5">

                    <form
                        action="{{ route('reading-plans.store') }}"
                        method="POST"
                    >
                        @csrf

                        {{-- 書籍 --}}
                        <div class="mb-5">

                            <label
                                for="book_id"
                                class="block text-xs font-medium text-gray-700 mb-1"
                            >
                                書籍 <span class="text-red-500">*</span>
                            </label>

                            <select
                                name="book_id"
                                id="book_id"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       @error('book_id') border-red-500 @enderror"
                            >
                                <option value="">
                                    -- 書籍を選択 --
                                </option>

                                @foreach($books as $book)
                                    <option
                                        value="{{ $book->id }}"
                                        {{ old('book_id') == $book->id ? 'selected' : '' }}
                                    >
                                        {{ $book->title }}
                                    </option>
                                @endforeach

                            </select>

                            @error('book_id')
                                <p class="text-xs text-red-600 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- 期日 --}}
                        <div class="mb-5">

                            <label
                                for="target_date"
                                class="block text-xs font-medium text-gray-700 mb-1"
                            >
                                期日 <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="date"
                                name="target_date"
                                id="target_date"
                                value="{{ old('target_date') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       @error('target_date') border-red-500 @enderror"
                            >

                            @error('target_date')
                                <p class="text-xs text-red-600 mt-1">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- ボタン --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">

                            <a
                                href="{{ route('reading-plans.index') }}"
                                class="text-sm text-gray-500 hover:text-gray-700"
                            >
                                キャンセル
                            </a>

                            <button
                                type="submit"
                                class="bg-blue-500 hover:bg-blue-600
                                       text-white text-sm font-semibold
                                       px-4 py-2 rounded-md
                                       transition"
                            >
                                登録
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>