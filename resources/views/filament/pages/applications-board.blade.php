<x-filament-panels::page>
    <div
        class="grid gap-4 overflow-x-auto md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6"
        x-data="{ draggingId: null }"
    >
        @foreach ($columns as $column)
            <section
                class="min-w-64 rounded-xl bg-gray-50 p-3 dark:bg-gray-900"
                @dragover.prevent
                @drop.prevent="$wire.moveApplication(draggingId, {{ $column['stage']['id'] }})"
            >
                <header
                    class="mb-3 rounded-lg border-s-4 p-3"
                    style="border-color: {{ $column['stage']['color'] }}"
                >
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="font-semibold text-gray-950 dark:text-white">
                            {{ $column['stage']['name'] }}
                        </h2>
                        <span class="rounded-full bg-white px-2 py-1 text-xs dark:bg-gray-800">
                            {{ $column['count'] }}
                        </span>
                    </div>
                </header>

                <div class="space-y-3">
                    @foreach ($column['applications'] as $application)
                        <article
                            class="rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10"
                            @if ($application['can_drag'])
                                draggable="true"
                                @dragstart="draggingId = {{ $application['id'] }}"
                            @else
                                draggable="false"
                            @endif
                        >
                            <a href="{{ $this->applicationUrl($application['id']) }}" class="block">
                                <div class="font-medium text-gray-950 dark:text-white">
                                    {{ $application['display_name'] }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $application['reference_code'] }}
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-300">
                                    @if ($application['ai_score'] !== null)
                                        <span>AI {{ $application['ai_score'] }}</span>
                                    @endif
                                    @if ($application['rating'] !== null)
                                        <span>Rating {{ $application['rating'] }}</span>
                                    @endif
                                    <span>{{ $application['applied_at'] }}</span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
