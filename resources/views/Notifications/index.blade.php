<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            通知一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if($notifications->isEmpty())
                        <div class="py-8 text-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="mx-auto h-10 w-10 text-gray-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9a6 6 0 10-12 0v.75c0 2.056-.732 4.939-2.314 6.772a23.848 23.848 0 005.454 1.31m5.717 0a24.255 24.255 0 01-5.717 0m5.717 0a3 3 0 11-5.717 0"
                                />
                            </svg>

                            <p class="mt-3 text-sm text-gray-400">
                                通知はありません。
                            </p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-200">
                            @foreach($notifications as $notification)
                                <div
                                    class="py-4 flex items-center justify-between
                                    {{ is_null($notification->read_at) ? 'bg-gray-50' : '' }}"
                                >
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            @if(is_null($notification->read_at))
                                                <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                            @endif

                                            <p class="font-medium text-gray-800">
                                                {{ $notification->data['message'] ?? '新しい通知があります。' }}
                                            </p>
                                        </div>

                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $notification->created_at->format('Y年m月d日 H:i') }}
                                        </p>
                                    </div>

                                    @if(is_null($notification->read_at))
                                        <form
                                            action="{{ route('notifications.read', $notification->id) }}"
                                            method="POST"
                                            class="ml-4"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="text-sm text-blue-600 hover:text-blue-800 hover:underline"
                                            >
                                                既読にする
                                            </button>
                                        </form>
                                    @else
                                        <span class="ml-4 text-sm text-gray-400">
                                            既読
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>

