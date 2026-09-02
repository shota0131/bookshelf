<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            マイ読書レポート
        </h2>
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-8">

        <div class="max-w-6xl mx-auto px-4">


            <div class="bg-white rounded-lg shadow-sm p-5 mb-4">

                <h3 class="font-bold text-sm text-gray-800 mb-4">
                    基本統計
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                    <div class="border border-gray-200 rounded-lg p-5 text-center">

                        <div class="text-2xl font-bold text-blue-600">
                            {{ $totalReviews }}
                        </div>

                        <div class="text-xs text-gray-500 mt-2">
                            総レビュー数
                        </div>

                    </div>


                    <div class="border border-gray-200 rounded-lg p-5 text-center">

                        <div class="text-2xl font-bold text-green-600">
                            {{ $readBooks }}
                        </div>

                        <div class="text-xs text-gray-500 mt-2">
                            読了冊数
                        </div>

                    </div>


                    <div class="border border-gray-200 rounded-lg p-5 text-center">

                        <div class="text-2xl font-bold text-yellow-500">
                            {{ $averageRating !== null ? number_format($averageRating, 1) : '0.0' }}
                        </div>

                        <div class="text-xs text-gray-500 mt-2">
                            平均評価
                        </div>

                    </div>

                </div>

            </div>



            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">


                <div class="bg-white rounded-lg shadow-sm p-5">

                    <h3 class="font-bold text-sm text-gray-800 mb-4">
                        評価分布
                    </h3>

                    @php
                        $maxRatingCount = max($ratingDistribution ?: [1]);
                    @endphp

                    <div class="space-y-2">

                        @for($rating = 1; $rating <= 5; $rating++)

                            @php
                                $count = $ratingDistribution[$rating] ?? 0;

                                $width = $maxRatingCount > 0
                                    ? ($count / $maxRatingCount) * 100
                                    : 0;
                            @endphp

                            <div class="flex items-center gap-2">

                                <div class="w-10 text-xs text-yellow-500 whitespace-nowrap">
                                    {{ str_repeat('★', $rating) }}
                                </div>

                                <div class="flex-1 bg-gray-200 rounded-full h-2">

                                    <div
                                        class="bg-yellow-400 h-2 rounded-full"
                                        style="width: {{ $width }}%"
                                    ></div>

                                </div>

                                <div class="w-8 text-right text-xs text-gray-500">
                                    {{ $count }}件
                                </div>

                            </div>

                        @endfor

                    </div>

                </div>

                <div class="bg-white rounded-lg shadow-sm p-5">

                    <h3 class="font-bold text-sm text-gray-800 mb-4">
                        高評価書籍 TOP5
                    </h3>

                    <div class="space-y-2">

                        @forelse($topBooks as $index => $review)

                            <a
                                href="{{ route('books.show', $review->book) }}"
                                class="flex items-center border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition"
                            >

                                <div
                                    class="
                                        w-7 h-7 rounded-full
                                        flex items-center justify-center
                                        text-xs font-bold
                                        mr-3
                                        {{ $index === 0
                                            ? 'bg-yellow-400 text-white'
                                            : 'bg-gray-300 text-white' }}
                                    "
                                >
                                    {{ $index + 1 }}
                                </div>

                                <div class="flex-1 min-w-0">

                                    <div class="text-xs font-medium text-gray-800 truncate">
                                        {{ $review->book->title }}
                                    </div>

                                    <div class="text-[10px] text-gray-400 truncate">
                                        {{ $review->book->author }}
                                    </div>

                                </div>


                                <div class="text-xs text-yellow-500 whitespace-nowrap">
                                    {{ str_repeat('★', $review->rating) }}
                                </div>

                            </a>

                        @empty

                            <p class="text-sm text-gray-500">
                                まだレビューがありません。
                            </p>

                        @endforelse

                    </div>

                </div>

            </div>



            <div class="bg-white rounded-lg shadow-sm p-5">

                <h3 class="font-bold text-sm text-gray-800">
                    ジャンル別評価傾向 TOP5
                </h3>

                <p class="text-xs text-gray-400 mt-1 mb-4">
                    どのジャンルを高く評価する傾向があるかを表示
                </p>


                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                    @forelse($genreStats as $index => $genre)

                        <div
                            class="border border-gray-200 rounded-lg p-3 flex items-center"
                        >

                            <div
                                class="
                                    w-7 h-7 rounded-full
                                    flex items-center justify-center
                                    text-xs font-bold
                                    mr-3
                                    {{ $index === 0
                                        ? 'bg-yellow-400 text-white'
                                        : 'bg-gray-300 text-white' }}
                                "
                            >
                                {{ $index + 1 }}
                            </div>


                            <div class="flex-1">

                                <div class="text-xs font-medium text-gray-700">
                                    {{ $genre['name'] }}
                                </div>

                                <div class="text-[10px] text-gray-400">
                                    {{ $genre['review_count'] }}件のレビュー
                                </div>

                            </div>


                            <div class="text-right">

                                <div class="text-sm font-bold text-yellow-500">
                                    {{ number_format($genre['average_rating'], 1) }}
                                </div>

                                <div class="text-[9px] text-gray-400">
                                    平均評価
                                </div>

                            </div>

                        </div>

                    @empty

                        <p class="text-sm text-gray-500">
                            まだレビューがありません。
                        </p>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</x-app-layout>