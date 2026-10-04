<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Banner;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::query()->updateOrCreate([], ['menu_position' => 'top']);

        User::query()->updateOrCreate(
            ['email' => 'admin@dds.local'],
            ['name' => 'Admin', 'password' => 'dds-admin', 'role' => 'admin', 'phone' => '0241555000'],
        );

        $clients = [
            ['email' => 'client@dds.local', 'name' => 'Ion Popescu', 'phone' => '0722000001', 'password' => 'dds-client'],
            ['email' => 'maria@dds.local', 'name' => 'Maria Ionescu', 'phone' => '0722000002', 'password' => 'dds-client'],
            ['email' => 'andrei@dds.local', 'name' => 'Andrei Georgescu', 'phone' => '0722000003', 'password' => 'dds-client'],
        ];

        $users = [];
        foreach ($clients as $client) {
            $users[] = User::query()->updateOrCreate(
                ['email' => $client['email']],
                [...$client, 'role' => 'client'],
            );
        }

        $equipment = [
            [$users[0], 'Centrala Ariston Genus', 'Centrala termica', now()->subMonths(23), 24],
            [$users[0], 'Aer conditionat Toshiba', 'Aer conditionat', now()->subMonths(10), 12],
            [$users[1], 'Centrala Buderus Logamax', 'Centrala termica', now()->subMonths(6), 24],
            [$users[2], 'Panou solar Kairos', 'Panou solar', now()->subYears(2), 24],
        ];

        $savedEquipment = [];
        foreach ($equipment as [$user, $name, $type, $last, $interval]) {
            $savedEquipment[] = $user->equipment()->updateOrCreate(
                ['name' => $name],
                [
                    'type' => $type,
                    'last_revision_on' => $last->toDateString(),
                    'interval_months' => $interval,
                ],
            );
        }

        Appointment::query()->updateOrCreate(
            ['user_id' => $users[0]->id, 'equipment_id' => $savedEquipment[0]->id],
            ['requested_on' => now()->addDays(12)->toDateString(), 'note' => 'Prefer dimineata.', 'status' => 'requested'],
        );
        Appointment::query()->updateOrCreate(
            ['user_id' => $users[1]->id, 'equipment_id' => $savedEquipment[2]->id],
            ['requested_on' => now()->addDays(20)->toDateString(), 'note' => 'Acces din spatele blocului.', 'status' => 'confirmed'],
        );
        Appointment::query()->updateOrCreate(
            ['user_id' => $users[2]->id, 'equipment_id' => $savedEquipment[3]->id],
            ['requested_on' => now()->subDays(15)->toDateString(), 'note' => 'Revizie efectuata.', 'status' => 'done'],
        );

        $centrale = ProductCategory::query()->updateOrCreate(
            ['slug' => 'centrale-termice'],
            ['name' => 'Centrale termice', 'sort' => 1, 'parent_id' => null],
        );
        $condensatie = ProductCategory::query()->updateOrCreate(
            ['slug' => 'centrale-in-condensatie'],
            ['name' => 'Centrale in condensatie', 'sort' => 1, 'parent_id' => $centrale->id],
        );
        $murale = ProductCategory::query()->updateOrCreate(
            ['slug' => 'centrale-murale'],
            ['name' => 'Centrale murale', 'sort' => 2, 'parent_id' => $centrale->id],
        );
        $ac = ProductCategory::query()->updateOrCreate(
            ['slug' => 'aparate-aer-conditionat'],
            ['name' => 'Aparate aer conditionat', 'sort' => 2, 'parent_id' => null],
        );
        $solare = ProductCategory::query()->updateOrCreate(
            ['slug' => 'panouri-solare'],
            ['name' => 'Panouri solare', 'sort' => 3, 'parent_id' => null],
        );

        $products = [
            ['Panou solar Kairos Thermo HF', 'panou-solar-kairos', $solare->id, 'ARISTON', '235.jpg', 'Panou solar pentru apa calda menajera.'],
            ['Centrala Clas Evo System', 'centrala-clas-evo-system', $murale->id, 'ARISTON', '234.jpg', 'Centrala murala cu tiraj fortat.'],
            ['Centrala Clas Evo', 'centrala-clas-evo', $murale->id, 'ARISTON', '233.jpg', 'Centrala murala pentru apartament.'],
            ['Genus Premium Evo System', 'genus-premium-evo-system', $condensatie->id, 'ARISTON', '232.jpg', 'Centrala in condensatie, sistem.'],
            ['Boiler Logalux SU', 'boiler-logalux-su', $centrale->id, 'BUDERUS', '228.jpg', 'Boiler cu o serpentina.'],
            ['Centrala Logano SK 625', 'centrala-logano-sk-625', $centrale->id, 'BUDERUS', '224.jpg', 'Centrala de pardoseala.'],
        ];

        foreach ($products as $index => [$name, $slug, $categoryId, $manufacturer, $image, $excerpt]) {
            Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'product_category_id' => $categoryId,
                    'manufacturer' => $manufacturer,
                    'image' => "produse/{$image}",
                    'excerpt' => $excerpt,
                    'description' => $excerpt.' Produs de prezentare, fara comanda online.',
                    'is_published' => true,
                    'sort' => $index + 1,
                ],
            );
        }

        $revizii = ServiceCategory::query()->updateOrCreate(
            ['slug' => 'revizii'],
            ['name' => 'Revizii', 'sort' => 1, 'show_in_sidebar' => true],
        );
        $montaj = ServiceCategory::query()->updateOrCreate(
            ['slug' => 'montaj'],
            ['name' => 'Montaj', 'sort' => 2, 'show_in_sidebar' => true],
        );
        $pagini = ServiceCategory::query()->updateOrCreate(
            ['slug' => 'pagini'],
            ['name' => 'Pagini', 'sort' => 3, 'show_in_sidebar' => false],
        );

        $this->service($revizii->id, 'Revizie centrala termica', 'revizie-centrala', 1, [
            ['type' => 'heading', 'data' => ['text' => 'Verificare tehnica periodica']],
            ['type' => 'text', 'data' => ['body' => 'Programati revizia centralei din contul de client. Va anuntam prin email si SMS inainte de termen.']],
            ['type' => 'button', 'data' => ['label' => 'Programeaza', 'url' => '/cont']],
        ]);
        $this->service($revizii->id, 'Verificare ISCIR', 'verificare-iscir', 2, [
            ['type' => 'text', 'data' => ['body' => 'Autorizatii de functionare si verificari tehnice periodice pentru toata gama de putere.']],
        ]);
        $this->service($montaj->id, 'Montaj centrala termica', 'montaj-centrala', 1, [
            ['type' => 'heading', 'data' => ['text' => 'Montaj si punere in functiune']],
            ['type' => 'text', 'data' => ['body' => 'Montam centrale murale si de pardoseala, cu pornire si instructaj.']],
        ]);
        $this->service($montaj->id, 'Instalatii panouri solare', 'instalatii-panouri-solare', 2, [
            ['type' => 'text', 'data' => ['body' => 'Proiectare si montaj pentru sisteme solare de apa calda.']],
        ]);
        $this->service($pagini->id, 'Despre noi', 'despre-noi', 1, [
            ['type' => 'text', 'data' => ['body' => 'DDS Services Group, infiintata in 1995, livreaza si monteaza centrale termice si aparate de aer conditionat in Constanta.']],
        ]);
        $this->service($pagini->id, 'Contact', 'contact', 2, [
            ['type' => 'text', 'data' => ['body' => 'Bd. Mamaia nr. 70, Constanta. Telefon 0241 555 000.']],
        ]);

        $articles = [
            ['Programari revizii', 'programari-revizii', 'Puteti cere programarea reviziei din cont.', 'Clientii cu cont pot urmari data urmatoarei revizii si pot trimite o cerere de programare.'],
            ['Garantie extinsa Ariston', 'garantie-extinsa-ariston', 'Cinci ani pentru modelele din promotie.', 'Modelele Genus Premium Evo, Genus Evo si Clas Evo pot primi garantie extinsa.'],
            ['Exemple de lucrari', 'exemple-de-lucrari', 'Centrale, panouri solare si aer conditionat.', 'O selectie din lucrarile executate in Constanta si imprejurimi.'],
        ];

        foreach ($articles as $index => [$title, $slug, $excerpt, $body]) {
            News::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'published_at' => now()->subDays($index * 3),
                    'is_published' => true,
                    'blocks' => [
                        ['type' => 'text', 'data' => ['body' => $body]],
                    ],
                ],
            );
        }

        MenuItem::query()->updateOrCreate(['label' => 'Home'], ['url' => '/', 'sort' => 1]);
        MenuItem::query()->updateOrCreate(['label' => 'Servicii'], ['url' => '/servicii/revizie-centrala', 'sort' => 2]);
        MenuItem::query()->updateOrCreate(['label' => 'Despre noi'], ['url' => '/servicii/despre-noi', 'sort' => 3]);
        MenuItem::query()->updateOrCreate(['label' => 'Contact'], ['url' => '/servicii/contact', 'sort' => 4]);

        $banners = [
            ['Chaffoteaux', '19.png', 1],
            ['Centrale termice', '17.jpg', 2],
            ['Aer conditionat', '18.jpg', 3],
            ['Panouri solare', '10.jpg', 4],
            ['Radiatoare decorative', '13.jpg', 5],
        ];

        foreach ($banners as [$title, $file, $sort]) {
            if (! is_file(storage_path('app/public/banners/'.$file))) {
                continue;
            }

            Banner::query()->updateOrCreate(
                ['title' => $title],
                ['image' => 'banners/'.$file, 'link' => '/', 'sort' => $sort, 'is_published' => true],
            );
        }
    }

    private function service(int $categoryId, string $name, string $slug, int $sort, array $blocks): void
    {
        Service::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'service_category_id' => $categoryId,
                'name' => $name,
                'is_published' => true,
                'sort' => $sort,
                'blocks' => $blocks,
            ],
        );
    }
}
