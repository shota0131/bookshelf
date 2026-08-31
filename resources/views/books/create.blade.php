<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('書籍の登録') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-6">
                <div class="flex items-center mb-2">
                    <span class="text-blue-600 mr-2">▣</span>
                    <h3 class="font-semibold text-blue-800">
                        ISBNから書籍情報を自動入力
                    </h3>
                </div>

                <p class="text-xs text-gray-600 mb-3">
                    13桁のISBNを入力すると、Google Books APIから書籍情報を取得してフォームを自動入力します。
                </p>

                <div class="flex gap-2">
                    <input
                        type="text"
                        id="isbn-search"
                        maxlength="13"
                        inputmode="numeric"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full"
                        placeholder="例: 9784101010014"
                    >

                    <button
                        type="button"
                        id="isbn-search-button"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold px-5 rounded-md whitespace-nowrap"
                    >
                        検索
                    </button>
                </div>

                <p id="isbn-search-message" class="text-sm mt-2 hidden"></p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form action="{{ route('books.store') }}" method="POST" novalidate>

                        @include('books._form')

                        <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                            <a
                                href="{{ route('books.index') }}"
                                class="text-gray-600 hover:text-gray-900 mr-4"
                            >
                                キャンセル
                            </a>

                            <button
                                type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded"
                            >
                                登録
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const searchInput = document.getElementById('isbn-search');
            const searchButton = document.getElementById('isbn-search-button');
            const message = document.getElementById('isbn-search-message');

            searchButton.addEventListener('click', async function () {

                const isbn = searchInput.value.trim();

                message.classList.add('hidden');
                message.textContent = '';

                if (!/^\d{13}$/.test(isbn)) {
                    message.textContent = 'ISBNは13桁の数字で入力してください。';
                    message.classList.remove('hidden');
                    message.classList.add('text-red-600');
                    return;
                }

                searchButton.disabled = true;
                searchButton.textContent = '検索中...';

                try {

                    const response = await fetch(
                        `{{ route('books.isbn-search') }}?isbn=${isbn}`
                    );

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || '書籍情報の取得に失敗しました。');
                    }

                    document.getElementById('title').value = data.title || '';
                    document.getElementById('author').value = data.author || '';
                    document.getElementById('isbn').value = data.isbn || '';
                    document.getElementById('published_date').value = data.published_date || '';
                    document.getElementById('description').value = data.description || '';
                    document.getElementById('image_url').value = data.image_url || '';

                    message.textContent = '書籍情報を取得しました。';
                    message.classList.remove('hidden', 'text-red-600');
                    message.classList.add('text-green-600');

                } catch (error) {

                    message.textContent = error.message;
                    message.classList.remove('hidden', 'text-green-600');
                    message.classList.add('text-red-600');

                } finally {

                    searchButton.disabled = false;
                    searchButton.textContent = '検索';
                }
            });
        });
    </script>

</x-app-layout>