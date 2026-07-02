<!-- Moje údaje -->
<div x-data="{ 
    zeme: '', 
    mesto: '', 
    openZeme: false, 
    openMesto: false, 
    countryCodes: {
        'Albánie': 'al', 'Andorra': 'ad', 'Arménie': 'am', 'Belgie': 'be', 
        'Bělorusko': 'by', 'Bosna a Hercegovina': 'ba', 'Bulharsko': 'bg', 
        'Černá Hora': 'me', 'Česká republika': 'cz'
    },
    countryData: {
        'Albánie': ['Tirana', 'Durrës', 'Vlorë', 'Shkodër', 'Fier'],
        'Andorra': ['Andorra la Vella', 'Escaldes-Engordany', 'Sant Julià de Lòria'],
        'Arménie': ['Jerevan', 'Gjumri', 'Vanadzor', 'Vagaršapat'],
        'Belgie': ['Brusel', 'Antverpy', 'Gent', 'Charleroi', 'Lutych'],
        'Bělorusko': ['Minsk', 'Homel', 'Mahiljow', 'Vicebsk', 'Hrodna'],
        'Bosna a Hercegovina': ['Sarajevo', 'Banja Luka', 'Tuzla', 'Zenica', 'Mostar', 'Bihać', 'Brčko', 'Doboj', 'Foča', 'Jahorina', 'Konjic', 'Neum', 'Prijedor', 'Šamac'],
        'Bulharsko': ['Sofie', 'Plovdiv', 'Varna', 'Burgas', 'Ruse'],
        'Černá Hora': ['Podgorica', 'Nikšić', 'Pljevlja', 'Bijelo Polje', 'Bar'],
        'Česká republika': ['Praha', 'Brno', 'Ostrava', 'Plzeň', 'Liberec', 'Olomouc', 'České Budějovice', 'Hradec Králové', 'Ústí nad Labem', 'Pardubice']
    },
    get cities() { return this.countryData[this.zeme] || []; }
}" class="flex flex-col w-[400px] max-[426px]:!w-[310px] mx-auto">
    <h3 class="font-bold text-[24px] text-[#5C2D62] mb-8" style="font-family: 'Poppins', sans-serif;">Moje údaje</h3>
    
    <div class="w-[400px] max-[426px]:!w-[310px] h-[84px] flex flex-col gap-2 items-center">
        <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Vaše přezdívka</label>
        <input type="text" class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="Příklad přezdívky">
    </div>
    
    <!-- Email -->
    <div class="w-[400px] max-[426px]:!w-[310px] h-[84px] flex flex-col gap-2 items-center mt-4">
        <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Váš email</label>
        <input type="email" class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="priklad@email.cz">
    </div>

    <!-- Země -->
    <div class="w-[400px] max-[426px]:!w-[310px] h-auto flex flex-col gap-2 items-center mt-4">
        <div class="flex justify-between w-[400px] max-[426px]:!w-[310px]">
            <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Země</label>
            <div x-show="zeme === ''" class="flex items-center gap-1.5">
                <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="w-[20px] h-[20px]" alt="Alert">
                <span class="text-[13px] text-[#D80027]" style="font-family: 'Poppins', sans-serif;">Povinná položka</span>
            </div>
        </div>
        <div class="relative w-[400px] max-[426px]:!w-[310px]">
            <input x-model="zeme" type="text" readonly class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] px-4 font-bold text-[15px] text-[#505050]" :class="zeme === '' ? '!border-[#D80027]' : '!border-[#E6E6E6]'" style="font-family: 'Poppins', sans-serif;" placeholder="Česká republika">
            <button @click="openZeme = !openZeme; openMesto = false" class="absolute right-1 top-1 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] flex items-center justify-center">
                <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L5 4L9 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <!-- Accordion Options -->
        <div x-show="openZeme" class="w-[400px] max-[426px]:!w-[310px] border-[2px] border-t-0 border-[#E6E6E6] rounded-b-[8px] bg-white z-10">
            <template x-for="country in Object.keys(countryData)" :key="country">
                <div class="flex items-center p-2 cursor-pointer hover:bg-[#FFE0E5]" @click="zeme = country; mesto = ''; openZeme = false">
                    <img :src="'https://flagcdn.com/' + countryCodes[country] + '.svg'" class="w-[24px] h-[24px] mr-2 rounded-full" alt="Flag">
                    <span x-text="country"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- Město -->
    <div class="w-[400px] max-[426px]:!w-[310px] h-auto flex flex-col gap-2 items-center mt-4">
        <div class="flex justify-between w-[400px] max-[426px]:!w-[310px]">
            <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Město</label>
            <div x-show="mesto === ''" class="flex items-center gap-1.5">
                <img src="{{ asset('images/icons/OctagonAlert.svg') }}" class="w-[20px] h-[20px]" alt="Alert">
                <span class="text-[13px] text-[#D80027]" style="font-family: 'Poppins', sans-serif;">Povinná položka</span>
            </div>
        </div>
        <div class="relative w-[400px] max-[426px]:!w-[310px]">
            <input x-model="mesto" type="text" readonly class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] px-4 font-bold text-[15px] text-[#505050]" :class="mesto === '' ? '!border-[#D80027]' : '!border-[#E6E6E6]'" style="font-family: 'Poppins', sans-serif;" placeholder="Praha">
            <button @click="openMesto = !openMesto; openZeme = false" class="absolute right-1 top-1 w-[42px] h-[42px] rounded-[4px] bg-[#DD3888] flex items-center justify-center">
                <svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L5 4L9 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <!-- Accordion Options -->
        <div x-show="openMesto" class="w-[400px] max-[426px]:!w-[310px] border-[2px] border-t-0 border-[#E6E6E6] rounded-b-[8px] bg-white z-10">
            <template x-for="city in cities" :key="city">
                <div class="p-2 cursor-pointer hover:bg-[#FFE0E5]" @click="mesto = city; openMesto = false">
                    <span x-text="city"></span>
                </div>
            </template>
            <div x-show="cities.length === 0" class="p-2 text-gray-500">Nejdříve vyberte zemi</div>
        </div>
    </div>

    <!-- Telefon -->
    <div class="w-[400px] max-[426px]:!w-[310px] h-[84px] flex flex-col gap-2 items-center mt-4">
        <label class="text-[13px] text-[#505050] self-start" style="font-family: 'Poppins', sans-serif;">Telefon</label>
        <input type="tel" class="w-[400px] max-[426px]:!w-[310px] h-[50px] rounded-[8px] border-[2px] border-[#E6E6E6] px-4 font-bold text-[15px] text-[#505050]" style="font-family: 'Poppins', sans-serif;" placeholder="+420 123 456 789">
    </div>
    
    <!-- Toggle Switches -->
    <div x-data="{ toggled1: false, toggled2: false }" class="w-[400px] max-[426px]:!w-[310px] mt-5 flex flex-col gap-[21px]">
        <div class="flex items-center gap-3">
            <button @click="toggled1 = !toggled1" 
                    class="w-[44px] h-[24px] rounded-full flex items-center p-[2px] transition-colors duration-300"
                    :class="toggled1 ? 'bg-[#00B80F]' : 'bg-[#E4E4E7]'">
                <div class="w-[20px] h-[20px] bg-white rounded-full shadow-sm transform transition-transform duration-300"
                     :class="toggled1 ? 'translate-x-[20px]' : 'translate-x-0'"></div>
            </button>
            <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">mám WhatsApp</span>
        </div>
        <div class="flex items-center gap-3">
            <button @click="toggled2 = !toggled2" 
                    class="w-[44px] h-[24px] rounded-full flex items-center p-[2px] transition-colors duration-300"
                    :class="toggled2 ? 'bg-[#00B80F]' : 'bg-[#E4E4E7]'">
                <div class="w-[20px] h-[20px] bg-white rounded-full shadow-sm transform transition-transform duration-300"
                     :class="toggled2 ? 'translate-x-[20px]' : 'translate-x-0'"></div>
            </button>
            <span class="text-[13px] text-[#505050]" style="font-family: 'Poppins', sans-serif;">mám Telegram</span>
        </div>
    </div>

    <!-- Save Button -->
    <button class="w-[400px] max-[426px]:!w-[310px] h-[50px] bg-[#E8E8E8] rounded-[8px] flex items-center justify-center gap-2 mt-8">
        <img src="{{ asset('images/icons/Save.svg') }}" class="w-[20px] h-[20px]" alt="Save">
        <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 16px; color: #A4A4A4;">Uložit změny</span>
    </button>
</div>