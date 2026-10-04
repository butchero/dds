<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { computed, ref } from 'vue';
import CookieConsent from '../Components/CookieConsent.vue';

const page = usePage();
const site = computed(() => page.props.site);
const auth = computed(() => page.props.auth);
const menuOpen = ref(false);
const search = useForm({ s: '' });

function submitSearch() {
    search.get('/cauta', { preserveState: true });
}

function isActive(item) {
    const path = page.url.split('?')[0];
    if (item.url === '/') {
        return path === '/';
    }

    return path === item.url || path.startsWith(`${item.url}/`);
}
</script>

<template>
    <div id="top" class="mx-auto w-full max-w-7xl bg-white px-3 pb-[10px] sm:px-4 lg:px-6">
        <div class="pr-5 pt-[5px] text-right">
            <p class="m-0 text-[9px] font-bold leading-[10px] text-[#b7b7b7]">Centrale termice Constanta - aparate aer conditionat Constanta</p>
        </div>

        <div class="mt-2 flex flex-col items-center gap-3 sm:flex-row sm:items-end sm:justify-between">
            <Link href="/" class="mb-0 block shrink-0 sm:mb-2">
                <img :src="'/images/logo.svg'" width="155" height="79" alt="DDS Services Group">
            </Link>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded border border-[#d5d5d5] text-portocaliu sm:hidden" aria-label="Meniu" @click="menuOpen = !menuOpen">
                <span class="block h-0.5 w-4 bg-current shadow-[0_-5px_0_currentColor,0_5px_0_currentColor]"></span>
            </button>
            <nav v-if="site.menuPosition === 'top'" class="relative z-20 -mb-px hidden max-w-full justify-center font-arial sm:flex sm:justify-end">
                <Link v-for="(item, index) in site.menu" :key="item.url + item.label" :href="item.url" class="tab" :class="{ 'tab-activ': isActive(item), 'mr-[10px]': index === site.menu.length - 1 }">{{ item.label }}</Link>
            </nav>
        </div>

        <div v-if="menuOpen" class="mt-2 flex flex-col border border-marginelight font-arial sm:hidden">
            <Link v-for="item in site.menu" :key="item.url" :href="item.url" class="border-b border-marginelight px-3 py-2 text-portocaliu no-underline" @click="menuOpen = false">{{ item.label }}</Link>
            <Link v-if="auth.user" href="/cont" class="px-3 py-2 text-portocaliu no-underline">Contul meu</Link>
            <Link v-else href="/cont/autentificare" class="px-3 py-2 text-portocaliu no-underline">Autentificare</Link>
        </div>

        <div class="bara-cautare flex min-h-[35px] flex-col gap-2 px-3 py-2 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-4 sm:py-[5px]">
            <form class="relative z-10 flex items-center gap-2" @submit.prevent="submitSearch">
                <span class="font-arial text-[11px] font-bold tracking-[1px] text-portocaliu">Cauta:</span>
                <input v-model="search.s" type="text" name="s" maxlength="30" aria-label="Cauta in site" class="h-[20px] w-[117px] max-w-full border border-[#a4a4a4] px-[3px] text-[11px] outline-none">
                <button type="submit" aria-label="Cauta" class="inline-flex h-[20px] w-[22px] shrink-0 items-center justify-center rounded-[2px] border border-[#a4a4a4] bg-[#f4f4f4] text-[#444444] hover:bg-white">
                    <Search class="h-[13px] w-[13px]" />
                </button>
            </form>
            <div class="relative z-10 flex flex-wrap items-center gap-x-[10px] gap-y-1 font-arial text-[11px] tracking-[0.6px] text-portocaliu">
                <Link href="/servicii/montaj-centrala" class="hover:underline">Care centrala termica mi se potriveste ?</Link>
                <span class="hidden h-[18px] w-px bg-[#dcdcdc] sm:block"></span>
                <Link href="/servicii/revizie-centrala" class="hover:underline">Garantie si service</Link>
                <span class="hidden h-[18px] w-px bg-[#dcdcdc] sm:block"></span>
                <Link href="/servicii/contact" class="hover:underline">Intrebari frecvente</Link>
            </div>
        </div>

        <div class="flex flex-col items-start gap-4 pt-3 md:flex-row md:flex-wrap lg:flex-nowrap lg:gap-0 lg:pt-[5px]">
            <aside class="order-2 w-full shrink-0 md:w-[calc(50%-8px)] lg:order-1 lg:w-60 lg:pr-[5px]">
                <nav v-if="site.menuPosition === 'left'" class="mb-[5px] flex">
                    <div class="w-[6px] shrink-0 pt-[63px]">
                        <div class="h-[84px] w-[6px] rounded-l-[5px] bg-[#fe5013]"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex h-[37px] items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                            <span class="font-arial text-[12px] text-[#ff4e11]">MENIU</span>
                        </div>
                        <div class="border-x border-marginelight pt-[7px]">
                            <template v-for="(item, index) in site.menu" :key="item.url + item.label">
                                <div class="flex px-[10px]" :class="index === site.menu.length - 1 ? 'pb-[7px]' : ''">
                                    <span class="w-[18px] shrink-0 pt-[2px]"><img :src="'/images/bullet-orange.svg'" width="13" height="12" alt=""></span>
                                    <Link :href="item.url" class="text-[12px] font-bold leading-[14px] text-meniu no-underline hover:text-albastru">{{ item.label }}</Link>
                                </div>
                                <div v-if="index < site.menu.length - 1" class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                            </template>
                        </div>
                        <div class="h-[8px] rounded-b-[7px] border border-t-0 border-marginelight"></div>
                    </div>
                </nav>

                <div v-if="site.hasProducts" class="flex">
                    <div class="w-[6px] shrink-0 pt-[63px]">
                        <div class="h-[129px] w-[6px] rounded-l-[5px] bg-[#fe5013]"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex h-[37px] items-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                            <img :src="'/images/flame.svg'" width="16" height="20" class="ml-[12px]" alt="">
                            <span class="ml-[6px] font-arial text-[12px] text-[#ff4e11]">CATEGORII</span>
                        </div>
                        <div class="border-x border-marginelight pt-[7px]">
                            <template v-for="(category, index) in site.productCategories" :key="category.id">
                                <div class="flex px-[10px]">
                                    <span class="w-[18px] shrink-0 pt-[2px]"><img :src="'/images/bullet-orange.svg'" width="13" height="12" alt=""></span>
                                    <Link :href="`/categorie/${category.slug}`" class="text-[12px] font-bold leading-[14px] text-meniu no-underline hover:text-albastru">{{ category.name }}</Link>
                                </div>
                                <div class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                <template v-for="(child, childIndex) in category.children" :key="child.id">
                                    <div class="flex px-[10px]" :class="index === site.productCategories.length - 1 && childIndex === category.children.length - 1 ? 'pb-[7px]' : ''">
                                        <span class="w-[25px] shrink-0"></span>
                                        <Link :href="`/categorie/${child.slug}`" class="leading-[13px] text-submeniu no-underline hover:text-albastru hover:underline">{{ child.name }}</Link>
                                    </div>
                                    <div v-if="!(index === site.productCategories.length - 1 && childIndex === category.children.length - 1)" class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                                </template>
                            </template>
                        </div>
                        <div class="h-[8px] rounded-b-[7px] border border-t-0 border-marginelight"></div>
                    </div>
                </div>

                <div v-for="category in site.serviceCategories" :key="category.id" class="mt-[5px] flex">
                    <div class="w-[6px] shrink-0 pt-[63px]">
                        <div class="h-[84px] w-[6px] rounded-l-[5px]" :class="site.hasProducts ? 'bg-[#8c8c8c]' : 'bg-[#fe5013]'"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex h-[37px] items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                            <span class="font-arial text-[12px] text-[#ff4e11]">{{ category.name.toUpperCase() }}</span>
                        </div>
                        <div class="border-x border-marginelight pt-[7px]">
                            <template v-for="(service, index) in category.services" :key="service.id">
                                <div class="flex px-[10px]" :class="index === category.services.length - 1 ? 'pb-[7px]' : ''">
                                    <span class="w-[18px] shrink-0 pt-[2px]"><img :src="site.hasProducts ? '/images/bullet-gray.svg' : '/images/bullet-orange.svg'" width="13" height="12" alt=""></span>
                                    <Link :href="`/servicii/${service.slug}`" class="text-[12px] font-bold leading-[14px] text-meniu no-underline hover:text-albastru">{{ service.name }}</Link>
                                </div>
                                <div v-if="index < category.services.length - 1" class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                            </template>
                        </div>
                        <div class="h-[8px] rounded-b-[7px] border border-t-0 border-marginelight"></div>
                    </div>
                </div>

                <div v-if="site.manufacturers.length" class="mt-[5px] flex">
                    <div class="w-[6px] shrink-0 pt-[63px]">
                        <div class="h-[84px] w-[6px] rounded-l-[5px] bg-[#8c8c8c]"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex h-[37px] items-center justify-center rounded-t-[7px] border border-b-0 border-[#ededed] bg-gradient-to-b from-white to-[#f7f7f7]">
                            <span class="font-arial text-[12px] text-[#ff4e11]">PRODUCATORI</span>
                        </div>
                        <div class="border-x border-marginelight pt-[7px]">
                            <template v-for="maker in site.manufacturers" :key="maker.name">
                                <div class="flex px-[10px]">
                                    <span class="w-[18px] shrink-0 pt-[2px]"><img :src="'/images/bullet-gray.svg'" width="13" height="12" alt=""></span>
                                    <span class="leading-[14px] text-[#62879f]"><Link :href="`/cauta?producator=${encodeURIComponent(maker.name)}`" class="font-bold text-[#6f6a68] no-underline hover:text-albastru hover:underline">{{ maker.name }}</Link> ({{ maker.total }})</span>
                                </div>
                                <div class="linie-punctata mx-auto my-[4px] w-[80%] max-w-[180px]"></div>
                            </template>
                            <div class="px-[10px] pb-[7px] pt-[5px] text-right">
                                <Link href="/cauta" class="text-[#444444] no-underline">...Toti producatorii</Link>
                            </div>
                        </div>
                        <div class="h-[8px] rounded-b-[7px] border border-t-0 border-marginelight"></div>
                    </div>
                </div>
            </aside>

            <main class="order-1 w-full min-w-0 lg:order-2 lg:w-auto lg:flex-1">
                <slot />
            </main>

            <aside class="order-3 w-full shrink-0 md:w-[calc(50%-8px)] lg:w-56 lg:px-[5px]">
                <div class="w-full">
                    <div class="h-[8px] rounded-t-[7px] border border-b-0 border-marginelight bg-white"></div>
                    <div class="border-x border-marginelight bg-white px-[2px] py-[2px]">
                        <div class="p-[2px] text-center font-arial text-[13px] text-portocaliu">STIRI</div>
                        <template v-for="(item, index) in site.news" :key="item.slug">
                            <div class="p-[5px] text-center text-[11px] font-bold text-albastru">
                                <Link :href="`/stiri/${item.slug}`" class="text-albastru no-underline hover:underline">{{ item.title }}</Link>
                            </div>
                            <div class="px-[5px] text-[11px] leading-[14px] text-[#716f6f]" :class="index === site.news.length - 1 ? 'pb-[5px]' : ''">{{ item.excerpt }}</div>
                        </template>
                    </div>
                    <img :src="'/images/box-bottom-right.svg'" width="164" height="33" alt="" class="block h-auto w-full">
                </div>

                <div v-if="site.promos.length" class="mt-[5px] w-full">
                    <div class="h-[8px] rounded-t-[7px] border border-b-0 border-marginelight bg-white"></div>
                    <div class="border-x border-marginelight bg-white px-[2px] py-[2px]">
                        <div class="p-[2px] text-center font-arial text-[13px] text-portocaliu">SUPER PROMOTIE</div>
                        <div v-for="product in site.promos" :key="product.slug" class="p-[5px] text-center">
                            <Link :href="`/produs/${product.slug}`" class="inline-block">
                                <img :src="product.image_url" :alt="product.name" class="mx-auto max-w-full border border-[#e3e3e3] bg-white p-[10px]">
                            </Link>
                            <div class="mt-[5px]">
                                <Link :href="`/produs/${product.slug}`" class="text-[10px] font-bold text-portocaliu no-underline hover:text-submeniu">{{ product.name }}</Link>
                            </div>
                        </div>
                    </div>
                    <img :src="'/images/box-bottom-right.svg'" width="164" height="33" alt="" class="block h-auto w-full">
                </div>
            </aside>
        </div>

        <div class="h-[20px]"></div>
        <div class="h-px bg-marginelight"></div>
        <div class="p-[10px] text-center leading-[20px]">
            <Link href="/categorie/centrale-termice" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Centrale termice</Link>&nbsp;-&nbsp;
            <Link href="/cauta" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Producatori</Link>&nbsp;-&nbsp;
            <Link href="/servicii/contact" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Intrebari Frecvente</Link>&nbsp;-&nbsp;
            <Link href="/servicii/despre-noi" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Despre Noi</Link>&nbsp;-&nbsp;
            <Link href="/servicii/despre-noi" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Termeni si conditii</Link>&nbsp;-&nbsp;
            <Link href="/cauta" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Toate cautarile</Link>&nbsp;-&nbsp;
            <Link href="/" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Sitemap</Link>&nbsp;-&nbsp;
            <Link href="/servicii/contact" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Contact</Link>
        </div>
        <div class="text-center text-[11px] text-[#716f6f]">
            <p class="mb-[11px]">Citeste <Link href="/politica-de-confidentialitate" class="text-albastru">Politica de Confidentialitate</Link> si continuarea navigarii pe acest site implica acceptarea <Link href="/politica-de-cookies" class="text-albastru">Politicii de Cookies</Link>.</p>
            <p class="mb-[11px]">
                <button type="button" class="text-albastru underline" @click="window.dispatchEvent(new Event('dds-cookies-open'))">Setări cookie-uri</button>
            </p>
            <p class="mb-[11px]">
                <a href="https://www.cazare-pe-litoral.ro" title="Cazare pe litoral" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Cazare pe litoral</a>
                &nbsp;-&nbsp;
                <a href="https://www.magazin-blanuri.ro" title="Magazin blanuri - haine din blana" class="text-portocaliu no-underline hover:text-submeniu hover:underline">Magazin blanuri</a>
            </p>
        </div>
        <div class="h-px bg-marginelight"></div>
        <div class="p-[10px] text-center">
            un produs proaspat oferit de <a href="https://www.layerzero.ro" title="Web Design" class="text-albastru">layerzero.ro</a>
        </div>
        <div class="text-center">&copy; 2008 DDS Services Group</div>
        <CookieConsent />
    </div>
</template>
