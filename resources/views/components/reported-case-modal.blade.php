<div x-data x-cloak @keydown.escape.window="$store.reportedCase.close()">
    <div x-show="$store.reportedCase.isOpen"
        x-transition.opacity
        @click="$store.reportedCase.close()"
        class="fixed inset-0 z-[110] backdrop-blur-lg"
        style="background-color: rgba(92, 45, 98, 0.8);">
    </div>

    <div x-show="$store.reportedCase.isOpen"
        x-transition.opacity
        @click.self="$store.reportedCase.close()"
        class="fixed inset-0 z-[110] flex items-start justify-center p-4 pt-24 overflow-y-auto">
        <div @click.stop
            class="relative w-[92vw] max-w-[600px] p-6 md:p-12"
            style="min-height:1323px;background:#FFFFFF;border-radius:24px;box-sizing:border-box;">

            <button type="button" @click="$store.reportedCase.close()"
                style="position:absolute;right:35px;top:35px;width:35px;height:35px;border-radius:50%;background:#DD3888;border:none;display:flex;align-items:center;justify-content:center;">
                <x-icons name="cross" style="width:12px;height:12px;color:#FFFFFF;" />
            </button>

            <template x-if="$store.reportedCase.data">
                <div>
                    <div class="flex justify-center">
                        <x-icons name="TriangleAlert" style="width:40px;height:40px;color:#DD3888;" />
                    </div>

                    <h2 class="text-center mt-4" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:36px;color:#5C2D62;">
                        {{ __('front.account.member.block_reason') }}
                    </h2>
                    <h3 class="text-center mt-1" style="font-family:'Poppins',sans-serif;font-weight:700;font-size:24px;color:#DD3888;" x-text="$store.reportedCase.data.name"></h3>

                    <div class="mt-1" style="width:190px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;margin-left:auto;margin-right:auto;">
                        <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;" x-text="$store.reportedCase.data.location"></span>
                    </div>

                    <div class="flex gap-3 mt-1 justify-center">
                        <div style="width:91px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                            <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;" x-text="$store.reportedCase.data.height + ' cm'"></span>
                        </div>
                        <div style="width:91px;height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                            <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;color:#505050;" x-text="$store.reportedCase.data.age + ' {{ __('front.profiles.list.years') }}'"></span>
                        </div>
                    </div>

                    <div class="mt-4 mx-auto p-4 md:p-5" style="width:510px;max-width:100%;background:#F2F2F2;border-radius:15px;box-sizing:border-box;">
                        <div class="flex flex-col md:flex-row items-center md:items-stretch gap-4">
                            <div class="relative flex-shrink-0 w-[180px] h-[227px] md:w-[210px] md:h-[265px]" style="border-radius:15px;overflow:hidden;">
                                <img :src="$store.reportedCase.data.image" alt="" class="w-full h-full object-cover" />
                                <div class="absolute left-0 right-0 bottom-3 flex justify-center" style="gap:3px;">
                                    <template x-for="n in 5" :key="n">
                                        <span class="w-2.5 h-2.5 rounded-full bg-white flex items-center justify-center" style="box-shadow: 0 0 0 1px rgba(0,0,0,0.04);">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="n === 1 ? 'bg-[#DD3888]' : 'bg-transparent'"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <div class="w-full md:flex-1 space-y-2">
                                <template x-for="allegation in $store.reportedCase.data.allegations" :key="allegation">
                                    <div style="height:30px;border-radius:8px;background:#FFFFFF;display:flex;align-items:center;justify-content:center;">
                                        <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:11px;color:#505050;" x-text="allegation"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <p class="mt-4" style="font-family:'Poppins',sans-serif;font-weight:400;font-size:14px;color:#505050;" x-text="$store.reportedCase.data.reason"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('reportedCase', {
            isOpen: false,
            data: null,
            open(payload) {
                this.data = payload;
                this.isOpen = true;
            },
            close() {
                this.isOpen = false;
            }
        });
    });
</script>
