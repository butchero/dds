<?php

namespace App\Support;

class Counties
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $counties = [
            'Alba', 'Arad', 'Argeș', 'Bacău', 'Bihor', 'Bistrița-Năsăud', 'Botoșani',
            'Brașov', 'Brăila', 'București', 'Buzău', 'Caraș-Severin', 'Călărași', 'Cluj',
            'Constanța', 'Covasna', 'Dâmbovița', 'Dolj', 'Galați', 'Giurgiu', 'Gorj',
            'Harghita', 'Hunedoara', 'Ialomița', 'Iași', 'Ilfov', 'Maramureș', 'Mehedinți',
            'Mureș', 'Neamț', 'Olt', 'Prahova', 'Satu Mare', 'Sălaj', 'Sibiu', 'Suceava',
            'Teleorman', 'Timiș', 'Tulcea', 'Vaslui', 'Vâlcea', 'Vrancea',
        ];

        return array_combine($counties, $counties);
    }
}
